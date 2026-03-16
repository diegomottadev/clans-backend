<?php

namespace App\Http\Controllers\Concept;

use App\Http\Controllers\ApiController;
use App\Models\Concept;
use Illuminate\Http\Request;

class ConceptController extends ApiController
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
    public function index(Request $request)
    {
        //
        $concepts = Concept::query()->orderBy('id','DESC');
        $collections = $request->input('all','') == 1
        ? $this->showList($concepts->get()) :
         $this->showAll($concepts);
         return $collections;
    }
}
