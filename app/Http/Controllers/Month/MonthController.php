<?php

namespace App\Http\Controllers\Month;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Month;
use Illuminate\Http\Request;

class MonthController extends ApiController
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
        $months = collect(Month::MONTHS);
        return $this->showAll($months);
    }

}
