<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teacher;

class AddNameCompleteTeachers extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $teachers = Teacher::all();

        foreach($teachers as $teacher)
        {
            $name = $teacher->nombre;
            $apellido = $teacher->apellido;
            $teacher->nombreCompleto = $name." ".$apellido;
            $teacher->save();
        }
    }
}
