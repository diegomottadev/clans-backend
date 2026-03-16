<?php

namespace App\Transformers;

use App\Models\Evaluation;
use App\Models\Languaje;
use League\Fractal\TransformerAbstract;

class LanguajeTransform extends TransformerAbstract
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
    public function transform(Languaje $languaje)
    {
        return [
            //
            'id' => (int)$languaje->id,
            'name' => (string) $languaje->nombre,
            'created_at' => isset($languaje->created_at) ? (string) $languaje->created_at : null,
            'updated_at' => isset($languaje->updated_at) ? (string) $languaje->updated_at : null,
            'deleted_at' => isset($languaje->deleted_at) ? (string) $languaje->deleted_at : null
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
