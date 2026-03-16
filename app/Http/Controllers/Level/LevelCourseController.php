<?php

namespace App\Http\Controllers\Level;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class LevelCourseController extends ApiController
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
    public function index(Request $request,Level $level)
    {
        //
        // $level = Level::find($level->id);
        $level = Level::query()->where('id',$level->id)->first();
        $schoolYear = $request->schoolYear  != null ? $request->schoolYear : SchoolYear::where('active',true)->first()->id;
        $courses = $level->courses->where('school_year_id',$schoolYear);

        $collections = $request->input('all') == 1
        ? $this->showList($courses):
         $this->showAll($courses);
         return $collections;
    }

   
}
