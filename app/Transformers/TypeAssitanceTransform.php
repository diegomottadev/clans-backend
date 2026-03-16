<?php

namespace App\Transformers;

use App\Models\TypeAssistance;
use League\Fractal\TransformerAbstract;

class TypeAssitanceTransform extends TransformerAbstract
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
    public function transform(TypeAssistance $typeAssitance)
    {
        return [
            //
            'id' => (int)$typeAssitance->id,
            'name' => (string) $typeAssitance->nombre,
            'created_at' =>  isset($typeAssitance->created_at) ? (string) $typeAssitance->created_at : null,
            'updated_at' => isset($typeAssitance->updated_at) ? (string) $typeAssitance->updated_at : null,
            'deleted_at' => isset($typeAssitance->deleted_at) ? (string) $typeAssitance->deleted_at : null
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
