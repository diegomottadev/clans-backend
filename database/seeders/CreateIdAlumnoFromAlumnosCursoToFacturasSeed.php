<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\StudentCourse;
use Illuminate\Database\Seeder;

class CreateIdAlumnoFromAlumnosCursoToFacturasSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $invoices = Invoice::all();
        foreach($invoices as $invoice){
            if ($invoice->id_alumno_curso !=null){
                $studentCourse = StudentCourse::find($invoice->id_alumno_curso);
                $invoice->id_alumno =  $studentCourse->id_alumno;
                $invoice->save();
            }
        }
    }
}
