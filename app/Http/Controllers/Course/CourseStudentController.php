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
        if (!$this->canAccessCourse($course->id)) {
            return $this->forbiddenCourse();
        }
        // Solo cursos habilitados del año lectivo activo; si no lo es, lista vacía (no 500)
        $schoolYearId = SchoolYear::where('active', true)->value('id');
        if (!$course->status || (int) $course->school_year_id !== (int) $schoolYearId) {
            return $this->successResponse([], 200);
        }
        $course->load('students');
        return $this->successResponse($course->toArray()['students'], 200);
    }

}
