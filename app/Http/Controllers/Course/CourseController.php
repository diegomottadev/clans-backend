<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class CourseController extends ApiController
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
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $schoolYearId = $request->input('schoolYearId');
        if ($schoolYearId !== null && $schoolYearId !== '' && $schoolYearId !== 'null') {
            $schoolYearId = (int) $schoolYearId;
        } else {
            $schoolYear = cache()->remember('active_school_year', 3600, function () {
                return SchoolYear::where('active', true)->first();
            });
            $schoolYearId = $schoolYear ? $schoolYear->id : null;
        }
        $courses = Course::query()
                        ->with([
                            'teacher:id,nombre,apellido',
                            'level:id,nombre',
                            'students:id,nombre,apellido,nombreCompleto',
                        ])
                        ->orderBy('id','DESC');
        if ($schoolYearId !== null) {
            $courses->where('school_year_id', $schoolYearId);
        }

        if(auth()->user()->role == 'user'){
            $courses = $courses->where('id_profesor',auth()->user()->id_profesor);
        }
        $collections = $request->input('all','') == 1
        ? $this->showList($courses->get()):
         $this->showAll($courses);
         return $collections;
    }


    public function show(Course $course)
    {
        //
        $course = Course::with(['students:id,nombre,apellido,nombreCompleto', 'teacher:id,nombre,apellido'])->find($course->id);
        return $this->showOne($course);
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
            'name' => 'required|max:20|unique:cursos,nombre',
            'teacher' =>'required',
            'level' => 'required'
        ];

        $this->validate($request,$rules);
        $schoolYear = cache()->remember('active_school_year', 3600, function () {
            return SchoolYear::where('active', true)->first();
        });
        $course = Course::create([
            'nombre' => $request->name,
            'school_year_id'=> $schoolYear->id
        ]);

        $course->teacher()->associate($request->teacher);
        $course->level()->associate($request->level);
        $course->save();
        $course->load(['teacher:id,nombre,apellido', 'level:id,nombre', 'students:id,nombre,apellido,nombreCompleto']);
        return $this->showOne($course);
    }

    public function update(Request $request, Course $course)
    {
        //
        $rules = [
/*             'name' => 'required|max:20|unique:cursos,nombre,'. $course->id,
 */
            'teacher' =>'required',
            'level' => 'required'
        ];

        $this->validate($request,$rules);

        $course->nombre = $request->name;
        $course->status = $request->status;
        $course->teacher()->associate($request->teacher);
        $course->level()->associate($request->level);
        $course->save();
        $course->load(['teacher:id,nombre,apellido', 'level:id,nombre', 'students:id,nombre,apellido,nombreCompleto']);
        return $this->showOne($course);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function destroy(Course $course)
    {
        //
        $course->load(['teacher:id,nombre,apellido', 'level:id,nombre', 'students:id,nombre,apellido,nombreCompleto']);
        $course->delete();
        return $this->showOne($course);
    }
}
