<?php

namespace App\Transformers;

use App\Models\SchoolYear;
use App\Models\Student;
use Illuminate\Support\Facades\Date;
use League\Fractal\TransformerAbstract;

class StudentEvaluationTransform extends TransformerAbstract
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
    public function transform(Student $student)
    {
        $data = [];
        foreach ($student->evaluations as $evaluation) {
            $data[] = $evaluation->pivot;
        }
        return [
            'fecha'       => (object) $data,
            'evaluations' => (object) $data,
        ];
    }

}
