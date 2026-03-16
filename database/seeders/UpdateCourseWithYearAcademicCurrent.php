<?php

namespace Database\Seeders;

use App\Models\Concept;
use App\Models\Course;
use App\Models\Invoice;
use App\Models\SchoolYear;
use App\Models\StudentCourse;
use Illuminate\Database\Seeder;

class UpdateCourseWithYearAcademicCurrent extends Seeder
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

        $studentCourses =  StudentCourse::where('anio_lectivo',$schoolYear->year)->orderBy('id','DESC')->get();

        foreach ($studentCourses as $sc){

            $courseOld = Course::where('id',$sc->id_curso)->first();
            $courseCurrent = Course::where('nombre',$courseOld->nombre)->where('school_year_id',$schoolYear->id)->first();
            $sc->id_curso = $courseCurrent->id;
            $sc->save();
        }
    }
}
