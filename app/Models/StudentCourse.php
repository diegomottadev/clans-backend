<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Course;
use App\Models\Student;

class StudentCourse extends Model
{
    use HasFactory;
    protected $table = 'alumnos_cursos';

    protected $fillable = [
        'id',
        'anio_lectivo',
        'dto_hermano',
        'estado',
        'fecha',
        'id_alumno',
        'id_curso',
        'id_nivel',
        'id_idioma',
        'importe',
        'pagado',
    ];


    public function course()
    {
        return $this->belongsTo(Course::class,'id_curso');
    }

    public function student(){
        return $this->belongsTo(Student::class,'id_alumno');
    }

}
