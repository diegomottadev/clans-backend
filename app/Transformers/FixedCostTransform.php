<?php

namespace App\Transformers;

use App\Models\FixedCost;
use App\Models\Languaje;
use App\Models\Level;
use League\Fractal\TransformerAbstract;

class FixedCostTransform extends TransformerAbstract
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

    public function transform(FixedCost $fixedCost)
    {
        return [
            //
            'id' => (int)$fixedCost->id,
            'date' => (string) $fixedCost->fecha,
            'amount' => (string) $fixedCost->monto,
            'typeExpense' => (object) $fixedCost->typeExpense,
            'created_at' => isset($fixedCost->created_at) ? (string) $fixedCost->created_at : null,
            'updated_at' => isset($fixedCost->updated_at) ? (string) $fixedCost->updated_at : null,
            'deleted_at' => isset($fixedCost->deleted_at) ? (string) $fixedCost->deleted_at : null
        ];
    }

    public static function originalAttributes($index){
        $attributes = [
            //
            'id' => 'id',
            'date' => 'fecha',
            'amount' => 'monto',
            'typeExpense' => 'typeExpense',
            'created_at' =>'created_at',
            'updated_at' =>'updated_at',
            'deletd_at' => 'deleted_at'
        ];

        return isset($attributes[$index]) ? $attributes[$index]:null;
    }
}
