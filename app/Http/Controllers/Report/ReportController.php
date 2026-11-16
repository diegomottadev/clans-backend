<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\ApiController;
use App\Models\Assistance;
use App\Models\Course;
use App\Models\Level;

use App\Models\Invoice;
use App\Models\Languaje;
use App\Models\PaymentTeacher;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TypeAssistance;
use App\Transformers\AssistanceTransformer;
use App\Transformers\AssitanceReportByStudentTransformer;
use App\Transformers\EvaluationTransform;
use App\Transformers\InvoiceTransform;
use App\Transformers\PaymentTeacherTransform;
use App\Transformers\StudentEvaluationTransform;
use App\Transformers\TeacherTransform;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use PDF;

class ReportController extends ApiController
{

    /**
     *
     * @return void
     */
    public function __construct()
    {
        ini_set('max_execution_time', 300); // 300 seconds = 5 minutes
        set_time_limit(0);
        // $this->middleware(
        //     'auth:api'
        // );
    }

    /** El frontend manda "null" (string) cuando un filtro está vacío. */
    private function reportParam($value): bool
    {
        return $value !== null && $value !== '' && $value !== 'null' && $value !== 'undefined';
    }

    public function getReportPaymentTeacher(Request $request)
    {
        $teacherId =  $request->teacherId;
        $dateInit = $request->dateInit;
        $dateFinish = $request->dateFinish;
        $schoolYearId = $request->schoolYearId && $request->schoolYearId !== "null"
            ? (int) $request->schoolYearId
            : SchoolYear::where('active', true)->first()->id;
        $schoolYear = SchoolYear::find($schoolYearId);
        $reportsTeacher = PaymentTeacher::query()->orderBy('fecha', 'DESC');

        // Cada filtro se aplica de forma independiente
        $hasTeacher = $this->reportParam($teacherId);
        $hasRange = $this->reportParam($dateInit) && $this->reportParam($dateFinish);

        $reportsTeacher->when($hasTeacher, function ($q) use ($teacherId) {
            return $q->where('id_profesor', (int) $teacherId);
        });
        $reportsTeacher->when($hasRange, function ($q) use ($dateInit, $dateFinish) {
            return $q->whereBetween('fecha', [$dateInit, $dateFinish]);
        });
        // Sin rango de fechas: limitar al año lectivo seleccionado
        $reportsTeacher->when(!$hasRange && $schoolYear, function ($q) use ($schoolYear) {
            return $q->whereYear('fecha', $schoolYear->year);
        });

        $collections =  $request->input('all', '') == 1
            ?   $this->showList($reportsTeacher) :
            $this->showAll($reportsTeacher);
        return $collections;
    }

    public function getReporybyLanguajeCourse(Request $request)
    {
        $languajeId =  $request->languajeId ?? null;
        $courseId = $request->courseId;
        $month = $request->month;
        $dateInit = $request->dateInit ?? null;
        $dateFinish = $request->dateFinish ?? null;

        $schoolYearId = $request->schoolYearId && $request->schoolYearId !== "null"
            ? (int) $request->schoolYearId
            : SchoolYear::where('active', true)->first()->id;

        $schoolYear = SchoolYear::find($schoolYearId);
        $yearNumber = $schoolYear ? $schoolYear->year : date('Y');

        $query = Invoice::query()->distinct()->with(['studentCourse', 'studentCourse.course'])
            ->where(function ($q) use ($yearNumber, $schoolYearId) {
                $q->where(function ($q2) use ($yearNumber) {
                    $q2->whereNotNull('fecha_emision')->whereYear('fecha_emision', $yearNumber);
                })->orWhereHas('studentCourse', function ($r) use ($schoolYearId) {
                    $r->whereHas('course', function ($c) use ($schoolYearId) {
                        $c->where('school_year_id', $schoolYearId);
                    });
                });
            })
            ->orderBy('fecha_emision', 'DESC');
        /*         $query->when($month != null && $month > 0, function ($q) use ($month, $courseId) {
            return $q->where('mes', $month)->whereHas('studentCourse', function ($r) use ($courseId) {
                $r->where('id_curso', $courseId);
            });
        })->orderBy('fecha_emision', 'DESC');
 */
        // Cada filtro se aplica de forma independiente (antes idioma/curso se ignoraban sin fechas)
        if ($this->reportParam($dateInit) && $this->reportParam($dateFinish)) {
            $query->whereBetween('fecha_emision', [$dateInit, $dateFinish]);
        }

        if ($this->reportParam($courseId)) {
            $query->whereHas('studentCourse', function ($r) use ($courseId) {
                return $r->where('id_curso', (int) $courseId);
            });
        } elseif ($this->reportParam($languajeId)) {
            $query->whereHas('studentCourse', function ($r) use ($languajeId, $schoolYearId) {
                return $r->whereHas('course', function ($r) use ($languajeId, $schoolYearId) {
                    return $r->where('school_year_id', $schoolYearId)->whereHas('level', function ($r) use ($languajeId) {
                        return $r->where('id_idioma', (int) $languajeId);
                    });
                });
            });
        }

        $collections = $request->input('all', '') == 1
            ? $this->showList($query->get()) :
            $this->showAll($query);

        return  $collections; //$this->successResponse($assitences->get(),200);
    }

    public function getReportAssistancesByCourse(Request $request)
    {

        $dateInit = $request->dateInit ?? null;
        $dateFinish = $request->dateFinish ?? null;
        $courseId = $request->courseId ?? null;
        $month = $request->month ?? null;
        $schoolYearId = $request->schoolYearId && $request->schoolYearId !== "null"
            ? (int) $request->schoolYearId
            : SchoolYear::where('active', true)->first()->id;
        $assitences =  Assistance::query()->with(['course', 'studentsAssisted'])->orderBy('fecha', 'DESC');

        // Cada filtro se aplica de forma independiente (antes, curso sin fechas mostraba todo)
        $hasCourse = $this->reportParam($courseId);
        $hasMonth = $this->reportParam($month) && strpos($month, '-') !== false;
        $hasRange = $this->reportParam($dateInit) && $this->reportParam($dateFinish);

        $assitences->when($hasCourse, function ($q) use ($courseId) {
            return $q->where('id_curso', (int) $courseId);
        });
        $assitences->when($hasMonth, function ($q) use ($month) {
            [$year, $mes] = explode('-', $month);
            return $q->whereYear('fecha', '=', (int) $year)->whereMonth('fecha', '=', (int) $mes);
        });
        $assitences->when(!$hasMonth && $hasRange, function ($q) use ($dateInit, $dateFinish) {
            return $q->whereBetween('fecha', [$dateInit, $dateFinish]);
        });

        // Sin mes ni rango: limitar al año lectivo seleccionado
        $sy = SchoolYear::find($schoolYearId);
        $assitences->when(!$hasMonth && !$hasRange && $sy, function ($q) use ($sy) {
            return $q->whereYear('fecha', $sy->year);
        });

        $collections = $request->input('all', '') == 1
            ? $this->showList($assitences->get()) :
            $this->showAll($assitences);
        return  $collections; //$this->successResponse($assitences->get(),200);
    }


    public function getReportLanguajeLevelCourse(Request $request)
    {


        $languajeId =  $request->languajeId ?? "null";
        $levelId = $request->levelId ?? "null";
        $courseId =  $request->courseId ?? "null";
        $schoolYears = $request->schoolYears != "null" ? (int) $request->schoolYears : SchoolYear::where('active',true)->first()->id;
        // $students =  Student::query()->with(['courses','courses.level']);
        
        // $students->when($schoolYears !== "null"  , function ($query) use ($schoolYears) {
        //     $query->whereHas('courses', function ($query) use ($schoolYears) {
        //         //  $schoolYear = SchoolYear::where('active',true)->first();
        //         $values = [];
        //         if(strlen($schoolYears) > 3){
        //             $list_c_array = explode(',', $schoolYears);
        //             $values = array_map('intval', $list_c_array);
        //         }else{
        //             $values[] = intval($schoolYears);
        //         }
        //         return $query->where('school_year_id', $values );
        //     });
        // });
        
        // $students->when($languajeId !== "null"  , function ($query) use ($languajeId) {
        //     $query->whereHas('courses.level', function ($query) use ($languajeId) {
        //             return $query->where('id_idioma', $languajeId);
        //         });;
        //     });
    
        // $students->when($levelId !== "null" , function ($query) use ($levelId) {
        //     $query->whereHas('courses.level', function ($query) use ($levelId) {
        //         $query->where('id', $levelId);
        //     });
        // });

        // $students->when($courseId !== "null" , function ($query) use ($courseId) {
        //     $query->whereHas('courses.level', function ($query) use ($courseId) {
        //         $query->where('id_curso', $courseId);
        //     });
        // });

        $list = [];

        $languaje = $languajeId !== "null" ? Languaje::find($languajeId) : null;
        $level = $levelId !== "null" ? Level::find($levelId) : null;
        //$course = $courseId !== "null" ? Course::find($courseId) : null;
        if ($languajeId !== "null" && $levelId !== "null" && $courseId !== "null" ){

            $course = Course::where('id',$courseId)->first();

            if (!$course) {
                return $this->errorResponse("No se encontró el curso seleccionado", 404);
            }

            $students = Student::with(['courses' => function ($query) use ($schoolYears){
                $query->where('school_year_id', $schoolYears);
            }])
            ->whereHas('courses', function ($query) use ($course) {
                $query->where('id_curso', $course->id);
            })->get()->toArray();

            $list[] = [
                'idioma' => $languaje->nombre,
                'idioma_id' => $languaje->id,
                'nivel' => $level->nombre,
                'nivel_id' => $level->id,
                'curso' => $course->nombre,
                'estudiantes' => $students,
                'num_estudiantes' => count($students),
            ];
        }


        else if ($languaje  && $level ){

            $courses = $level->courses->where('school_year_id',$schoolYears)->pluck('nombre','id');

            foreach($courses as $courseId =>$courseName){

                $students = Student::with(['courses' => function ($query) use ($schoolYears){
                    $query->where('school_year_id', $schoolYears);
                }])
                ->whereHas('courses.level', function ($query) use ($courseId) {
                    $query->where('id_curso', $courseId);
                })->get()->toArray();

                $list[] = [
                    'idioma' => $languaje->nombre,
                    'idioma_id' => $languaje->id,
                    'nivel' => $level->nombre,
                    'nivel_id' => $level->id,
                    'curso' => $courseName,
                    'estudiantes' => $students,
                    'num_estudiantes' => count($students),
                ];
            }
        }
    
        else if ($languaje ){

            $levels = $languaje->levels;

            foreach ($levels as $level) {

                $courses = Course::where('school_year_id',$schoolYears)->where('id_nivel',$level->id)->get();
                // dd($courses['3 ADULTS']);
               
                foreach($courses as $course){
                
                    $partial = [
                        'idioma' => $languaje->nombre,
                        'idioma_id' => $languaje->id,
                        'nivel' => $level->nombre,
                        'nivel_id' => $level->id,
                        'curso' => $course->nombre,
                        'curso_id' => $course->id,
                        'estudiantes'=> null,
                        'num_estudiantes' => null
                    ];

                    $students = Student::with(['courses' => function ($query) use ($schoolYears){
                        $query->where('school_year_id', $schoolYears);
                    }])->whereHas('courses.level', function ($query) use ($course) {
                        $query->where('id_curso', $course->id);
                    })->get()->toArray();

                    $partial['estudiantes'] =  $students;
                    $partial['num_estudiantes'] =  count($students);

                    $list[] = $partial;

                }
            }
        }

        // return $this->successResponse($list,200);


        $result = [];
        $existe_valor = false; // variable para comprobar si existe un valor con la estructura y apellido específicos

        foreach ($list as $key => $item ) {
           
            $data = [
                'apellido' => $item['nivel'].'-'.$item['curso'],
                'nombre' => '',
                'dni' => '',
                'turno_escolar' => '',
                'horario_ed_fisica' => '',
                'observaciones' => '',
            ];
        
            $children = [];

            $students = [];        

            foreach ($item['estudiantes'] as $estudiante) {
                $children[] = [
                    'key' => $estudiante['id'],
                    'data' => [
                        'apellido' => $estudiante['apellido'],
                        'nombre' => $estudiante['nombre'],
                        'dni' => $estudiante['dni'],
                        'turno_escolar' => $estudiante['turno_escolar'],
                        'horario_ed_fisica' => $estudiante['horario_ed_fisica'],
                        'observaciones' => $estudiante['observaciones'],
                    ],
                ];
            }

            $children[] = [
                'key' => $item['nivel'].'-'.$item['curso'],
                'data' => [
                    'apellido' => '',
                    'nombre' => '',
                    'dni' => '',
                    'turno_escolar' => '',
                    'horario_ed_fisica' => '',
                    'observaciones' => '',
                ],
                'children' => $students,
            ];
        
            $result[] = [
                'key' => $item['idioma'].'-'.$item['nivel'].'-'.$item['curso'].'-'.$key,
                'data' => $data,
                'children' => $children,
            ]; 

        }
        

        return $this->successResponse($result,200);
    }

    public function getReportAssistancesByStudent(Request $request)
    {
        $dateInit = $request->dateInit ?? null;
        $dateFinish = $request->dateFinish ?? null;
        $studentId = $request->studentId;
        $month = $request->month ?? null;
        $schoolYearId = $request->schoolYearId && $request->schoolYearId !== "null"
            ? (int) $request->schoolYearId
            : SchoolYear::where('active', true)->first()->id;

        $assitences =  Student::query()->with('assitences');

        $assitences = $assitences->when($month === "null"  && $dateInit !== "null" && $dateFinish !== "null" && $studentId !== "null", function ($q) use ($request, $dateInit, $dateFinish, $studentId) {
            return $q->whereHas('assitences', function ($s) use ($dateInit, $dateFinish) {
                return  $s->whereBetween('fecha', [$dateInit, $dateFinish])->orderBy('fecha', 'DESC');;
            })->where('id', $studentId);
        });
        $assitences = $assitences->when($month !== "null"  && $dateInit === "null"  && $dateFinish === "null"  && $studentId !== "null", function ($q) use ($month, $studentId) {
            return $q->whereHas('assitences', function ($s) use ($month) {
                $date = explode('-', $month);
                $year =  $date[0]; //año
                $mes =  $date[1]; // mes
                return $s->whereYear("fecha",  $year)->whereMonth("fecha", $mes);
            })->where('id', $studentId);
        });


        if ($assitences->first() == null) {
            return $this->successResponse(['data' => []], 200);
        }

        if ($month === "null"  && $dateInit !== "null" && $dateFinish !== "null" && $studentId !== "null") {
            $sy = SchoolYear::find($schoolYearId);
            $q = $assitences->first()->assitences()->whereBetween('fecha', [$dateInit, $dateFinish]);
            if ($sy) { $q->whereYear('fecha', $sy->year); }
            return $this->showRelationshipPaginate($q, AssitanceReportByStudentTransformer::class);
        }

        if ($month !== "null"  && $dateInit === "null"  && $dateFinish === "null"  && $studentId !== "null") {
            $date = explode('-', $month);
            $year =  $date[0]; //año
            $mes =  $date[1]; // mes
            $q = $assitences->first()->assitences()->whereYear("fecha", $year)->whereMonth("fecha", $mes);
            return $this->showRelationshipPaginate($q, AssitanceReportByStudentTransformer::class);
        }
        return $this->successResponse(['data' => []], 200);
    }

    //asistencias, evaluacion y pagos por alumno

    public function getReportCompleteByStudent(Request $request)
    {

        try {

            $dateInit = $request->dateInit ?? null;
            $dateFinish = $request->dateFinish ?? null;
            $studentId = $request->studentId ?? null;
            $schoolYearId = $request->schoolYearId && $request->schoolYearId !== "null"
                ? (int) $request->schoolYearId
                : SchoolYear::where('active', true)->first()->id;

            if (!$this->reportParam($studentId)) {
                return $this->successResponse(['assistences' => null, 'evaluations' => null, 'invoices' => []], 200);
            }
            // Sin rango de fechas: usar el año lectivo completo (antes daba error 500)
            if (!$this->reportParam($dateInit) || !$this->reportParam($dateFinish)) {
                $sy = SchoolYear::find($schoolYearId);
                $year = $sy ? $sy->year : date('Y');
                $dateInit = "{$year}-01-01";
                $dateFinish = "{$year}-12-31";
            }

            // Filtrar también la carga de relaciones: whereHas solo filtra al alumno,
            // no las asistencias/evaluaciones que se traen con with().
            $student = Student::with([
                'assitences' => function ($q) use ($dateInit, $dateFinish) {
                    $q->whereBetween('fechas_curso.fecha', [$dateInit, $dateFinish])
                      ->orderBy('fechas_curso.fecha', 'DESC');
                },
                'assitences.course',
                'evaluations' => function ($q) use ($dateInit, $dateFinish) {
                    $q->whereBetween('evaluaciones.fecha', [$dateInit, $dateFinish])
                      ->orderBy('evaluaciones.fecha', 'DESC');
                },
                'evaluations.course',
                'evaluations.typeEvaluation',
            ])->find($studentId);

            $invoices = Invoice::with('studentCourse.course')->where('id_alumno', $studentId)->whereBetween('fecha_emision', [$dateInit, $dateFinish]);

            $params = [
                'assistences' => $student && $student->assitences->isNotEmpty() ? $student->assitences : null,
                'evaluations' => $student && $student->evaluations->isNotEmpty() ? $student->evaluations : null,
                'invoices' => $invoices->get(),
            ];

            return $this->successResponse($params, 200);
        } catch (\Exception $e) {
            Log::critical('Exception: ' . $e);
            return $this->successResponse([], 200);
        }
    }

    public function downloadReportByStudent(Request $request)
    {
        try {

            $dateInit = $request->dateInit ?? null;
            $dateFinish = $request->dateFinish ?? null;
            $studentId = $request->studentId;
            $month = $request->month ?? null;

            $student = Student::where('id', $studentId)->first();

            $assitences =  Student::query()->with('assitences');

            $assitences = $assitences->when($month === "null"  && $dateInit !== "null" && $dateFinish !== "null" && $studentId !== "null", function ($q) use ($request, $dateInit, $dateFinish, $studentId) {
                return $q->whereHas('assitences', function ($s) use ($dateInit, $dateFinish) {
                    return  $s->whereBetween('fecha', [$dateInit, $dateFinish])->orderBy('fecha', 'DESC');;
                })->where('id', $studentId);
            });
            $assitences = $assitences->when($month !== "null"  && $dateInit === "null"  && $dateFinish === "null"  && $studentId !== "null", function ($q) use ($month, $studentId) {
                return $q->whereHas('assitences', function ($s) use ($month) {
                    $date = explode('-', $month);
                    $year =  $date[0]; //año
                    $mes =  $date[1]; // mes
                    return $s->whereYear("fecha",  $year)->whereMonth("fecha", $mes);
                })->where('id', $studentId);
            });


            if ($month === "null"  && $dateInit !== "null" && $dateFinish !== "null" && $studentId !== "null") {
                $firstStudent = $assitences->first();
                if (!$firstStudent) {
                    return $this->errorResponse("No se encontró el alumno o no tiene asistencias en el rango de fechas", 404);
                }
                $assitences = $firstStudent->assitences()->whereBetween('fecha', [$dateInit, $dateFinish]);
                $assistences = $this->transformData($assitences->get(), AssitanceReportByStudentTransformer::class);
                $assistences = $assistences['data'];

                $tipoAsistencias = TypeAssistance::all();
                $countByTypeAssistence = [];
                $count = 0;
                foreach ($tipoAsistencias as $typeAssistence) {
                    $nameTypeAssistence = $typeAssistence->nombre;
                    foreach ($assistences as $assistence) {
                        if ($nameTypeAssistence == $assistence['typeAssistance']['nameTypeAssistance']['nombre']) {
                            $count += 1;
                        }
                    }
                    $countByTypeAssistence[] = ["typeAssistance" => $nameTypeAssistence, "cant" => $count];
                    $count = 0;
                }

                $pdf = PDF::loadView('reportes/assistenceByStudent', compact('student', 'dateInit', 'dateFinish', 'assistences', 'countByTypeAssistence'));

                return $pdf->download('asistencia-por-alumno.pdf');
            }

            if ($month !== "null"  && $dateInit === "null"  && $dateFinish === "null"  && $studentId !== "null") {
                $date = explode('-', $month);
                $year =  $date[0]; //año
                $mes =  $date[1]; // mes
                $firstStudent = $assitences->first();
                if (!$firstStudent) {
                    return $this->errorResponse("No se encontró el alumno o no tiene asistencias en el mes seleccionado", 404);
                }
                $assitences = $firstStudent->assitences()->whereYear("fecha",  $year)->whereMonth("fecha", $mes);
                $assistences = $this->transformData($assitences->get(), AssitanceReportByStudentTransformer::class);
                $assistences = $assistences['data'];

                $tipoAsistencias = TypeAssistance::all();
                $countByTypeAssistence = [];
                $count = 0;
                foreach ($tipoAsistencias as $typeAssistence) {
                    $nameTypeAssistence = $typeAssistence->nombre;
                    foreach ($assistences as $assistence) {
                        if ($nameTypeAssistence == $assistence['typeAssistance']['nameTypeAssistance']['nombre']) {
                            $count += 1;
                        }
                    }
                    $countByTypeAssistence[] = ["typeAssistance" => $nameTypeAssistence, "cant" => $count];
                    $count = 0;
                }
                $pdf = PDF::loadView('reportes/assistenceByStudent', compact('student', 'month', 'assistences', 'countByTypeAssistence'));
                return $pdf->download('asistencia-por-alumno.pdf');
            }
        } catch (\Exception $e) {
            Log::critical('Exception: ' . $e);
            return $this->successResponse([], 200);
            //return $this->errorResponse("Error al obtener el reporte de asistencia por alumno", 404);
        }
    }

    public function downloadReportByCourse(Request $request)
    {
        try {

            $dateInit = $request->dateInit ?? null;
            $dateFinish = $request->dateFinish ?? null;
            $courseId = $request->courseId ?? null;
            $month = $request->month ?? null;
            $schoolYearId = $request->schoolYearId && $request->schoolYearId !== "null"
                ? (int) $request->schoolYearId
                : SchoolYear::where('active', true)->first()->id;
            $assitences =  Assistance::query()->with(['course', 'studentsAssisted'])->orderBy('fecha', 'DESC');

            // Misma lógica que getReportAssistancesByCourse (pantalla y PDF coinciden)
            $hasCourse = $this->reportParam($courseId);
            $hasMonth = $this->reportParam($month) && strpos($month, '-') !== false;
            $hasRange = $this->reportParam($dateInit) && $this->reportParam($dateFinish);

            $assitences->when($hasCourse, function ($q) use ($courseId) {
                return $q->where('id_curso', (int) $courseId);
            });
            $assitences->when($hasMonth, function ($q) use ($month) {
                [$year, $mes] = explode('-', $month);
                return $q->whereYear('fecha', '=', (int) $year)->whereMonth('fecha', '=', (int) $mes);
            });
            $assitences->when(!$hasMonth && $hasRange, function ($q) use ($dateInit, $dateFinish) {
                return $q->whereBetween('fecha', [$dateInit, $dateFinish]);
            });
            $sy = SchoolYear::find($schoolYearId);
            $assitences->when(!$hasMonth && !$hasRange && $sy, function ($q) use ($sy) {
                return $q->whereYear('fecha', $sy->year);
            });

            $collection = $this->transformData($assitences->get(), AssistanceTransformer::class);
            $assistences =  $collection['data'];

            $course = Course::where('id', $courseId)->first();
            $level =  $course->level;
            $languaje =  $course->level->languaje;

            //return view('reportes.assistencesByCourse', compact('dateInit','dateFinish','assistences','course','languaje','level'));
            $pdf = PDF::loadView('reportes.assistencesByCourse', compact('month', 'dateInit', 'dateFinish', 'assistences', 'course', 'languaje', 'level'));
            return $pdf->download('asistencia-por-curso.pdf');
        } catch (\Exception $e) {
            Log::critical('Exception: ' . $e);
            //return $this->errorResponse("Error al obtener el reporte de asistencia por curso", 404);
            return $this->successResponse([], 200);
        }
    }

    public function downloadReportByInvoice(Request $request, Invoice $invoice)
    {
        try {

            $student = Student::where('id', $invoice->id_alumno)->first();
            if (!$student || !$invoice->studentCourse || !$invoice->studentCourse->course) {
                return $this->errorResponse("No se encontraron datos completos para generar el recibo", 404);
            }
            $course = $invoice->studentCourse->course;
            $date =  $invoice->fecha_emision;
            $month = $invoice->mes;
            $fee = $invoice->cuota;
            $id = $invoice->id;
            $logoPath = public_path('img/encabezado-factura.png');
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            $pdf = PDF::loadView('reportes.invoiceByStudent', compact('student', 'course', 'date', 'month', 'fee', 'id', 'logoBase64'));
            return $pdf->download('recibo-pago-cuota.pdf');
        } catch (\Exception $e) {
            Log::critical('Exception: ' . $e);
            //return $this->errorResponse("Error al obtener el recibo de pago de cuota", 500);
            return $this->successResponse([], 200);
        }
    }

    public function downloadReceiptByPaymentTeacher(Request $request, $id)
    {
        try {
            $paymentTeacher = PaymentTeacher::findOrFail($id);
            $teacher = Teacher::where('id', $paymentTeacher->id_profesor)->first();
            if (!$teacher) {
                return $this->errorResponse("No se encontraron datos del docente para generar el recibo", 404);
            }
            $date = $paymentTeacher->fecha;
            $amount = $paymentTeacher->monto;
            $observation = $paymentTeacher->observacion;
            $id = $paymentTeacher->id;
            $logoPath = public_path('img/encabezado-factura.png');
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            $pdf = PDF::loadView('reportes.paymentTeacherReceipt', compact('teacher', 'date', 'amount', 'observation', 'id', 'logoBase64'));
            return $pdf->download('recibo-pago-docente.pdf');
        } catch (\Exception $e) {
            Log::critical('Exception: ' . $e);
            return $this->errorResponse("Error al generar el recibo de pago a docente", 500);
        }
    }

    public function downloadReportCompleteByStudent(Request $request)
    {
        $dateInit = $request->dateInit ?? null;
        $dateFinish = $request->dateFinish ?? null;
        $studentId = $request->studentId ?? null;
        $schoolYearId = $request->schoolYearId && $request->schoolYearId !== "null"
            ? (int) $request->schoolYearId
            : SchoolYear::where('active', true)->first()->id;

        if (!$this->reportParam($studentId)) {
            return $this->errorResponse("Seleccione un alumno para generar el informe completo", 422);
        }
        // Sin rango de fechas: usar el año lectivo completo (igual que en pantalla)
        if (!$this->reportParam($dateInit) || !$this->reportParam($dateFinish)) {
            $sy = SchoolYear::find($schoolYearId);
            $year = $sy ? $sy->year : date('Y');
            $dateInit = "{$year}-01-01";
            $dateFinish = "{$year}-12-31";
        }

        $assitences =  Student::query()->with(['assitences', 'assitences.course']);
        $assitences = $assitences->when($dateInit !== "null" && $dateFinish !== "null" && $studentId !== "null", function ($q) use ($dateInit, $dateFinish, $studentId) {
            return $q->whereHas('assitences', function ($s) use ($dateInit, $dateFinish) {
                return  $s->whereBetween('fecha', [$dateInit, $dateFinish])->orderBy('fecha', 'DESC');
            })->where('id', $studentId);
        });

        $evaluations =  Student::query()->with(['evaluations', 'evaluations.course', 'evaluations.typeEvaluation']);
        $evaluations = $evaluations->when($dateInit !== "null" && $dateFinish !== "null", function ($q) use ($dateInit, $dateFinish, $studentId) {
            return $q->whereHas('evaluations', function ($s) use ($dateInit, $dateFinish) {
                return  $s->whereBetween('fecha', [$dateInit, $dateFinish])->orderBy('fecha', 'DESC');
            })->where('id', $studentId);
        });

        $invoices = Invoice::with('studentCourse.course')->where('id_alumno', $studentId)->whereBetween('fecha_emision', [$dateInit, $dateFinish]);
        $student = Student::where('id', $studentId)->first();
        if ($dateInit !== "null" && $dateFinish !== "null" && $studentId !== "null") {

            $results = $assitences->get();
            if ($results->isEmpty() || !isset($results[0])) {
                return $this->errorResponse("No se encontraron datos del alumno para generar el informe completo", 404);
            }
            $assistences = $this->transformData($results[0]['assitences'] ?? collect(), AssitanceReportByStudentTransformer::class);
            $assistences = $assistences['data'];

            $tipoAsistencias = TypeAssistance::all();
            $countByTypeAssistence = [];
            $count = 0;
            foreach ($tipoAsistencias as $typeAssistence) {
                $nameTypeAssistence = $typeAssistence->nombre;
                foreach ($assistences as $assistence) {
                    if ($nameTypeAssistence == $assistence['typeAssistance']['nameTypeAssistance']['nombre']) {
                        $count += 1;
                    }
                }
                $countByTypeAssistence[] = ["typeAssistance" => $nameTypeAssistence, "cant" => $count];
                $count = 0;
            }

            $evaluations = $this->transformData($results[0]['evaluations'] ?? collect(), EvaluationTransform::class);
            $evaluations = $evaluations['data'];

            $invoices = $this->transformData($invoices->get(), InvoiceTransform::class);
            $invoices = $invoices['data'];
            //PDF::loadView
            $pdf = PDF::loadView('reportes.completeByStudent', compact('student', 'assistences', 'evaluations', 'invoices', 'countByTypeAssistence', 'dateInit', 'dateFinish'));
            return $pdf->download('informe-completo.pdf');
        }
    }

    public function downloadInvoicesByLanguajeCourse(Request $request)
    {
        $languajeId = $request->languajeId ?? null;
        $courseId = $request->courseId ?? null;
        $month = $request->month ?? null;
        $dateInit = $request->dateInit ?? null;
        $dateFinish = $request->dateFinish ?? null;
        $course = ($courseId !== "null") ? Course::where('id', $courseId)->first() : null;
        $languaje = ($languajeId !== "null") ? Languaje::where('id', $languajeId)->first() : null;
        $invoices  = Invoice::query()->with(['studentCourse', 'studentCourse.course'])->orderBy('fecha_emision', 'DESC');

        $schoolYearId = $request->schoolYearId && $request->schoolYearId !== "null"
            ? (int) $request->schoolYearId
            : SchoolYear::where('active', true)->first()->id;


        $courses = ($languajeId !== "null") ?
            Course::query()->when($languajeId !== "null", function ($q) use ($languajeId, $schoolYearId) {
                return $q->whereNotNull('school_year_id')->where('school_year_id', $schoolYearId)->whereHas('level', function ($r) use ($languajeId) {
                    return $r->where('id_idioma', $languajeId);
                });
            })->orderBy('id', 'ASC')->get()
            :
            null;

        // Misma lógica que getReporybyLanguajeCourse (pantalla y PDF coinciden)
        $sy = SchoolYear::find($schoolYearId);
        $yearNumber = $sy ? $sy->year : date('Y');
        $invoices->where(function ($q) use ($yearNumber, $schoolYearId) {
            $q->where(function ($q2) use ($yearNumber) {
                $q2->whereNotNull('fecha_emision')->whereYear('fecha_emision', $yearNumber);
            })->orWhereHas('studentCourse', function ($r) use ($schoolYearId) {
                $r->whereHas('course', function ($c) use ($schoolYearId) {
                    $c->where('school_year_id', $schoolYearId);
                });
            });
        });
        if ($this->reportParam($dateInit) && $this->reportParam($dateFinish)) {
            $invoices->whereBetween('fecha_emision', [$dateInit, $dateFinish]);
        }
        if ($this->reportParam($courseId)) {
            $invoices->whereHas('studentCourse', function ($r) use ($courseId) {
                return $r->where('id_curso', (int) $courseId);
            });
        } elseif ($this->reportParam($languajeId)) {
            $invoices->whereHas('studentCourse', function ($r) use ($languajeId, $schoolYearId) {
                return $r->whereHas('course', function ($r) use ($languajeId, $schoolYearId) {
                    return $r->where('school_year_id', $schoolYearId)->whereHas('level', function ($r) use ($languajeId) {
                        return $r->where('id_idioma', (int) $languajeId);
                    });
                });
            });
        }
        $invoices = $this->transformData($invoices->get(), InvoiceTransform::class);

        $invoices = $invoices['data'];
        $pdf = PDF::loadView('reportes.invoicesByLanguajeCourse', compact('invoices', 'courses', 'course', 'languaje', 'dateInit', 'dateFinish', 'month'));
        return $pdf->download('repote-por-idioma-y-curso.pdf');
    }

    public function downloadPaymentTeacher(Request $request)
    {
        $teacherId =  $request->teacherId;
        $dateInit = $request->dateInit;
        $dateFinish = $request->dateFinish;
        $schoolYearId = $request->schoolYearId && $request->schoolYearId !== "null"
            ? (int) $request->schoolYearId
            : null;
        $schoolYear = $schoolYearId ? SchoolYear::find($schoolYearId) : SchoolYear::where('active', true)->first();
        $reportsTeacher = PaymentTeacher::query()->orderBy('fecha', 'DESC');
        $teacher = ($teacherId !== "null") ? Teacher::where('id', $teacherId)->first() : null;
        $teacher = $teacher ? $this->transformData($teacher, TeacherTransform::class) : null;
        // Misma lógica que getReportPaymentTeacher (pantalla y PDF coinciden)
        $hasRange = $this->reportParam($dateInit) && $this->reportParam($dateFinish);
        $reportsTeacher->when($this->reportParam($teacherId), function ($q) use ($teacherId) {
            return $q->where('id_profesor', (int) $teacherId);
        });
        $reportsTeacher->when($hasRange, function ($q) use ($dateInit, $dateFinish) {
            return $q->whereBetween('fecha', [$dateInit, $dateFinish]);
        });
        $reportsTeacher->when(!$hasRange && $schoolYear, function ($q) use ($schoolYear) {
            return $q->whereYear('fecha', $schoolYear->year);
        });

        $reportsTeacher = $this->transformData($reportsTeacher->get(), PaymentTeacherTransform::class);
        $reportsTeacher = $reportsTeacher['data'];
        $pdf = PDF::loadView('reportes.paymentsByteachers', compact('reportsTeacher', 'teacher', 'dateInit', 'dateFinish'));
        return $pdf->download('repote-pagos-de-docentes.pdf');
    }

    public function reportAnalytical(Request $request)
    {
        $this->validate($request, [
            'studentId' => 'required|integer|exists:alumnos,id',
        ]);
        $courseId = $request->courseId;
        $studentId = $request->studentId;
        $dateInit = $request->dateInit;
        $dateFinish = $request->dateFinish;
        $schoolYearId = $request->schoolYearId && $request->schoolYearId !== "null"
            ? (int) $request->schoolYearId
            : SchoolYear::where('active', true)->first()->id;

        $student =  Student::query()->with(['evaluations','assitences'])->where('id', $studentId)->first();

        $data = [];
        $evaluations = $student->evaluations->whereBetween('fecha', [$dateInit, $dateFinish])->where('id_curso',$courseId);
        foreach($evaluations as $evaluation){
            $val = array_filter($evaluation->students()->get()->toArray(), function ($item) use ($student) {
                return $item["id"] === $student->id;
            });
            $key = array_keys($val);
            //dd($val[$key[0]]);
            $data[] = $val[$key[0]]['pivot'];
        }
        $result = $this->scoreFinal($data);


        $assitences = $student->assitences->filter(function ($a) use ($dateInit, $dateFinish) {
            $raw = $a->getRawOriginal('fecha');
            return $raw >= $dateInit && $raw <= $dateFinish;
        });
        $assistences = $this->transformData($assitences, AssitanceReportByStudentTransformer::class);
        $assistences = $assistences['data'];
        $tipoAsistencias = TypeAssistance::all();
        $countByTypeAssistence = [];
        $count = 0;
        foreach ($tipoAsistencias as $typeAssistence) {
            $nameTypeAssistence = $typeAssistence->nombre;
            foreach ($assistences as $assistence) {
                if ($nameTypeAssistence == $assistence['typeAssistance']['nameTypeAssistance']['nombre']) {
                    $count += 1;
                }
            }
            $countByTypeAssistence[] = ["typeAssistance" => $nameTypeAssistence, "cant" => $count];
            $count = 0;
        }

        $result = array_merge($result,["assistences" => $countByTypeAssistence]);

        return $this->successResponse($result,200);

    }

    public function downloadReportLanguajesLevelsCourses(Request $request){
        $languajeId =  $request->languajeId ?? "null";
        $levelId = $request->levelId ?? "null";
        $courseId =  $request->courseId ?? "null";
        $schoolYears = $request->schoolYears != "null" ? (int) $request->schoolYears : SchoolYear::where('active',true)->first()->id;

        $list = [];

        $languaje = $languajeId !== "null" ? Languaje::find($languajeId) : null;
        $level = $levelId !== "null" ? Level::find($levelId) : null;

        if ($languajeId !== "null" && $levelId !== "null" && $courseId !== "null"){

            $course = Course::where('id',$courseId)->first();

            if (!$course) {
                return $this->errorResponse("No se encontró el curso seleccionado", 404);
            }

            $students = Student::with(['courses' => function ($query) use ($schoolYears){
                $query->where('school_year_id', $schoolYears);
            }])
            ->whereHas('courses', function ($query) use ($course) {
                $query->where('id_curso', $course->id);
            })->get()->toArray();

            $list[] = [
                'idioma' => $languaje->nombre,
                'idioma_id' => $languaje->id,
                'nivel' => $level->nombre,
                'nivel_id' => $level->id,
                'curso' => $course->nombre,
                'estudiantes' => $students,
                'num_estudiantes' => count($students),
            ];
        }
        else if ($languaje  && $level ){

            $courses = $level->courses->where('school_year_id',$schoolYears)->pluck('nombre','id');

            foreach($courses as $courseId =>$courseName){

                $students = Student::with(['courses' => function ($query) use ($schoolYears){
                    $query->where('school_year_id', $schoolYears);
                }])
                ->whereHas('courses.level', function ($query) use ($courseId) {
                    $query->where('id_curso', $courseId);
                })->get()->toArray();

                $list[] = [
                    'idioma' => $languaje->nombre,
                    'idioma_id' => $languaje->id,
                    'nivel' => $level->nombre,
                    'nivel_id' => $level->id,
                    'curso' => $courseName,
                    'estudiantes' => $students,
                    'num_estudiantes' => count($students),
                ];
            }
        }
    
        else if ($languaje ){

            $levels = $languaje->levels;

            foreach ($levels as $level) {

                $courses = Course::where('school_year_id',$schoolYears)->where('id_nivel',$level->id)->get();
                // dd($courses['3 ADULTS']);
               
                foreach($courses as $course){
                
                    $partial = [
                        'idioma' => $languaje->nombre,
                        'idioma_id' => $languaje->id,
                        'nivel' => $level->nombre,
                        'nivel_id' => $level->id,
                        'curso' => $course->nombre,
                        'curso_id' => $course->id,
                        'estudiantes'=> null,
                        'num_estudiantes' => null
                    ];

                    $students = Student::with(['courses' => function ($query) use ($schoolYears){
                        $query->where('school_year_id', $schoolYears);
                    }])->whereHas('courses.level', function ($query) use ($course) {
                        $query->where('id_curso', $course->id);
                    })->get()->toArray();

                    $partial['estudiantes'] =  $students;
                    $partial['num_estudiantes'] =  count($students);

                    $list[] = $partial;

                }
            }
        }

        $pdf = PDF::loadView('reportes.listadoIdiomaNivelCurso', compact('list'))->setPaper('A3', 'portrait');
        
        return $pdf->download('reporte-listado-idioma-nivel-curso.pdf');
    }


    public function downloadReportAnalytical(Request $request)
    {
        $this->validate($request, [
            'studentId' => 'required|integer|exists:alumnos,id',
        ]);
        $courseId = $request->courseId;
        $oralidad = $request->oralidad;
        $participacion = $request->participacion;
        $cumplimiento = $request->cumplimiento;
        // SecDevOps: las observaciones son HTML del editor y se imprimen con {!! !!} en el PDF.
        // Se deja solo un subconjunto de etiquetas de formato y se quitan atributos (on*, style, src, href...).
        $observations = $request->observations;
        if (is_string($observations) && $observations !== 'null') {
            $observations = strip_tags($observations, '<p><br><b><strong><i><em><u><s><ul><ol><li><span><h1><h2><h3><h4><blockquote>');
            $observations = preg_replace('/<(\/?)([a-z0-9]+)[^>]*>/i', '<$1$2>', $observations);
        }

        $studentId = $request->studentId;
        $dateInit = $request->dateInit;
        $dateFinish = $request->dateFinish;

        $student =  Student::query()->with(['evaluations','assitences'])->where('id', $studentId)->first();

        $data = [];
        $evaluations = $student->evaluations->whereBetween('fecha', [$dateInit, $dateFinish])->where('id_curso',$courseId);
        foreach($evaluations as $evaluation){
            $val = array_filter($evaluation->students()->get()->toArray(), function ($item) use ($student) {
                return $item["id"] === $student->id;
            });
            $key = array_keys($val);
            //dd($val[$key[0]]);
            $data[] = $val[$key[0]]['pivot'];
        }
        $result = $this->scoreFinal($data);


        $assitences = $student->assitences->filter(function ($a) use ($dateInit, $dateFinish) {
            $raw = $a->getRawOriginal('fecha');
            return $raw >= $dateInit && $raw <= $dateFinish;
        });

        $assistences = $this->transformData($assitences, AssitanceReportByStudentTransformer::class);
        $assistences = $assistences['data'];

        $tipoAsistencias = TypeAssistance::all();
        $countByTypeAssistence = [];
        $count = 0;
        foreach ($tipoAsistencias as $typeAssistence) {
            $nameTypeAssistence = $typeAssistence->nombre;
            foreach ($assistences as $assistence) {
                if ($nameTypeAssistence == $assistence['typeAssistance']['nameTypeAssistance']['nombre']) {
                    $count += 1;
                }
            }
            $countByTypeAssistence[] = ["typeAssistance" => $nameTypeAssistence, "cant" => $count];
            $count = 0;
        }
        $result = array_merge($result,["assistences" => $countByTypeAssistence],['oralidad'=>$oralidad],['cumplimiento'=>$cumplimiento],['participacion'=>$participacion],['observations'=>$observations]);

        $course = Course::where('id',$courseId)->first();
        $pdf = PDF::loadView('reportes.analytical', compact('result','student','course','dateInit','dateFinish'));
        $pdf->setPaper('letter', 'portrait');

        // Render first, then add background image BEHIND content
        $dompdf = $pdf->getDomPDF();
        $dompdf->render();

        $canvas = $dompdf->getCanvas();
        $bgPath = public_path('img/cover-analitico.jpeg');
        $w = $canvas->get_width();
        $h = $canvas->get_height();

        // Create background image object
        $bgObjId = $canvas->open_object();
        $canvas->image($bgPath, 0, 0, $w, $h);
        $canvas->close_object();

        // Create date text object — aligned with "Fecha:" label in background image (first page only)
        // Background image is 904×1280px scaled to letter 612×792pt.
        // "Fecha:" label in image is at ~(545px, 198px) → in pt: x≈369, y≈122
        $dateObjId = $canvas->open_object();
        $dateStr = \Carbon\Carbon::now()->format('d/m/Y');
        $font = $dompdf->getFontMetrics()->getFont("Helvetica", "bold");
        $canvas->text(369, 103, $dateStr, $font, 10, [0.1, 0.1, 0.1]);
        $canvas->close_object();

        // Background on all pages; date text only on first page
        $cpdf = $canvas->get_cpdf();
        $pageIdx = 0;
        foreach ($cpdf->objects as &$obj) {
            if ($obj['t'] === 'page' && isset($obj['info']['contents'])) {
                array_unshift($obj['info']['contents'], $bgObjId);
                if ($pageIdx === 0) {
                    array_push($obj['info']['contents'], $dateObjId);
                }
                $pageIdx++;
            }
        }
        unset($obj);

        $studentName = preg_replace('/[^a-zA-Z0-9]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $student->nombreCompleto));
        $timestamp = \Carbon\Carbon::now()->format('d-m-Y_H-i');
        $filename = "Analitico-{$studentName}-{$timestamp}.pdf";

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);

    }

    private function scoreFinal(array $evaluations)
    {
        $scoreFinalEvaluation = [];
        $scoreFinalPractice = [];
    
        $result = [];
    
        // Inicializamos acumuladores y contadores individuales
        $areas = [
            'listening' => ['total' => 0, 'count' => 0],
            'vocabulary' => ['total' => 0, 'count' => 0],
            'languajeFocus' => ['total' => 0, 'count' => 0],
            'reading' => ['total' => 0, 'count' => 0],
            'communication' => ['total' => 0, 'count' => 0],
            'writing' => ['total' => 0, 'count' => 0],
            'oralExam' => ['total' => 0, 'count' => 0],
        ];
    
        $pending = 0;
        $delivered = 0;
        $assigned = 0;
        $count = 0;
    
        foreach ($evaluations as $e) {
            // Sumar cada área individual si no es null
            foreach ($areas as $key => &$data) {
                if (isset($e[$key]) && $e[$key] !== null) {
                    $data['total'] += $e[$key];
                    $data['count']++;
                }
            }
    
            // Prácticas
            if (isset($e['pending']) && $e['pending']) $pending++;
            if (isset($e['delivered']) && $e['delivered']) $delivered++;
            if (isset($e['assigned']) && $e['assigned']) $assigned++;
    
            $count++;
        }
    
        // Calcular promedios solo si hay notas válidas
        $score = [];
        foreach ($areas as $key => $data) {
            $score[$key] = $data['count'] > 0
                ? number_format($data['total'] / $data['count'], 2)
                : 0;
        }
    
        $scoreFinalEvaluation[] = $score;
    
        $scoreFinalPractice[] = [
            "pending" => $pending,
            "delivered" => $delivered,
            "assigned" => $assigned,
        ];
    
        return [
            "scoreFinal" => $scoreFinalEvaluation,
            "scoreFinalPractice" => $scoreFinalPractice,
            "units" => $count
        ];
    }
    
}