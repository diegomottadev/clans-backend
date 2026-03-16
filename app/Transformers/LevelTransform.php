<?php

namespace App\Transformers;

use App\Models\Languaje;
use App\Models\Level;
use League\Fractal\TransformerAbstract;

class LevelTransform extends TransformerAbstract
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

    public function transform(Level $level)
    {
        return [
            //
            'id' => (int)$level->id,
            'name' => (string) $level->nombre,
            'fee' => (string) $level->cuota,
            'monthInit' => (integer) $level->mes_desde,
            'monthFinish' => (integer) $level->mes_hasta,
            'typeCourse' => (object) $level->typeCourse,
            'languaje' => (object) $level->languaje,
            'created_at' => isset($level->created_at) ? (string) $level->created_at : null,
            'updated_at' => isset($level->updated_at) ? (string) $level->updated_at : null,
            'deleted_at' => isset($level->deleted_at) ? (string) $level->deleted_at : null
        ];
    }

    public static function originalAttributes($index){
        $attributes = [
            //
            'id' => 'id',
            'name' => 'nombre',
            'typeCourse' => 'typeCourse',
            'languaje' => 'languaje',
            'monthInit' => 'mes_desde',
            'monthFinish' => 'mes_hasta',
            'created_at' =>'created_at',
            'updated_at' =>'updated_at',
            'deletd_at' => 'deleted_at'
        ];

        return isset($attributes[$index]) ? $attributes[$index]:null;
    }
}
