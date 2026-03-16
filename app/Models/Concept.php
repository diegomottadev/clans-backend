<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Transformers\ConceptTransform;
class   Concept extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $transformer = ConceptTransform::class;


    protected $table = 'conceptos';
	protected $dates = ['created_at','update_at','deleted_at'];

    protected $fillable = [
        'id',
        'nombre',
        'id_idioma'
    ];

}
