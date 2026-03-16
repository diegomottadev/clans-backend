<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Transformers\CourseTransform;
use App\Models\Teacher;
use App\Models\Level;
use App\Models\Student;
use App\Models\SchoolYear;

class Course extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $transformer = CourseTransform::class;

    protected $with = ['level', 'schoolYear'];
    protected $table = 'cursos';
	protected $dates = ['created_at','update_at','deleted_at'];

    protected $appends = ['nameId', 'nameYear'];

    protected $fillable = [
        'id',
        'nombre',
        'id_profesor',
        'id_nivel',
        'school_year_id',
        'status',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'id_profesor');
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'id_nivel');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'alumnos_cursos','id_curso','id_alumno')->withPivot(['fecha']);
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id');
    }

    /**
     * Nombre del curso con id (ej: "5 - Inglés 1")
     */
    public function getNameIdAttribute(): string
    {
        return (string) $this->id . ' - ' . $this->nombre;
    }

    /**
     * Nombre del curso con año lectivo (ej: "Inglés 1-2024")
     */
    public function getNameYearAttribute(): string
    {
        $year = $this->relationLoaded('schoolYear') && $this->schoolYear
            ? (int) $this->schoolYear->year
            : null;
        return (string) $this->nombre . '-' . $year;
    }

    public function toArray()
    {
        $attributes = $this->attributesToArray();
        $attributes = array_merge($attributes, $this->relationsToArray());
        unset($attributes['pivot']['id_curso'],$attributes['pivot']['id_alumno']);
        return $attributes;
    }
}
