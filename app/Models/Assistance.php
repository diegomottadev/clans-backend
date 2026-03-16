<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Course;
use App\Models\Student;
use App\Transformers\AssistanceTransformer;
use Carbon\Carbon;

class Assistance extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $transformer = AssistanceTransformer::class;
    protected $table = 'fechas_curso';
	protected $dates = ['created_at','update_at','deleted_at'];

    protected $fillable = [
        'id',
        'fecha',
        'id_curso'
    ];

    public function getFechaAttribute($value)
    {
        return Carbon::parse($value)->format('d/m/Y');
    }

    public function course(){

        return $this->belongsTo(Course::class,'id_curso');

    }

    public function studentsAssisted(){
        return $this->belongsToMany(Student::class, 'asistencias','id_fechas_curso','id_alumno')->withPivot(['id_tipos_asistencia'])->using(TypeAssistancePivot::class)->orderBy('apellido', 'asc')->orderBy('nombre', 'asc');
    }


    public function assitencesByIdStudent(){
        // = in where is optional in this case
        return $this->belongsToMany(Student::class,'asistencias','id_fechas_curso','id_alumno','id');
    }
}
