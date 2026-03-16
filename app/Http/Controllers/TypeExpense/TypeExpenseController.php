<?php

namespace App\Http\Controllers\TypeExpense;

use App\Http\Controllers\ApiController;
use App\Models\TypeExpense;
use Illuminate\Http\Request;

class TypeExpenseController extends ApiController
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
        $typeExpenses = TypeExpense::query()->orderBy('id','DESC');
        $collections = $request->input('all','') == 1
                    ? $this->showList($typeExpenses->get()) :
                      $this->showAll($typeExpenses);
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
            'name' => 'required|max:20|unique:tipos_egresos,nombre',
        ];

        $this->validate($request,$rules);

        $typeExpense = TypeExpense::create([
            "nombre" => $request->name
        ]);

        return $this->showOne($typeExpense);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TypeExpense  $typeExpense
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TypeExpense $typeExpense)
    {
        //
        $rules = [
            'name' => 'required|max:20|unique:tipos_egresos,nombre,'. $typeExpense->id,
        ];

        $this->validate($request,$rules);

        $typeExpense->nombre = $request->name;
        $typeExpense->save();

        return $this->showOne($typeExpense);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TypeExpense  $typeExpense
     * @return \Illuminate\Http\Response
     */
    public function destroy(TypeExpense $typeExpense)
    {
        //
        $typeExpense->delete();
        return $this->showOne($typeExpense);
    }
}
