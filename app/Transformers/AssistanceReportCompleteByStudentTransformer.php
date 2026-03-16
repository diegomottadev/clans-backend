<?php

namespace App\Transformers;

use App\Models\Assistance;
use App\Models\Student;
use League\Fractal\TransformerAbstract;

class AssistanceReportCompleteByStudentTransformer extends TransformerAbstract
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
    public function transform(Student $student)
    {
        return [
            'id'         => (int) $student->id,
            'name'       => (string) $student->nombre,
            'last_name'  => (string) $student->apellido,
            'assistance' => $student->assitences->map(function ($assistance) {
                return [
                    'id'            => (int) $assistance->id,
                    'date'          => (string) $assistance->fecha,
                    'course'        => (object) $assistance->course,
                    'typeAssistance'=> (object) $assistance->pivot,
                ];
            }),
        ];
    }

    public static function originalAttributes($index){
        $attributes = [
            //
            'id' => 'id',
            'date' => 'fecha',
            'created_at' =>'created_at',
            'updated_at' =>'updated_at',
            'deletd_at' => 'deleted_at'
        ];

        return isset($attributes[$index]) ? $attributes[$index]:null;
    }
}
