<?php

namespace App\Http\Controllers\TypeEvaluation;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\TypeEvaluation;
use Illuminate\Http\Request;

class TypeEvaluationController extends ApiController
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
        // Usar caché para mejorar el rendimiento
        $typeEvalutions = cache()->remember('type_evaluations_all', 3600, function () {
            return TypeEvaluation::query()->orderBy('id','DESC')->get();
        });
        
        $collections = $request->input('all','') == 1
                    ? $this->showList($typeEvalutions) :
                      $this->showAll(TypeEvaluation::query()->orderBy('id','DESC'));
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
            'name' => 'required|max:20|unique:tipo_evaluacion,nombre',
        ];

        $this->validate($request,$rules);

        $typeEvalution = TypeEvaluation::create([
            "nombre" => $request->name
        ]);
        
        // Limpiar caché de typeEvaluations
        cache()->forget('type_evaluations_all');

        return $this->showOne($typeEvalution);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TypeEvaluation  $typeEvalution
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TypeEvaluation $typeEvalution)
    {
        //
        $rules = [
            'name' => 'required|max:20|unique:tipo_evaluacion,nombre,'. $typeEvalution->id,
        ];

        $this->validate($request,$rules);

        $typeEvalution->nombre = $request->name;
        $typeEvalution->save();
        
        // Limpiar caché de typeEvaluations
        cache()->forget('type_evaluations_all');

        return $this->showOne($typeEvalution);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TypeEvaluation  $typeEvalution
     * @return \Illuminate\Http\Response
     */
    public function destroy(TypeEvaluation $typeEvalution)
    {
        //
        $typeEvalution->delete();
        
        // Limpiar caché de typeEvaluations
        cache()->forget('type_evaluations_all');
        
        return $this->showOne($typeEvalution);
    }
}
