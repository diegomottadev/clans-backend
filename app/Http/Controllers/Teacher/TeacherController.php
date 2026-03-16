<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends ApiController
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
        $teachers = Teacher::query()->orderBy('id','DESC');
        $collections = $request->input('all','') == 1
                        ? $this->showList($teachers->get()) :
                          $this->showAll($teachers);
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
            'name' => 'required|max:15',
            'last_name' => 'required|max:15',
            'dni' => 'required|max:12|unique:profesores',
            'email' => 'email',
        ];

        $this->validate($request,$rules);

        $teacher = Teacher::create([
            'apellido' => $request->last_name,
            'nombre'=>$request->name,
            'dni' => $request->dni,
            'tel_part'=> $request->telphone,
            'cel_part'=> $request->celphone,
            'email'=> $request->email,
            'domicilio'=>$request->address,
            'nombreCompleto'=> $request->name." ".$request->last_name
        ]);

        return $this->showOne($teacher);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Teacher  $teacher
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Teacher $teacher)
    {
        //

        $rules = [
            'name' => 'required|max:15',
            'last_name' => 'required|max:15',
            'dni' => 'required|max:20|unique:profesores,dni,'.$teacher->id,
            //'email' => 'email',
        ];

        $this->validate($request,$rules);

        $teacher->apellido =$request->last_name;
        $teacher->nombre = $request->name;
        $teacher->dni = $request->dni;;
        $teacher->tel_part = $request->telphone;
        $teacher->cel_part = $request->celphone;
        $teacher->email = $request->email;
        $teacher->domicilio =$request->address;
        $teacher->nombreCompleto = $request->name." ".$request->last_name;
        $teacher->save();

        return $this->showOne($teacher);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Teacher  $teacher
     * @return \Illuminate\Http\Response
     */
    public function destroy(Teacher $teacher)
    {
        //
        $teacher->delete();
        return $this->showOne($teacher);
    }
}
