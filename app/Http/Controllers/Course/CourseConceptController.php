<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\ApiController;
use App\Models\Concept;
use App\Models\Course;
use App\Models\Level;
use Illuminate\Http\Request;

class CourseConceptController extends ApiController
{
    /**
     *
     * @return void
     */
    public function __construct()
    {
       /*  $this->middleware(
            'auth:api'
        ); */
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Course $course)
    {
        // dd($course->id_nivsel);
        $level = Level::where('id',$course->id_nivel)->first();
        $concepts = Concept::query()->where('id_idioma',$level->id_idioma)->orderBy('id','DESC');
        return $this->showList($concepts->get());

    }

}
