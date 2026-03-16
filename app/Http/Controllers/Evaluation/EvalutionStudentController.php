<?php

namespace App\Http\Controllers\Evaluation;

use App\Http\Controllers\ApiController;
use App\Models\Evaluation;
use App\Models\EvaluationStudent;
use App\Models\Student;
use Illuminate\Http\Request;

class EvalutionStudentController extends ApiController
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
    public function storej(Request $request,Evaluation $evaluation)
    {
        //

        $rules = [
            'student' =>'required',
            'concept'  =>'required',

        ];

        $this->validate($request,$rules);
        $evaluation->students()->attach([$request->student =>['observaciones'=> $request->observation,
                                                            'id_concepto' => $request->concept,
                                                            'concept'=> $request->puntuation]]);
        $evaluation->save();
        return $this->showOne($evaluation);
    }

    public function store(Request $request,Evaluation $evaluation)
    {
        $scoresArray = [];
        $scores = ($request->studentWithScore);
        foreach($scores as $studentNotes){
            $student = ($studentNotes['stdId']);
            $score = $studentNotes['attrs'];
            
            // Normalizar las notas: convertir coma a punto
            $normalizedScore = $this->normalizeScores($score);
            
            $values = ([['observaciones'=> $normalizedScore["observaciones"],"assigned" => $normalizedScore["assigned"],"pending" => $normalizedScore["pending"],"delivered" => $normalizedScore["delivered"],"listening" => $normalizedScore["listening"],"vocabulary" => $normalizedScore["vocabulary"],"languajeFocus" => $normalizedScore["languajeFocus"],"reading" => $normalizedScore["reading"], "communication" => $normalizedScore["communication"], "writing" => $normalizedScore["writing"],"oralExam" => $normalizedScore["oralExam"],"assigned" => $normalizedScore["assigned"],"delivered" => $normalizedScore["delivered"],"pending" => $normalizedScore["pending"]]]);
            $scoresArray[$student]=$values[0];
        }
        $evaluation->students()->detach();
        $evaluation->students()->attach($scoresArray);
        $evaluation->save();
        return $this->showOne($evaluation);
    }
    
    /**
     * Normalizar las notas: convertir coma a punto para almacenamiento
     */
    private function normalizeScores($score)
    {
        $normalized = $score;
        
        // Campos que pueden contener notas decimales
        $scoreFields = ['listening', 'vocabulary', 'languajeFocus', 'reading', 'communication', 'writing', 'oralExam'];
        
        foreach ($scoreFields as $field) {
            if (isset($normalized[$field]) && $normalized[$field] !== null) {
                // Convertir coma a punto
                $normalized[$field] = str_replace(',', '.', $normalized[$field]);
            }
        }
        
        return $normalized;
    }





    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Evaluation  $evaluation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Evaluation $evaluation,Student $student)
    {
        //

        $evaluation->students()->detach($student);
        $evaluation->save();
        return $this->showOne($evaluation);
    }

}
