<?php

namespace App\Transformers;

use App\Models\ClassSchedule;
use League\Fractal\TransformerAbstract;

class ClassScheduleTransform extends TransformerAbstract
{
    protected $defaultIncludes = [];
    protected $availableIncludes = [];

    public function transform(ClassSchedule $classSchedule)
    {
        return [
            'id' => (int) $classSchedule->id,
            'course' => (object) $classSchedule->course,
            'teacher' => (object) $classSchedule->teacher,
            'classroom' => (object) $classSchedule->classroom,
            'date' => (string) $classSchedule->fecha,
            'startTime' => (string) $classSchedule->hora_inicio,
            'endTime' => (string) $classSchedule->hora_fin,
            'created_at' => isset($classSchedule->created_at) ? (string) $classSchedule->created_at : null,
            'updated_at' => isset($classSchedule->updated_at) ? (string) $classSchedule->updated_at : null,
            'deleted_at' => isset($classSchedule->deleted_at) ? (string) $classSchedule->deleted_at : null,
        ];
    }

    public static function originalAttributes($index)
    {
        $attributes = [
            'id' => 'id',
            'course' => 'id_curso',
            'teacher' => 'id_profesor',
            'classroom' => 'id_aula',
            'date' => 'fecha',
            'startTime' => 'hora_inicio',
            'endTime' => 'hora_fin',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'deleted_at' => 'deleted_at',
        ];

        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
