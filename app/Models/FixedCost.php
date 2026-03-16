<?php

namespace App\Models;

use App\Transformers\FixedCostTransform;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\TypeExpense;


class FixedCost extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $transformer = FixedCostTransform::class;
    protected $table = 'pagos_cuentas';
	protected $dates = ['created_at','update_at','deleted_at'];

    protected $fillable = [
        'id',
        'fecha',
        'id_tipo_egreso',
        'monto',
    ];

    public function typeExpense()
    {
        return $this->belongsTo(TypeExpense::class, 'id_tipo_egreso');
    }

}
