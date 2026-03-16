<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\ApiController;
use App\Models\Invoice;
use App\Models\SchoolYear;
use App\Models\Setting;
use App\Models\StudentCourse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InvoiceController extends ApiController
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
        $invoices =  Invoice::query()
                ->with(['student', 'student.courses', 'studentCourse', 'studentCourse.course'])
                ->orderBy('id','DESC');

        $collections = $request->input('all','') == 1
                        ? $this->showList($invoices->get()) :
                          $this->showAll($invoices);
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
            'student' => 'required',
            'studentCourse' => 'required',
            'dateIssue' => 'required',
            'month' => 'required',
            //'fee' => 'required',
        ];

        $this->validate($request,$rules);

        $studentCourse = StudentCourse::where('id_alumno',$request->student)->where('id_curso',$request->studentCourse)->first();

        $fee = $request->fee == "" ? 0 : (float) $request->fee;
        $mora = $request->has('mora') ? (float) $request->mora : 0;
        $total = $fee + $mora;

        $invoice = Invoice::create([
            'id_alumno' => $request->student,
            'id_alumno_curso' => $studentCourse->id,
            'fecha_emision' => $request->dateIssue,
            'mes' => $request->month,
            'cuota' => $fee,
            'mora' => $mora,
            'total' => $total,
        ]);
        $invoice->load(['student', 'student.courses', 'studentCourse', 'studentCourse.course']);
        return $this->showOne($invoice);

    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Invoice $invoice)
    {
        //
        $rules = [
            'student' => 'required',
            'studentCourse' => 'required',
            'dateIssue' => 'required',
            'month' => 'required',
            'fee' => 'required',
        ];

        $this->validate($request,$rules);
        $studentCourse = StudentCourse::where('id_alumno',$request->student)->where('id_curso',$request->studentCourse)->first();
        $invoice->id_alumno = $request->student;
        $invoice->id_alumno_curso = $studentCourse->id;
        $invoice->fecha_emision = $request->dateIssue;
        $invoice->mes = $request->month;
        $invoice->cuota = $request->fee;
        $invoice->save();
        $invoice->load(['student', 'student.courses', 'studentCourse', 'studentCourse.course']);
        return $this->showOne($invoice);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Invoice $invoice)
    {
        //
        $invoice->load(['student', 'student.courses', 'studentCourse', 'studentCourse.course']);
        $invoice->delete();
        return $this->showOne($invoice);
    }

    /**
     * GET /api/invoices/getMoraForMonth
     * Calcula la mora para un mes dado si está vencido.
     */
    public function getMoraForMonth(Request $request)
    {
        $month = (int) $request->input('month');
        $year = $request->input('year') ? (int) $request->input('year') : Carbon::today()->year;

        $today = Carbon::today();

        // Calcular deadline del mes en el año correspondiente
        $deadline = Carbon::create($year, $month, 10);
        if ($deadline->isSaturday()) $deadline->addDays(2);
        if ($deadline->isSunday()) $deadline->addDay();

        $interestRate = (float) Setting::getValue('interest_rate', '0');
        $mora = $today->gt($deadline) ? $interestRate : 0;

        return $this->successResponse([
            'mora' => $mora,
            'isOverdue' => $today->gt($deadline),
            'interestRate' => $interestRate,
        ], 200);
    }

    public function getInvoiceByStudentAndCourse(Request $request){
        $schoolYear = SchoolYear::where('active',true)->first();
        $studentCourse = StudentCourse::where('id_alumno',$request->student)->where('id_curso',$request->course)->first();
        $invoices = Invoice::with(['studentCourse.course'])->where('id_alumno_curso', $studentCourse->id)->where('id_alumno',$request->student)->whereBetween('fecha_emision', ["{$schoolYear->year}-01-01", "{$schoolYear->year}-12-31"])->orderBy('mes','DESC');
        $collections = $this->showList($invoices->get());
        return $collections;
    }
}
