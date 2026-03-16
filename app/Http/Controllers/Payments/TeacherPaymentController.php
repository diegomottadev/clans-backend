<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\ApiController;
use App\Models\PaymentTeacher;
use Illuminate\Http\Request;

class TeacherPaymentController extends ApiController
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
        $paymentTeachers = PaymentTeacher::query()->with(['teacher'])->orderBy('id','DESC');
        $collections =  $request->input('all','') == 1
                        ?   $this->showList($paymentTeachers->get()) :
                            $this->showAll($paymentTeachers);
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
            'teacher' => 'required',
            'amount' => 'required',
        ];

        $this->validate($request,$rules);

        $paymentTeacher = PaymentTeacher::create([

            'fecha' => $request->date,
            'id_profesor' => $request->teacher,
            'monto' => $request->amount,

        ]);

        $paymentTeacher->load('teacher');
        return $this->showOne($paymentTeacher);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PaymentTeacher $paymentTeacher)
    {
        //
        $rules = [
            'date' => 'required',
            'teacher' => 'required',
            'amount' => 'required',
        ];

        $this->validate($request,$rules);

        $paymentTeacher->fecha = $request->date;
        $paymentTeacher->id_profesor = $request->teacher;
        $paymentTeacher->monto = $request->amount;
        $paymentTeacher->save();

        $paymentTeacher->load('teacher');
        return $this->showOne($paymentTeacher);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaymentTeacher $paymentTeacher)
    {
        //
        $paymentTeacher->load('teacher');
        $paymentTeacher->delete();
        return $this->showOne($paymentTeacher);
    }
}
