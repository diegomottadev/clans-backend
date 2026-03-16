<?php

namespace App\Http\Controllers\TypeAssistance;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\TypeAssistance;
use Illuminate\Http\Request;

class TypeAssistanceController extends ApiController
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
        $typeAssistances = cache()->remember('type_assistances_all', 3600, function () {
            return TypeAssistance::query()->orderBy('id','DESC')->get();
        });
        
        $collections = $request->input('all','') == 1
                    ? $this->showList($typeAssistances) :
                    $this->showAll(TypeAssistance::query()->orderBy('id','DESC'));
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
            'name' => 'required|max:20|unique:tipos_asistencias,nombre',
        ];

        $this->validate($request,$rules);

        $typeAssistance = TypeAssistance::create([
            "nombre" => $request->name
        ]);
        
        // Limpiar caché de typeAssistances
        cache()->forget('type_assistances_all');

        return $this->showOne($typeAssistance);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TypeAssistance  $typeAssistance
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TypeAssistance $typeAssistance)
    {

        $rules = [
            'name' => 'required|max:20|unique:tipos_asistencias,nombre,'. $typeAssistance->id,
        ];

        $this->validate($request,$rules);

        $typeAssistance->nombre = $request->name;
        $typeAssistance->save();
        
        // Limpiar caché de typeAssistances
        cache()->forget('type_assistances_all');

        return $this->showOne($typeAssistance);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TypeAssistance  $typeAssistance
     * @return \Illuminate\Http\Response
     */
    public function destroy(TypeAssistance $typeAssistance)
    {
        //
        $typeAssistance->delete();
        
        // Limpiar caché de typeAssistances
        cache()->forget('type_assistances_all');
        
        return $this->showOne($typeAssistance,204);
    }
}
