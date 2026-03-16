<?php

namespace App\Transformers;

use App\Models\Assistance;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class AssistanceTransformer extends TransformerAbstract
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
    public function transform(Assistance $assistance)
    {
        return [
            //
            'id' => (int)$assistance->id,
            'date' => (string) $assistance->fecha,
            'course' => (object)$assistance->course,
            'studentsAssisted' => (object) $assistance->studentsAssisted,
            'created_at' => (string)$assistance->updated_at,
            'updated_at' => (string)$assistance->updated_at,
            'deleted_at' => isset($assistance->deleted_at) ? (string) $assistance->deleted_at : null
        ];
    }

    public static function originalAttributes($index){
        $attributes = [
            //
            'id' => 'id',
            'date' => 'fecha',
            'created_at' =>'created_at',
            'updated_at' =>'updated_at',
            'deletd_at' => 'deleted_at',
            'courseAssistence'=>'courseAssistence',
            'dateAssistence' => 'dateAssistence'
        ];

        return isset($attributes[$index]) ? $attributes[$index]:null;
    }
}
