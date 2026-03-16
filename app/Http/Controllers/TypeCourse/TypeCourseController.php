<?php

namespace App\Http\Controllers\TypeCourse;

use App\Http\Controllers\ApiController;
use App\Models\TypeCourse;
use Illuminate\Http\Request;

class TypeCourseController extends ApiController
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
        $typeCourses = TypeCourse::query()->orderBy('id','DESC');
        $collections = $request->input('all','') == 1
        ? $this->showList($typeCourses->get()) :
         $this->showAll($typeCourses);
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
            'name' => 'required|max:20|unique:tipos_cursado,nombre',
        ];

        $this->validate($request,$rules);

        $typeCourse = TypeCourse::create([
            "nombre" => $request->name
        ]);

        return $this->showOne($typeCourse);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TypeCourse $typeCourse)
    {
        //

        $rules = [
            'name' => 'required|max:20|unique:tipos_cursado,nombre,'.$typeCourse->id,
        ];

        $this->validate($request,$rules);

        $typeCourse->nombre = $request->name;
        $typeCourse->save();

        return $this->showOne($typeCourse);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(TypeCourse $typeCourse)
    {
        //
        $typeCourse->delete();
        return $this->showOne($typeCourse,204);
    }
}
