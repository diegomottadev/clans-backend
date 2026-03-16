<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Invoice;
use App\Models\StudentCourse;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

class StudentCourseController extends ApiController
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Student $student)
    {
        //
        $rules = [
            'course_ids' =>'required',
        ];
        $this->validate($request,$rules);
        $courses =$request->course_ids;
        $c = [];
        $fecha = Carbon::parse($request->date_inscription)->format('Y-m-d');
        $year = SchoolYear::where('active',true)->orderBy('id','DESC')->first();
        foreach($courses as $key=>  $course){
            if (!$student->courses->contains($course)){
                $c[$course]=  ['fecha'=> $fecha,'anio_lectivo'=>$year->year];
                $student->courses()->attach($c);

            }
        }
        $student->save();

        return $this->showOne($student);
    }


    public function update(Request $request, Student $student,Course $course)
    {
        //
        $courseCurrent = $course;
        $courseByChange = $request->course_id;
        $student_id = $student->id;
        $year = SchoolYear::where('active',true)->orderBy('id','DESC')->first();
        // dd($student_id,$courseCurrent->id,$year- >year);
        $studentCourse = StudentCourse::where('id_alumno',$student_id)->where('id_curso',$courseCurrent->id)->where('anio_lectivo',$year->year)->first();
        $studentCourse->id_curso =  $courseByChange;
        $studentCourse->save();


        return $this->showOne($student);
    }

    public function destroy( Student $student,Course $course)
    {
        //
        $student->courses()->detach($course->id);
        $student->save();

        return $this->showOne($student);
    }

}
