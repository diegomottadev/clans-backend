<?php

namespace App\Models;

use App\Transformers\TypeExpenseTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeExpense extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $transformer = TypeExpenseTransformer::class;

    protected $table = 'tipos_egresos';
	protected $dates = ['created_at','update_at','deleted_at'];

    protected $fillable = [
    	'id',
    	'nombre',
    ];
}
