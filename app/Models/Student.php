<?php

namespace App\Models;

use App\Transformers\StudentTransform;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Course;
use App\Models\Evaluation;
use App\Models\ConceptStudentPivot;
use App\Models\Assistance;
use App\Models\TypeAssistancePivot;
use App\Models\StudentCourse;


class Student extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $transformer = StudentTransform::class;

    protected $table = 'alumnos';

	protected $dates = ['created_at','update_at','deleted_at'];
    protected $appends = ['name_complete', 'apellido_nombre_completo'];
    protected $fillable =[
        'id',
        'apellido',
        'nombre',
        'fecha_nac',
        'dni',
        'domicilio',
        'telefono',
        'barrio',
        'observaciones',
        'id_tutor',
        'escuela',
        'turno_escolar',
        'horario_ed_fisica',
        'activo',
        'nombreCompleto',
        'actividad',
    ];

    public function setHorario_ed_fisicaAttribute($value)
    {
        $this->attributes['horario_ed_fisica'] = $value != null ? Carbon::parse($value)->timezone('America/Argentina/San_Luis')->format('H:i'): null;
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'alumnos_cursos','id_alumno','id_curso')->withPivot(['fecha', 'anio_lectivo']);
    }

    /** Inscripciones (pivot alumnos_cursos) para filtrar por curso y por anio_lectivo. */
    public function enrollments()
    {
        return $this->hasMany(StudentCourse::class, 'id_alumno');
    }

    public function getNameCompleteAttribute()
    {
        return "{$this->nombre} {$this->apellido}";
    }

    public function getApellidoNombreCompletoAttribute()
    {
        return "{$this->apellido}, {$this->nombre}";
    }

    public function evaluations()
    {
        return $this->belongsToMany(Evaluation::class, 'evaluaciones_alumnos','id_alumno','id_evaluacion','id')
        ->withPivot(['concept',
                    'observaciones',
                    'id_concepto',
                    ])->using(ConceptStudentPivot::class);;
    }

    public function assitences(){
        return $this->belongsToMany(Assistance::class,'asistencias','id_alumno','id_fechas_curso','id')->withPivot(['id_tipos_asistencia'])->using(TypeAssistancePivot::class);
    }


    public function latestPayment()
    {
        return $this->hasOne(Invoice::class,'id_alumno')->latestOfMany('fecha_emision');
    }
}
