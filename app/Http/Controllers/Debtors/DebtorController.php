<?php

namespace App\Http\Controllers\Debtors;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Invoice;
use App\Models\SchoolYear;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentCourse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Util\Json;

class DebtorController extends ApiController
{
    /**
     * Calcula la fecha de vencimiento del mes: día 10 o siguiente día hábil si cae en fin de semana.
     */
    private function getDeadline($year, $month)
    {
        $date = Carbon::create($year, $month, 10);
        // Si cae sábado, mover a lunes
        if ($date->isSaturday()) $date->addDays(2);
        // Si cae domingo, mover a lunes
        if ($date->isSunday()) $date->addDay();
        return $date;
    }

    /**
     * GET /api/debtors/auto
     * Detecta automáticamente morosos: alumnos activos con inscripción en cursos del año activo
     * que no tienen factura pagada para meses ya vencidos.
     */
    public function auto(Request $request)
    {
        $schoolYear = SchoolYear::where('active', true)->first();
        if (!$schoolYear) {
            return $this->successResponse(['data' => [], 'deadline' => null, 'currentMonth' => null], 200);
        }

        // Permite simular fecha para testing (ej: ?test_date=2026-03-11)
        $today = $request->input('test_date')
            ? Carbon::parse($request->input('test_date'))
            : Carbon::today();
        $currentYear = $today->year;
        $currentMonth = $today->month;

        // Filtros opcionales
        $languageId = $request->input('language_id');
        $courseId = $request->input('course_id');
        $filterMonth = $request->input('month') ? (int) $request->input('month') : null;

        // Determinar hasta qué mes se debe haber pagado
        // Si ya pasó el vencimiento del mes actual, ese mes también cuenta como adeudado
        $deadline = $this->getDeadline($currentYear, $currentMonth);
        $maxMonthDue = $today->gt($deadline) ? $currentMonth : $currentMonth - 1;

        // Si se filtra por un mes específico, usar ese mes como límite
        if ($filterMonth) {
            $maxMonthDue = $filterMonth;
        }

        // Ciclo lectivo: marzo (3) a diciembre (12)
        $maxMonthDue = min($maxMonthDue, 12);

        if ($maxMonthDue < 3) {
            return $this->successResponse([
                'data' => [],
                'deadline' => $deadline->format('d/m/Y'),
                'currentMonth' => $currentMonth,
                'maxMonthDue' => 0,
            ], 200);
        }

        // Alumnos activos inscriptos en cursos del año lectivo activo
        $query = StudentCourse::with([
            'student',
            'course.level.languaje',
        ])
        ->whereHas('student', function ($q) {
            $q->where('activo', true);
        })
        ->whereHas('course', function ($q) use ($schoolYear) {
            $q->where('school_year_id', $schoolYear->id);
        });

        if ($courseId) {
            $query->where('id_curso', (int) $courseId);
        }

        if ($languageId) {
            $query->whereHas('course.level', function ($q) use ($languageId) {
                $q->where('id_idioma', (int) $languageId);
            });
        }

        $enrollments = $query->get();

        $debtors = [];
        $interestRate = (float) Setting::getValue('interest_rate', '0');

        // Buscar año anterior para arrastrar deuda
        $previousSchoolYear = SchoolYear::where('year', $schoolYear->year - 1)->first();

        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;
            $course = $enrollment->course;
            $level = $course ? $course->level : null;
            $language = $level ? $level->languaje : null;

            if (!$student || !$course || !$level) continue;

            $monthlyFee = (float) ($level->cuota ?? 0);

            // --- Deuda del año anterior ---
            $previousYearOwedMonths = [];
            $previousYearFee = 0;
            if ($previousSchoolYear) {
                $prevEnrollment = StudentCourse::where('id_alumno', $student->id)
                    ->whereHas('course', function ($q) use ($previousSchoolYear) {
                        $q->where('school_year_id', $previousSchoolYear->id);
                    })->with('course.level')->first();

                if ($prevEnrollment) {
                    $previousYearFee = (float) ($prevEnrollment->course->level->cuota ?? 0);

                    // Sin filtrar por fecha: la deuda anterior se puede pagar en el año actual
                    $prevPaidMonths = Invoice::where('id_alumno_curso', $prevEnrollment->id)
                        ->whereNull('deleted_at')
                        ->pluck('mes')
                        ->map(function ($m) { return (int) $m; })
                        ->toArray();

                    $prevEnrollmentMonth = $prevEnrollment->fecha
                        ? Carbon::parse($prevEnrollment->fecha)->month
                        : 3;
                    $prevStartMonth = max($prevEnrollmentMonth, 3);

                    for ($m = $prevStartMonth; $m <= 12; $m++) {
                        if (!in_array($m, $prevPaidMonths)) {
                            $previousYearOwedMonths[] = $m;
                        }
                    }
                }
            }

            // --- Deuda del año actual ---
            $paidMonths = Invoice::where('id_alumno_curso', $enrollment->id)
                ->whereNull('deleted_at')
                ->whereBetween('fecha_emision', ["{$currentYear}-01-01", "{$currentYear}-12-31"])
                ->pluck('mes')
                ->map(function ($m) { return (int) $m; })
                ->toArray();

            $enrollmentMonth = $enrollment->fecha
                ? Carbon::parse($enrollment->fecha)->month
                : 3;
            $startMonth = max($enrollmentMonth, 3);

            $owedMonths = [];
            for ($m = $startMonth; $m <= $maxMonthDue; $m++) {
                if (!in_array($m, $paidMonths)) {
                    $owedMonths[] = $m;
                }
            }

            if (empty($owedMonths) && empty($previousYearOwedMonths)) continue;

            // Filtro por mes específico: solo aplica a meses del año actual
            if ($filterMonth && !in_array($filterMonth, $owedMonths) && empty($previousYearOwedMonths)) continue;

            // Mora: monto fijo por cada mes vencido (ambos años)
            $moraPerMonth = $interestRate;
            $totalCurrentMonths = count($owedMonths);
            $totalPrevMonths = count($previousYearOwedMonths);
            $totalMora = ($totalCurrentMonths + $totalPrevMonths) * $moraPerMonth;
            $amountOwed = ($totalCurrentMonths * $monthlyFee) + ($totalPrevMonths * $previousYearFee) + $totalMora;

            // Último pago (considerando ambos años)
            $lastInvoice = Invoice::where('id_alumno', $student->id)
                ->whereNull('deleted_at')
                ->orderBy('fecha_emision', 'DESC')
                ->first();

            $debtors[] = [
                'studentId' => $student->id,
                'student' => $student->apellido . ', ' . $student->nombre,
                'dni' => $student->dni ?? '',
                'courseId' => $course->id,
                'courseName' => $course->nombre,
                'levelName' => $level->nombre,
                'languageName' => $language ? $language->nombre : '',
                'languageId' => $language ? $language->id : null,
                'monthlyFee' => $monthlyFee,
                'moraPerMonth' => round($moraPerMonth, 2),
                'totalMora' => round($totalMora, 2),
                'owedMonths' => $owedMonths,
                'monthsOwed' => $totalCurrentMonths,
                'amountOwed' => round($amountOwed, 2),
                'previousYearDebt' => !empty($previousYearOwedMonths),
                'previousYear' => $previousSchoolYear ? $previousSchoolYear->year : null,
                'previousYearOwedMonths' => $previousYearOwedMonths,
                'previousYearMonthsOwed' => $totalPrevMonths,
                'previousYearFee' => $previousYearFee,
                'previousYearAmount' => round($totalPrevMonths * $previousYearFee, 2),
                'lastPaymentDate' => $lastInvoice ? Carbon::parse($lastInvoice->fecha_emision)->format('d/m/Y') : null,
                'lastMonthPaid' => $lastInvoice ? (int) $lastInvoice->mes : null,
            ];
        }

        // Ordenar por language > level > course > student
        usort($debtors, function ($a, $b) {
            return strcmp($a['languageName'] . $a['levelName'] . $a['courseName'] . $a['student'],
                         $b['languageName'] . $b['levelName'] . $b['courseName'] . $b['student']);
        });

        // Totales
        $totalOwed = array_sum(array_column($debtors, 'amountOwed'));
        $totalMora = array_sum(array_column($debtors, 'totalMora'));
        $totalStudents = count(array_unique(array_column($debtors, 'studentId')));
        $interestRate = (float) Setting::getValue('interest_rate', '0');

        return $this->successResponse([
            'data' => $debtors,
            'deadline' => $deadline->format('d/m/Y'),
            'currentMonth' => $currentMonth,
            'maxMonthDue' => $maxMonthDue,
            'totalOwed' => round($totalOwed, 2),
            'totalMora' => round($totalMora, 2),
            'totalStudents' => $totalStudents,
            'interestRate' => $interestRate,
        ], 200);
    }

    public function index(Request $request){
        $invoiceCollectConvert = [];

        $year = (int)$request->year;
        $month = (int)$request->month;
        $languaje = (int)$request->languaje;
        $course = $request->curso ?? null;
        $status = $request->status == "true" ? true : false;
        $courses = null;
        $schoolYear = SchoolYear::where('active',true)->first();
        $courses = null;

        if($course=="null"){
            $courses = Course::query()->whereHas('level',function($query) use ($languaje){
                return $query->where('id_idioma',$languaje)->whereHas('languaje',function($q) use($languaje){
                    return $q->where('id',$languaje);
                });
            })->where('school_year_id',$schoolYear->id)->pluck('id');
        }

        if($course!=="null"){
            $courses = [$course];
        }

        $studentCourse = StudentCourse::with(['student.latestPayment'])
        ->whereHas('student',function($q) use ($month,$status){
            return $q->where('activo',$status)->whereHas('latestPayment',function($q) use ($month){
                return $q->whereRaw("date_part('month',fecha_emision) < $month" );
            });
        })
        ->whereIn('id_curso',$courses)
        ->pluck('id');




        $invoices = Invoice::query()
        ->with(['studentCourse' => function ($query) {
            $query->select('id');
        }])

        ->with(['studentCourse.course.level.languaje' => function ($query) use($languaje) {
            $query->where('id',$languaje)->select('id', 'nombre');
        }])
        ->with(['studentCourse.student' => function ($query) {
            $query->select('id', 'nombreCompleto');
        }])
        ->whereIn('id_alumno_curso',$studentCourse)
        ->whereBetween('fecha_emision', ["{$year}-01-01", "{$year}-12-31"])
        ->select('id',
                'id_alumno',
                'id_alumno_curso',
                'mes',
                'fecha_emision',
                'cuota',
                DB::raw( "sum($month)  - sum(date_part('month',fecha_emision))  as monthOwed"),
                DB::raw( "cuota * (sum($month)  - sum(date_part('month',fecha_emision)))  as amountOwed"),
        )
        ->groupBy('id')->orderBy('fecha_emision')

        ->with(['studentCourse.course.level' => function ($query) use($languaje){
            $query->where('id_idioma',$languaje)->select('id', 'nombre','id_idioma','cuota');
        }])
        ->with(['studentCourse.course' => function ($query) {
            $query->select('id', 'nombre','id_nivel')->orderBy('nombre');
        }])
        ->get()->sortBy('studentCourse.course.nombre')->groupBy('studentCourse.course.level.nombre');

        $collection = $invoices->map(function ($invoiceLevel,$indice) use ($invoiceCollectConvert){
            $invoiceCourses = $invoiceLevel->groupBy('studentCourse.course.nombre');
            $invoiceCourses = $invoiceCourses->map(function ($invoiceCourse) use($invoiceCollectConvert) {

                $collectionInvoiceCourse = $invoiceCourse->map(function ($invoice,$i) use($invoiceCollectConvert) {

                    $amountOwed = (int)$invoice->monthowed * $invoice->studentCourse->course->level->cuota;
                    $invoiceOrder = [   "id" => $i,
                                        "student"=>$invoice->studentCourse->student->nombreCompleto,
                                        "lastDatePaid"=> Carbon::parse($invoice->fecha_emision)->format('d/m/Y'),
                                        "lastMonthPaid"=>(int)$invoice->mes,
                                        "monthsOwed"=>(int)$invoice->monthowed,
                                        "monthFee"=>(float)$invoice->studentCourse->course->level->cuota,
                                        "amountOwed"=>(float)$amountOwed];
                    return $invoiceOrder;
                });

                $amountOwedTotal = $collectionInvoiceCourse->map(function($invoice){
                    return $invoice["amountOwed"];
                });
                $collectionInvoiceCourse->push(['amountOwedTotal'=>array_sum($amountOwedTotal->toArray())]);
                return $collectionInvoiceCourse;
            });
            return [$invoiceCourses];
        });
        return $this->successResponse(['data'=>[$collection]],200);

      /*   $collection = $invoices->map(function ($invoice,$indice) use ($invoiceCollectConvert){
            $collect = collect();
            $collectionNested = $invoice->map(function ($inv) use($indice,$invoiceCollectConvert) {
                $amountOwed = (int)$inv->monthowed * $inv->studentCourse->course->level->cuota;
                $invoiceCollectConvert = ["student"=>$inv->studentCourse->student->nombreCompleto,
                                        "lastDatePaid"=> Carbon::parse($inv->fecha_emision)->format('d/m/Y'),
                                        "lastMonthPaid"=>(int)$inv->mes,
                                        "monthsOwed"=>(int)$inv->monthowed,
                                        "monthFee"=>(float)$inv->studentCourse->course->level->cuota,
                                        "amountOwed"=>(float)$amountOwed];
                return $invoiceCollectConvert;
            });
            $amountOwedTotal = $collectionNested->map(function($invoice){
                return $invoice["amountOwed"];
            });
            $collect->put('amountOwedTotal',array_sum($amountOwedTotal->toArray()));
            $collect->put('data',$collectionNested);

            return $collect;
        });  */
    }
}


// 'id',
// 'id_alumno',
// 'id_alumno_curso',
// 'fecha_emision',
// 'mes',
// 'cuota',
// 'fecha_vto',
// 'dto_pago_termino',
// 'dto_hermano',
// 'mora',
// 'total',
// 'estado'
