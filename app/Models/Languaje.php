<?php

namespace App\Models;

use App\Transformers\LanguajeTransform;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Level;
class Languaje extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $transformer = LanguajeTransform::class;
    protected $table = 'idiomas';
	protected $dates = ['created_at','update_at','deleted_at'];

    protected $fillable = [
    	'id',
    	'nombre',
    ];


    public function level(){
        return $this->hasOne(Level::class);
    }


    public function levels(){
        return $this->hasMany(Level::class, 'id_idioma');

    }
}
