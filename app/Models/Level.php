<?php

namespace App\Models;

use App\Transformers\LevelTransform;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Languaje;
use App\Models\TypeCourse;

class Level extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $transformer = LevelTransform::class;

    protected $table = 'niveles';
	protected $dates = ['created_at','update_at','deleted_at'];
    protected $fillable = [
        'id',
    	'nombre',
        'id_idioma',
        'cuota',
        'id_tipo_cursado',
        'estado',
        'mes_desde',
        'mes_hasta'
    ];


    public function languaje(){
        return $this->belongsTo(Languaje::class,'id_idioma');
    }

    public function typeCourse(){
        return $this->belongsTo(TypeCourse::class,'id_tipo_cursado');

    }


    public function courses(){
        return $this->hasMany(Course::class, 'id_nivel');

    }
}
