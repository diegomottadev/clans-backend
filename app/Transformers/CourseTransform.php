<?php

namespace App\Transformers;

use App\Models\Course;
use League\Fractal\TransformerAbstract;

class CourseTransform extends TransformerAbstract
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
    public function transform(Course $course)
    {

        $transformation = fractal($course->students,new StudentTransform);
        $students =  $transformation->toArray();
        $year = isset($course->schoolYear) ? (integer)$course->schoolYear->year : null;
        return [
            //
            'id' => (int)$course->id,
            'nameId' => (string) $course->id . " - " . $course->nombre,
            'name' => (string) $course->nombre,
            'nameYear' => (string) $course->nombre . "-".$year,
            'level' => (object) $course->level,
            'teacher' => (object) $course->teacher,
            'students' => (object) $students,
            'year'=> isset($course->schoolYear) ? (integer)$course->schoolYear->year : null,
            'status' => (bool) $course->status,
            'created_at' => (string)$course->created_at,
            'updated_at' => (string)$course->updated_at,
            'deletd_at' => isset($course->deleted_at) ? (string) $course->deleted_at : null
        ];
    }

    public static function originalAttributes($index){
        $attributes = [
            //
            'id' => 'id',
            'name' => 'nombre',
            'level'=> 'level',
            'year' => 'school_year_id',
            'status'=> 'status',
            'teacher'=> 'teacher',
            'students'=> 'students',
            'created_at' =>'created_at',
            'updated_at' =>'updated_at',
            'deletd_at' => 'deleted_at'
        ];

        return isset($attributes[$index]) ? $attributes[$index]:null;
    }
}
