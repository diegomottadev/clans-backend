<?php

namespace App\Models;

use App\Transformers\TypeEvaluationTransform;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeEvaluation extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $transformer = TypeEvaluationTransform::class;
    protected $table = 'tipo_evaluacion';
	protected $dates = ['created_at','update_at','deleted_at'];

    protected $fillable = [
    	'id',
    	'nombre',
    ];
}
