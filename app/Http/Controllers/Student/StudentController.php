<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\StudentCourse;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentController extends ApiController
{
        /**
     *
     * @return void
     */
    public function __construct()
    {
/*         $this->middleware(
            'auth:api'
        ); */
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        Log::channel('single')->info('[StudentController::index] Request', [
            'query' => $request->query(),
            'schoolYearId' => $request->schoolYearId,
            'status' => $request->status,
            'noCourse' => $request->noCourse,
            'completeName' => $request->completeName,
            'all' => $request->input('all'),
        ]);

        $students = Student::query()->with('courses')->orderBy('id', 'DESC');

        if ($request->completeName) {
            $students->where('nombreCompleto', 'like', '%' . $request->completeName . '%');
        }

        $schoolYearId = null;
        if ($request->schoolYearId && $request->schoolYearId !== 'null') {
            $schoolYearId = (int) $request->schoolYearId;
        } else {
            $schoolYear = SchoolYear::where('active', true)->first();
            if ($schoolYear) {
                $schoolYearId = $schoolYear->id;
            }
        }

        $filterNoCourse = $request->boolean('noCourse') || $request->input('noCourse') === '1';
        if ($filterNoCourse && $schoolYearId === null) {
            $schoolYear = SchoolYear::where('active', true)->first();
            if ($schoolYear) {
                $schoolYearId = $schoolYear->id;
            }
        }

        if ($filterNoCourse && $schoolYearId !== null) {
            // Filtro "Sin cursos": estudiantes que no tienen ningún curso en el año indicado
            $students->whereDoesntHave('courses', function ($q) use ($schoolYearId) {
                $q->where('school_year_id', $schoolYearId);
            });
            Log::channel('single')->info('[StudentController::index] Filtro sin cursos en año', ['schoolYearId' => $schoolYearId]);
        } elseif ($schoolYearId !== null) {
            // Filtro por ciclo: solo quienes tienen al menos un curso en ese año
            $students->whereHas('courses', function ($q) use ($schoolYearId) {
                $q->where('school_year_id', $schoolYearId);
            });
            Log::channel('single')->info('[StudentController::index] Filtro por schoolYearId', ['schoolYearId' => $schoolYearId]);
        }

        // Filtro por idioma (a través de curso → nivel → idioma)
        if ($request->languageId && $request->languageId !== 'null') {
            $langId = (int) $request->languageId;
            $students->whereHas('courses', function ($q) use ($langId, $schoolYearId) {
                $q->whereHas('level', function ($lq) use ($langId) {
                    $lq->where('id_idioma', $langId);
                });
                if ($schoolYearId !== null) {
                    $q->where('school_year_id', $schoolYearId);
                }
            });
        }

        // Filtro por curso
        if ($request->courseId && $request->courseId !== 'null') {
            $cId = (int) $request->courseId;
            $students->whereHas('courses', function ($q) use ($cId) {
                $q->where('cursos.id', $cId);
            });
        }

        // Contadores totales (activos e inactivos con curso en el año; no aplican cuando noCourse=1)
        $totalActive = (clone $students)->where('activo', true)->count();
        $totalInactive = (clone $students)->where('activo', false)->count();

        // Por defecto solo estudiantes activos; si envían status se respeta (no aplica cuando noCourse=1 para mostrar todos sin curso)
        if (!$filterNoCourse) {
            if ($request->has('status') && $request->status !== null && $request->status !== '') {
                $students->where('activo', (int) $request->status);
                Log::channel('single')->info('[StudentController::index] Filtro status explícito', ['activo' => (int) $request->status]);
            } else {
                $students->where('activo', true);
                Log::channel('single')->info('[StudentController::index] Filtro status por defecto: activo=true');
            }
        }

        $totalBeforeResponse = $students->count();
        Log::channel('single')->info('[StudentController::index] Total estudiantes tras filtros', ['count' => $totalBeforeResponse]);

        $collections = $request->input('all', '') == 1
            ? $this->showList($students->get())
            : $this->showAll($students);

        // Inyectar contadores totales (activos e inactivos) en la respuesta
        $payload = $collections->getData(true);
        $payload['counts'] = [
            'active'   => $totalActive,
            'inactive' => $totalInactive,
        ];
        return response()->json($payload);
    }

    /**
     * Contadores de regulares e inactivos por año lectivo.
     * Sin schoolYearId → ciclo lectivo actual. Con schoolYearId → ese año.
     * noCourse: activos que no tienen ninguna inscripción (alumnos_cursos) en el ciclo,
     * considerando tanto que el curso sea del ciclo (school_year_id) como que la fila
     * del pivot tenga anio_lectivo = año del ciclo (consistente con cómo se guarda al inscribir).
     * Respuesta: { data: { schoolYearId, year, active, inactive, noCourse } }
     */
    public function counts(Request $request)
    {
        $schoolYear = null;
        if ($request->schoolYearId && $request->schoolYearId !== 'null') {
            $schoolYear = SchoolYear::find((int) $request->schoolYearId);
        } else {
            $schoolYear = SchoolYear::where('active', true)->first();
        }

        if ($schoolYear === null) {
            return response()->json([
                'data' => [
                    'schoolYearId' => null,
                    'year' => null,
                    'active' => 0,
                    'inactive' => 0,
                    'noCourse' => 0,
                ],
            ], 200);
        }

        $schoolYearId = $schoolYear->id;
        $yearNumber = (int) $schoolYear->year;

        $baseScope = function ($q) use ($schoolYearId) {
            $q->where('school_year_id', $schoolYearId);
        };

        $active = Student::query()
            ->where('activo', 1)
            ->whereHas('courses', $baseScope)
            ->count();

        $inactive = Student::query()
            ->where('activo', 0)
            ->whereHas('courses', $baseScope)
            ->count();

        // Sin curso en este ciclo: alumnos (activos e inactivos) que no tienen ningún curso
        // con school_year_id = este año. Misma lógica que report_alumnos_sin_curso_anio_actual.sql
        $noCourse = Student::query()
            ->whereDoesntHave('courses', $baseScope)
            ->count();

        return response()->json([
            'data' => [
                'schoolYearId' => $schoolYearId,
                'year' => $yearNumber,
                'active' => $active,
                'inactive' => $inactive,
                'noCourse' => $noCourse,
            ],
        ], 200);
    }

    public function show(Student $student)
    {
        //
        $students = Student::with('courses')->find($student->id);
        return $this->showOne($students);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $rules = [
            'dni' => 'required|max:12|unique:alumnos',
            'name' =>'required',
            'last_name' => 'required'
        ];

        $this->validate($request,$rules);

        $student = Student::create([
            'apellido'=> $request->last_name,
            'nombre'=> $request->name,
            'nombreCompleto' => $request->name." ".$request->last_name,
            'fecha_nac'=> $request->date_of_birth ,
            'dni'=> $request->dni,
            'domicilio'=> $request->address,
            'telefono'=> $request->telphone,
            'barrio'=> $request->neighborhood,
            'observaciones'=> $request->observation,
            'escuela'=> $request->school,
            'turno_escolar'=> $request->shift,
            'horario_ed_fisica'=> $request->horario_hour_physical_education,
            'actividad'=> $request->activity]);

        $student->save();
        $student->load('courses');
        return $this->showOne($student);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Student $student)
    {
        //
        $rules = [
            'dni' => 'required|max:12|unique:alumnos,dni,'.$student->id,
            'name' =>'required',
            'last_name' => 'required'
        ];

        $this->validate($request,$rules);

        $student->apellido = $request->last_name;
        $student->nombre = $request->name;
        $student->nombreCompleto = $request->name." ".$request->last_name;
        $student->fecha_nac = $request->date_of_birth;
        $student->dni = $request->dni;
        $student->domicilio = $request->address;
        $student->telefono = $request->telphone;
        $student->barrio = $request->neighborhood;
        $student->observaciones = $request->observation;
        $student->escuela = $request->school;
        $student->turno_escolar = $request->shift;
        $student->horario_ed_fisica = $request->horario_hour_physical_education ;
        $student->actividad = $request->activity ;
        $student->activo = $request->status ;

        $student->save();
        $student->load('courses');
        return $this->showOne($student);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function destroy(Student $student)
    {
        //
        $student->load('courses');
        $student->delete();
        return $this->showOne($student);
    }

    public function getLastInvoicePay(Request $request){
        $SchoolYear = SchoolYear::where('active',true)->first();
        $courseId = $request->courseId;
        $studentId = $request->studentId;

        // Verificar si tiene deuda del año anterior
        $previousSchoolYear = SchoolYear::where('year', $SchoolYear->year - 1)->first();
        if ($previousSchoolYear) {
            $prevEnrollment = StudentCourse::where('id_alumno', $studentId)
                ->whereHas('course', function ($q) use ($previousSchoolYear) {
                    $q->where('school_year_id', $previousSchoolYear->id);
                })->with('course.level')->first();

            if ($prevEnrollment) {
                // Buscar meses pagados de esa inscripción (sin filtrar por fecha, porque se puede pagar en el año siguiente)
                $prevPaidMonths = Invoice::where('id_alumno_curso', $prevEnrollment->id)
                    ->whereNull('deleted_at')
                    ->pluck('mes')
                    ->map(function ($m) { return (int) $m; })
                    ->toArray();

                $prevEnrollmentMonth = $prevEnrollment->fecha
                    ? Carbon::parse($prevEnrollment->fecha)->month
                    : 3;
                $prevStartMonth = max($prevEnrollmentMonth, 3);

                // Buscar primer mes impago del año anterior
                for ($m = $prevStartMonth; $m <= 12; $m++) {
                    if (!in_array($m, $prevPaidMonths)) {
                        // Tiene deuda del año anterior: devolver datos para que pague ese mes primero
                        return $this->successResponse([
                            'data' => [
                                'month' => (string) ($m - 1), // mes anterior al impago, para que el frontend ofrezca $m
                                'fee' => (string) ($prevEnrollment->course->level->cuota ?? 0),
                                'hasPreviousYearDebt' => true,
                                'previousYear' => $previousSchoolYear->year,
                                'previousEnrollmentId' => $prevEnrollment->id,
                                'previousCourseId' => $prevEnrollment->id_curso,
                                'debtMonth' => $m,
                            ]
                        ], 200);
                    }
                }
            }
        }

        // Sin deuda anterior: buscar última factura del año actual
        $invoice = Invoice::with('studentCourse')
                            ->whereHas('studentCourse', function ($r) use ($courseId) {
                                return $r->where('id_curso', $courseId);
                            })
                            ->whereBetween('fecha_emision', ["{$SchoolYear->year}-01-01", "{$SchoolYear->year}-12-31"])
                            ->where('id_alumno', $studentId)->orderBy('id', 'DESC')->first();
        return $this->showOne($invoice);
    }
}
