<?php

namespace App\Transformers;

use App\Models\TypeCourse;
use League\Fractal\TransformerAbstract;

class TypeCourseTransform extends TransformerAbstract
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
    public function transform(TypeCourse $typeCourse)
    {
        return [
            //
            'id' => (int)$typeCourse->id,
            'name' => (string) $typeCourse->nombre,
            'created_at' =>  isset($typeCourse->created_at) ? (string) $typeCourse->created_at : null,
            'updated_at' => isset($typeCourse->updated_at) ? (string) $typeCourse->updated_at : null,
            'deleted_at' => isset($typeCourse->deleted_at) ? (string) $typeCourse->deleted_at : null
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
