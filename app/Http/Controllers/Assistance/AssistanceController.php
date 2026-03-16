<?php

namespace App\Http\Controllers\Assistance;

use App\Http\Controllers\ApiController;
use App\Models\Assistance;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class AssistanceController extends ApiController
{
    //

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
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Assistance::query()->with(['course', 'studentsAssisted'])->orderBy('id', 'DESC');

        if (auth()->user()->role == 'user') {
            $query->whereHas('course', function ($q) {
                $q->where('id_profesor', auth()->user()->id_profesor);
            });
        }

        // Año lectivo — filtrar por año de la fecha (no por school_year_id del curso, ya que muchos cursos heredados tienen school_year_id = NULL)
        $schoolYearId = $request->input('schoolYearId');
        if ($schoolYearId !== null && $schoolYearId !== '' && $schoolYearId !== 'null') {
            $sy = SchoolYear::find((int) $schoolYearId);
            if ($sy) {
                $query->whereYear('fecha', $sy->year);
            }
        }

        // Nivel
        $levelId = $request->input('levelId');
        if ($levelId !== null && $levelId !== '' && $levelId !== 'null') {
            $query->whereHas('course', function ($q) use ($levelId) {
                $q->where('id_nivel', (int) $levelId);
            });
        }

        // Curso (por id o nombre parcial)
        $courseAssistence = $request->input('courseAssistence');
        if ($courseAssistence !== null && $courseAssistence !== '') {
            if (is_numeric($courseAssistence)) {
                $query->where('id_curso', (int) $courseAssistence);
            } else {
                $query->whereHas('course', function ($q) use ($courseAssistence) {
                    $q->where('nombre', 'ilike', '%' . $courseAssistence . '%');
                });
            }
        }

        // Fecha
        $dateAssistence = $request->input('dateAssistence');
        if ($dateAssistence !== null && $dateAssistence !== '' && $dateAssistence !== 'null') {
            $query->where('fecha', $dateAssistence);
        }

        $collections = $request->input('all') == 1
            ? $this->showList($query->get())
            : $this->showAll($query);

        return $collections;
    }

    public function counts(Request $request)
    {
        $query = Assistance::query();

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

        $courseAssistence = $request->input('courseAssistence');
        if ($courseAssistence !== null && $courseAssistence !== '') {
            if (is_numeric($courseAssistence)) {
                $query->where('id_curso', (int) $courseAssistence);
            } else {
                $query->whereHas('course', function ($q) use ($courseAssistence) {
                    $q->where('nombre', 'ilike', '%' . $courseAssistence . '%');
                });
            }
        }

        $dateAssistence = $request->input('dateAssistence');
        if ($dateAssistence !== null && $dateAssistence !== '' && $dateAssistence !== 'null') {
            $query->where('fecha', $dateAssistence);
        }

        $total        = (clone $query)->count();
        $withStudents = (clone $query)->has('studentsAssisted')->count();

        return response()->json([
            'data' => [
                'total'            => $total,
                'with_students'    => $withStudents,
                'without_students' => $total - $withStudents,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $rules = [
            'date' => 'required',
            'course' =>'required',
        ];

        $this->validate($request,$rules);

        $assistance = Assistance::create([
            'fecha' =>  $request->date,
        ]);

        $assistance->course()->associate($request->course);
        $assistance->save();
        $assistance->load(['course', 'studentsAssisted']);
        return $this->showOne($assistance);
    }

    public function update(Request $request, Assistance $assistance){
        $rules = [
            'date' => 'required',
        ];

        $this->validate($request,$rules);
        $assistance->fecha  = $request->date;
        $assistance->save();
        $assistance->load(['course', 'studentsAssisted']);
        return $this->showOne($assistance);
    }

    public function destroy(Assistance $assistance){
        $assistance->load(['course', 'studentsAssisted']);
        $assistance->delete();
        return $this->showOne($assistance);
    }

}
