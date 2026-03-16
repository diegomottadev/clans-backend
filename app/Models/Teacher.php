<?php

namespace App\Models;

use App\Transformers\TeacherTransform;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Course;

class Teacher extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'profesores';
    public $transformer = TeacherTransform::class;

    protected $fillable =[
        'id',
        'apellido',
        'nombre',
        'dni',
        'tel_part',
        'cel_part',
        'email',
        'domicilio',
        'nombreCompleto'
    ];

    protected $appends = ['apellido_nombre_completo'];

    public function getApellidoNombreCompletoAttribute()
    {
        return "{$this->apellido}, {$this->nombre}";
    }

    public function course(){
        return $this->hasOne(Course::class, 'id_profesor');
    }
}
