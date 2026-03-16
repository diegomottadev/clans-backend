<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class CourseStudentController extends ApiController
{

    /**
     *
     * @return void
     */
/*     public function __construct()
    {
        $this->middleware(
            'auth:api'
        );
    } */
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Course $course)
    {
        //
        $schoolYear = SchoolYear::where('active',true);
        $course = Course::query()
                        ->with('students')
                        ->where('school_year_id',$schoolYear->first()->id)
                        ->where('status',true)
                        ->where('id',$course->id)
                        ->orderBy('id','ASC')->get()->toArray();
         return $this->successResponse($course[0]["students"],200);
    }

}
