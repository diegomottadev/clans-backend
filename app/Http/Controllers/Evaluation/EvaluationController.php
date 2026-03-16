<?php

namespace App\Http\Controllers\Evaluation;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EvaluationController extends ApiController
{

    /**
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(
            'auth:api'
        );
    }

    /**
     * Display a listing of the resource.
     * Filtros (orden de aplicación): año lectivo (obligatorio), nombre (input libre), nivel, curso, fecha.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Evaluation::query()
            ->with([
                'course:id,nombre,id_profesor,id_nivel,school_year_id',
                'course.level:id,nombre',
                'typeEvaluation:id,nombre',
                'course.students:id,nombre,apellido,nombreCompleto',
            ])
            ->orderBy('id', 'DESC');

        if (auth()->user()->role == 'user') {
            $query->whereHas('course', function ($q) {
                $q->where('id_profesor', auth()->user()->id_profesor);
            });
        }

        // 1) Año lectivo — filtrar por año de la fecha (muchos cursos heredados tienen school_year_id = NULL)
        $schoolYearId = $request->input('schoolYearId');
        if ($schoolYearId !== null && $schoolYearId !== '' && $schoolYearId !== 'null') {
            $schoolYearId = (int) $schoolYearId;
            $sy = SchoolYear::find($schoolYearId);
            if ($sy) {
                $query->whereYear('fecha', $sy->year);
            }
        } else {
            $schoolYearId = null;
        }

        // 2) Input libre: nombre de la evaluación (opcional)
        $name = $request->input('name');
        if ($name !== null && $name !== '') {
            $query->where('nombre', 'ilike', '%' . $name . '%');
        }

        // 3) Nivel (opcional)
        $levelId = $request->input('levelId');
        if ($levelId !== null && $levelId !== '' && $levelId !== 'null') {
            $levelId = (int) $levelId;
            $query->whereHas('course', function ($q) use ($levelId) {
                $q->where('id_nivel', $levelId);
            });
        }

        // 4) Curso (opcional)
        $courseId = $request->input('courseId');
        if ($courseId !== null && $courseId !== '' && $courseId !== 'null') {
            $courseId = (int) $courseId;
            $query->where('id_curso', $courseId);
        }

        // 5) Fecha de evaluación (opcional)
        $date = $request->input('date');
        if ($date !== null && $date !== '' && $date !== 'null') {
            $query->where('fecha', $date);
        }

        Log::channel('single')->info('[EvaluationController::index] Filtros aplicados', [
            'schoolYearId_raw'    => $request->input('schoolYearId'),
            'schoolYearId_usado'  => $schoolYearId,
            'name'                => $name,
            'levelId'             => $request->input('levelId'),
            'courseId'            => $request->input('courseId'),
            'date'                => $date,
        ]);

        // Log del SQL generado y el total antes de paginar
        try {
            $sqlRaw   = $query->toSql();
            $bindings = $query->getBindings();
            $total    = (clone $query)->count();
            Log::channel('single')->info('[EvaluationController::index] SQL y conteo', [
                'sql'      => $sqlRaw,
                'bindings' => $bindings,
                'total'    => $total,
            ]);
        } catch (\Exception $e) {
            Log::channel('single')->error('[EvaluationController::index] Error al loguear SQL: ' . $e->getMessage());
        }


        $collections = $request->input('all', '') == 1
            ? $this->showList($query->get())
            : $this->showAll($query);

        return $collections;
    }


    public function counts(Request $request)
    {
        $query = Evaluation::query();

        if (auth()->user()->role == 'user') {
            $query->whereHas('course', function ($q) {
                $q->where('id_profesor', auth()->user()->id_profesor);
            });
        }

        $schoolYearId = $request->input('schoolYearId');
        if ($schoolYearId !== null && $schoolYearId !== '' && $schoolYearId !== 'null') {
            $sy = SchoolYear::find((int) $schoolYearId);
            if ($sy) {
                $query->whereYear('fecha', $sy->year);
            }
        }

        $levelId = $request->input('levelId');
        if ($levelId !== null && $levelId !== '' && $levelId !== 'null') {
            $query->whereHas('course', function ($q) use ($levelId) {
                $q->where('id_nivel', (int) $levelId);
            });
        }

        $courseId = $request->input('courseId');
        if ($courseId !== null && $courseId !== '' && $courseId !== 'null') {
            $query->where('id_curso', (int) $courseId);
        }

        $date = $request->input('date');
        if ($date !== null && $date !== '' && $date !== 'null') {
            $query->where('fecha', $date);
        }

        $name = $request->input('name');
        if ($name !== null && $name !== '') {
            $query->where('nombre', 'ilike', '%' . $name . '%');
        }

        $total     = (clone $query)->count();
        $withNotes = (clone $query)->has('students')->count();

        return response()->json([
            'data' => [
                'total'         => $total,
                'with_notes'    => $withNotes,
                'without_notes' => $total - $withNotes,
            ],
        ]);
    }

    public function store(Request $request)
    {
        //
        $rules = [
            'name' => 'required|max:60',
            'course' =>'required',
            'typeEvaluation' => 'required',
            'date' => 'required'
        ];

        $this->validate($request,$rules);

        $evaluation = Evaluation::create([
            'nombre'=> $request->name,
            'id_curso'=> $request->course,
            'id_tipo_evaluacion'=> $request->typeEvaluation ,
            'fecha'=> $request->date ,

          ]);

        $evaluation->save();
        $evaluation->load(['course.students', 'typeEvaluation', 'students']);
        return $this->showOne($evaluation);
    }

    public function update(Request $request, Evaluation $evaluation)
    {
        $rules = [
            'name' => 'required|max:80',
            'course' =>'required',
            'typeEvaluation' => 'required',
            'date' => 'required'
        ];

        $this->validate($request,$rules);

        $evaluation->nombre = $request->name;
        $evaluation->id_curso = $request->course;
        $evaluation->id_tipo_evaluacion = $request->typeEvaluation;
        $evaluation->fecha = $request->date;

        $evaluation->save();
        $evaluation->load(['course.students', 'typeEvaluation', 'students']);
        return $this->showOne($evaluation);
    }

    public function destroy(Evaluation $evaluation)
    {
        $evaluation->load(['course.students', 'typeEvaluation', 'students']);
        $evaluation->delete();
        return $this->showOne($evaluation);
    }
}
