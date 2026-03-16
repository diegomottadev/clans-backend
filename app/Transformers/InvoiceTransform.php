<?php

namespace App\Transformers;

use App\Models\Invoice;
use League\Fractal\TransformerAbstract;

class InvoiceTransform extends TransformerAbstract
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
    public function transform(Invoice $invoice)
    {

        return [
            //
            'id' => (string) $invoice->id,
            'dateIssue' => $invoice->fecha_emision ? \Carbon\Carbon::parse($invoice->fecha_emision)->format('d/m/Y') : null,
            'month' => (string) $invoice->mes,
            'dateExpiration' => (string) $invoice->fecha_vto,
            'discountPaymentCompletion' => (string) $invoice->dto_pago_termino,
            'discountBrother' => (string) $invoice->dto_hermano,
            'fee' => (string) $invoice->cuota,
            'mora' => (string) $invoice->mora,
            'total' => (string) $invoice->total,
            'status' => (string) $invoice->estado,
            'created_at' => isset($invoice->created_at) ? (string) $invoice->created_at : null,
            'updated_at' => isset($invoice->updated_at) ? (string) $invoice->updated_at : null,
            'deleted_at' => isset($invoice->deleted_at) ? (string) $invoice->deleted_at : null,
            'student' => (object) $invoice->student,
            'studentCourse' => (object) $invoice->studentCourse,
        ];
    }

    public static function originalAttributes($index){
        $attributes = [
            //
            'id' => 'id',
            'student' => 'student',
            'studentCourse' => 'studentCourse',
            'dateIssue' => 'fecha_emision',
            'month' => 'mes',
            'dateExpiration' => 'fecha_vto',
            'discountPaymentCompletion' => 'dto_pago_termino',
            'discountBrother' => 'dto_hermano',
            'mora' => 'mora',
            'total' => 'total',
            'status' => 'estado',
            'created_at' =>'created_at',
            'updated_at' => 'updated_at',
            'deleted_at' => 'deleted_at',
        ];

        return isset($attributes[$index]) ? $attributes[$index]:null;
    }
}
