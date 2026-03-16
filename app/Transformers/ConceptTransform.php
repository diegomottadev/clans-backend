<?php

namespace App\Transformers;

use App\Models\Concept;
use App\Models\Course;
use League\Fractal\TransformerAbstract;

class ConceptTransform extends TransformerAbstract
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
    public function transform(Concept $concept)
    {
        return [
            //
            'id' => (int)$concept->id,
            'name' => (string) $concept->nombre,
            'created_at' => (string)$concept->created_at,
            'updated_at' => (string)$concept->updated_at,
            'deletd_at' => isset($concept->deleted_at) ? (string) $concept->deleted_at : null
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
