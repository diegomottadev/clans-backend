<?php

namespace App\Http\Controllers\Languaje;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Languaje;
use Illuminate\Http\Request;

class LanguajeController extends ApiController
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
        $languajes = Languaje::query()->orderBy('id','DESC');
        $collections = $request->input('all','') == 1
        ? $this->showList($languajes->get()):
         $this->showAll($languajes);
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
            'name' => 'required|max:20|unique:idiomas,nombre',
        ];

        $this->validate($request,$rules);

        $languaje = Languaje::create([
            "nombre" => $request->name
        ]);

        return $this->showOne($languaje);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Languaje  $languaje
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Languaje $languaje)
    {
        //
        $rules = [
            'name' => 'required|max:20|unique:idiomas,nombre,'. $languaje->id,
        ];

        $this->validate($request,$rules);

        $languaje->nombre= request()->name;
        $languaje->save();

        return $this->showOne($languaje);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Languaje  $languaje
     * @return \Illuminate\Http\Response
     */
    public function destroy(Languaje $languaje)
    {
        //
        $languaje->delete();
        return $this->showOne($languaje);
    }
}
