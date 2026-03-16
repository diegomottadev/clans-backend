<?php

namespace App\Transformers;

use App\Models\SchoolYear;
use League\Fractal\TransformerAbstract;

class SchoolYearTransform extends TransformerAbstract
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

    public function transform(SchoolYear $schoolYear)
    {
        return [
            //
            'id' => (int)$schoolYear->id,
            'year' => (integer) $schoolYear->year,
            'active' => (bool) $schoolYear->active,
            'created_at' => isset($schoolYear->created_at) ? (string) $schoolYear->created_at : null,
            'updated_at' => isset($schoolYear->updated_at) ? (string) $schoolYear->updated_at : null,
            'deleted_at' => isset($schoolYear->deleted_at) ? (string) $schoolYear->deleted_at : null
        ];
    }

    public static function originalAttributes($index){
        $attributes = [
            //
            'id' => 'id',
            'year' => 'year',
            'active' => 'active',
            'created_at' =>'created_at',
            'updated_at' =>'updated_at',
            'deletd_at' => 'deleted_at'
        ];

        return isset($attributes[$index]) ? $attributes[$index]:null;
    }
}
