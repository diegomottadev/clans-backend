<?php

namespace App\Models;

use App\Transformers\TypeAssitanceTransform;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeAssistance extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $transformer = TypeAssitanceTransform::class;

    protected $table = 'tipos_asistencias';
	protected $dates = ['created_at','update_at','deleted_at'];

    protected $fillable = [
    	'id',
    	'nombre',
    ];


}
