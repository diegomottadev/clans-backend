<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class AddNameCompleteAllStudent extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $students = Student::all();

        foreach($students as $student)
        {
            $name = $student->nombre;
            $apellido = $student->apellido;
            $student->nombreCompleto = $name." ".$apellido;
            $student->save();
        }

    }
}
