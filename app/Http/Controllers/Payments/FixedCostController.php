<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\FixedCost;
use Illuminate\Http\Request;

class FixedCostController extends ApiController
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
        $fixedCosts = FixedCost::query()->with(['typeExpense'])->orderBy('id','DESC');
        $collections =  $request->input('all','') == 1
                        ?   $this->showList($fixedCosts->get()) :
                            $this->showAll($fixedCosts);
         return $collections;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $rules = [
            'date' => 'required',
            'typeExpense' => 'required',
            'amount' => 'required',
        ];

        $this->validate($request,$rules);

        $fixedCost = FixedCost::create([

            'fecha' => $request->date,
            'id_tipo_egreso' => $request->typeExpense,
            'monto' => $request->amount,

        ]);

        $fixedCost->load('typeExpense');
        return $this->showOne($fixedCost);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FixedCost $fixedCost)
    {
        //
        $rules = [
            'date' => 'required',
            'typeExpense' => 'required',
            'amount' => 'required',
        ];

        $this->validate($request,$rules);

        $fixedCost->fecha = $request->date;
        $fixedCost->id_tipo_egreso = $request->typeExpense;
        $fixedCost->monto = $request->amount;
        $fixedCost->save();

        $fixedCost->load('typeExpense');
        return $this->showOne($fixedCost);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(FixedCost $fixedCost)
    {
        //
        $fixedCost->load('typeExpense');
        $fixedCost->delete();
        return $this->showOne($fixedCost);
    }
}
