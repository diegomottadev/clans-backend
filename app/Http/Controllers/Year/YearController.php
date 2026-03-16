<?php

namespace App\Http\Controllers\Year;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Year;
use Illuminate\Http\Request;

class YearController extends ApiController
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
    //
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        $years = collect(Year::YEARS);
        return $this->showAll($years);
    }
}
