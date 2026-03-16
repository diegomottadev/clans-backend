<?php

namespace App\Http\Controllers\Languaje;

use App\Http\Controllers\ApiController;
use App\Models\Course;
use App\Models\Languaje;
use App\Models\Level;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class LanguajeLevelController extends ApiController
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


    public function index(Request $request,Languaje $languaje)
    {
        
        $levels = Level::where('id_idioma',$languaje->id)->get();
        $collections = $request->input('all', '') == 1
        ? $this->showList($levels) :
        $this->showAll($levels);

        return $collections;

    }

}
