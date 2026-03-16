<?php

namespace App\Http\Controllers\Level;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Languaje;
use App\Models\Level;
use App\Models\SchoolYear;
use App\Models\TypeCourse;
use Illuminate\Http\Request;

class LevelController extends ApiController
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
        //
        $levels = Level::query()->with(['languaje','typeCourse'])->orderBy('id','DESC');

        $collections = $request->input('all') == 1
        ? $this->showList($levels->get()):
         $this->showAll($levels);
         return $collections;
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
            'name' => 'required|max:50|unique:niveles,nombre',
            'languaje' => 'required',
            'fee' => 'required',
            'typeCourse' => 'required',
            'monthInit' => 'required',
            'monthFinish' => 'required'
        ];

        $this->validate($request,$rules);

        $level = Level::create([
            "nombre" => $request->name,
            'cuota' => $request->fee,
            'mes_desde' => $request->typeCourse,
            'mes_hasta' => $request->monthFinish,
        ]);

        $level->languaje()->associate($request->languaje);
        $level->typeCourse()->associate($request->typeCourse);
        $level->save();
        return $this->showOne($level);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Level $level)
    {
        //
        $rules = [
            'name' => 'required|max:50|unique:niveles,nombre,'. $level->id,
            'languaje' => 'required',
            'fee' => 'required',
            'typeCourse' => 'required',
            'monthInit' => 'required',
            'monthFinish' => 'required'
        ];

        $this->validate($request,$rules);

        $level->nombre  = $request->name;
        $level->cuota = $request->fee;
        $level->mes_desde = $request->monthInit;
        $level->mes_hasta = $request->monthFinish;
        $level->languaje()->associate($request->languaje);
        $level->typeCourse()->associate($request->typeCourse);
        $level->save();

        return $this->showOne($level);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function destroy(Level $level)
    {
        //
        $level->delete();
        return $this->showOne($level);
    }


    public function getAmountCourse(Request $request){
        $SchoolYear = SchoolYear::where('active',true)->first();
        $courseId =$request->courseId;
        $course = Course::with('level')->where('id',$courseId)->where('school_year_id',$SchoolYear->id)->first();
        $level  = $course->level;
        return $this->showOne($level);
    }
}
