<?php

namespace App\Transformers;

use App\Models\FixedCost;
use App\Models\Languaje;
use App\Models\Level;
use App\Models\PaymentTeacher;
use League\Fractal\TransformerAbstract;

class PaymentTeacherTransform extends TransformerAbstract
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

    public function transform(PaymentTeacher $paymentTeacher)
    {
        return [
            //
            'id' => (int)$paymentTeacher->id,
            'date' => (string) $paymentTeacher->fecha,
            'amount' => (string) $paymentTeacher->monto,
            'teacher_id' => (int)$paymentTeacher->id_profesor,
            'teacher' => (object) $this->transformData($paymentTeacher->teacher, TeacherTransform::class),
            'created_at' => isset($paymentTeacher->created_at) ? (string) $paymentTeacher->created_at : null,
            'updated_at' => isset($paymentTeacher->updated_at) ? (string) $paymentTeacher->updated_at : null,
            'deleted_at' => isset($paymentTeacher->deleted_at) ? (string) $paymentTeacher->deleted_at : null
        ];
    }

    public static function originalAttributes($index){
        $attributes = [
            //
            'id' => 'id',
            'date' => 'fecha',
            'amount' => 'monto',
            'teacher' => 'teacher',
            'created_at' =>'created_at',
            'updated_at' =>'updated_at',
            'deletd_at' => 'deleted_at'
        ];

        return isset($attributes[$index]) ? $attributes[$index]:null;
    }

    protected function transformData($data,$tranformer){
        $transformation = fractal($data,new $tranformer);
        return $transformation->toArray();
    }
}
