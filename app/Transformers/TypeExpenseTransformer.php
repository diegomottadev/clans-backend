<?php

namespace App\Transformers;

use App\Models\TypeEvaluation;
use App\Models\TypeExpense;
use League\Fractal\TransformerAbstract;

class TypeExpenseTransformer extends TransformerAbstract
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
    public function transform(TypeExpense $typeExpense)
    {
        return [
            //
            'id' => (int)$typeExpense->id,
            'name' => (string) $typeExpense->nombre,
            'created_at' =>  isset($typeExpense->created_at) ? (string) $typeExpense->created_at : null,
            'updated_at' => isset($typeExpense->updated_at) ? (string) $typeExpense->updated_at : null,
            'deleted_at' => isset($typeExpense->deleted_at) ? (string) $typeExpense->deleted_at : null
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
