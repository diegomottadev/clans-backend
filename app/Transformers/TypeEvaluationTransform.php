<?php

namespace App\Transformers;

use App\Models\TypeEvaluation;
use League\Fractal\TransformerAbstract;

class TypeEvaluationTransform extends TransformerAbstract
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
    public function transform(TypeEvaluation $typeEvaluation)
    {
        return [
            //
            'id' => (int)$typeEvaluation->id,
            'name' => (string) $typeEvaluation->nombre,
            'created_at' =>  isset($typeEvaluation->created_at) ? (string) $typeEvaluation->created_at : null,
            'updated_at' => isset($typeEvaluation->updated_at) ? (string) $typeEvaluation->updated_at : null,
            'deleted_at' => isset($typeEvaluation->deleted_at) ? (string) $typeEvaluation->deleted_at : null
        ];
    }

    public static function originalAttributes($index){
        $attributes = [
            //
            'id' => 'id',
            'name' => 'nombre',
            'created_at' =>'created_at',
            'updated_at' =>'updated_at',
            'deletd_at' => 'deleted_at'
        ];

        return isset($attributes[$index]) ? $attributes[$index]:null;
    }
}
