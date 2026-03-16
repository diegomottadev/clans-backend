<?php

namespace Database\Seeders;

use App\Models\Concept;
use App\Models\Course;
use App\Models\Invoice;
use App\Models\SchoolYear;
use App\Models\StudentCourse;
use Illuminate\Database\Seeder;

class AddIdNivelInStudentCourse extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Verificar que este cargado el ño lectivo actual para usar este seed

        $schoolYear = SchoolYear::where('active',true)->first();
        $studentCourses =  StudentCourse::orderBy('id','DESC')->get();
        foreach ($studentCourses as $sc){
            $course = Course::with('level')->where('id',$sc->id_curso)->first();
            //dd($course->id_nivel);
            $sc->id_idioma = $course->level->id_idioma;
            $sc->id_nivel = $course->id_nivel;;
            $sc->save();
         }
    }
}
