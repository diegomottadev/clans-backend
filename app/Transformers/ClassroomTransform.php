<?php

namespace App\Transformers;

use App\Models\Classroom;
use League\Fractal\TransformerAbstract;

class ClassroomTransform extends TransformerAbstract
{
    protected $defaultIncludes = [];
    protected $availableIncludes = [];

    public function transform(Classroom $classroom)
    {
        return [
            'id' => (int) $classroom->id,
            'name' => (string) $classroom->nombre,
            'capacity' => $classroom->capacidad !== null ? (int) $classroom->capacidad : null,
            'created_at' => isset($classroom->created_at) ? (string) $classroom->created_at : null,
            'updated_at' => isset($classroom->updated_at) ? (string) $classroom->updated_at : null,
            'deleted_at' => isset($classroom->deleted_at) ? (string) $classroom->deleted_at : null,
        ];
    }

    public static function originalAttributes($index)
    {
        $attributes = [
            'id' => 'id',
            'name' => 'nombre',
            'capacity' => 'capacidad',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'deleted_at' => 'deleted_at',
        ];

        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
