<?php

namespace App\Transformers;

use App\Models\Teacher;
use App\Models\TypeEvaluation;
use League\Fractal\TransformerAbstract;

class TeacherTransform extends TransformerAbstract
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

    public function transform(Teacher $teacher)
    {
        return [
            //
            'id' => (int)$teacher->id,
            'firstName' => (string) $teacher->nombre,
            'lastName' => (string) $teacher->apellido,
            'completeName' => (string) $teacher->nombreCompleto,
            'apellido_nombre_completo' => (string) $teacher->apellido_nombre_completo,
            'dni' => (string) $teacher->dni,
            'telphone' => (string) $teacher->tel_part,
            'celphone' => (string) $teacher->cel_part,
            'email' => (string) $teacher->email,
            'address' => (string) $teacher->domicilio,
            'created_at' =>  isset($teacher->created_at) ? (string) $teacher->created_at : null,
            'updated_at' => isset($teacher->updated_at) ? (string) $teacher->updated_at : null,
            'deleted_at' => isset($teacher->deleted_at) ? (string) $teacher->deleted_at : null
        ];
    }

    public static function originalAttributes($index){
        $attributes = [
            //
            'id' => 'id',
            'firsName' => 'nombre',
            'lastName' => 'apellido',
            'completeName' => 'nombreCompleto',
            'dni' => 'dni',
            'telphone' => 'tel_part',
            'celphone' => 'cel_part',
            'email' => 'email',
            'address' => 'domicilio',
            'created_at' =>'created_at',
            'updated_at' =>'updated_at',
            'deletd_at' => 'deleted_at'
        ];

        return isset($attributes[$index]) ? $attributes[$index]:null;
    }
}
