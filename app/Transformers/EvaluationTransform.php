<?php

namespace App\Transformers;

use App\Models\Evaluation;
use League\Fractal\TransformerAbstract;

class EvaluationTransform extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        //
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Evaluation $evaluation)
    {

        $transformation = fractal($evaluation->course->students,new StudentTransform);
        $students =  $transformation->toArray();
        return [
            //
            'id' => (int)$evaluation->id,
            'name' => (string) $evaluation->nombre,
            'date'=> isset($evaluation->fecha) ? (string) $evaluation->fecha : null,
            'course'=> (object)$evaluation->course,
            'id_course'=> (int)$evaluation->id_curso,
            'typeEvaluation' =>(object)$evaluation->typeEvaluation,
            'studentsEvaluated' =>(object)$evaluation->students,
            'students' =>(object)$students,
            'conceptStudent' => (object) $evaluation->pivot,

            'updated_at' => (string)$evaluation->updated_at,
            'deletd_at' => isset($evaluation->deleted_at) ? (string) $evaluation->deleted_at : null
        ];
    }

    public static function originalAttributes($index){
        $attributes = [
            //
            'id' => 'id',
            'name' => 'nombre',
            'date'=> 'fecha',
            'course'=> 'course',
            'typeEvaluation' => 'typeEvaluation',
            'created_at' =>'created_at',
            'updated_at' =>'updated_at',
            'deletd_at' => 'deleted_at'
        ];

        return isset($attributes[$index]) ? $attributes[$index]:null;
    }
}
