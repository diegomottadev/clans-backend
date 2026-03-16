<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Transformers\EvaluationTransform;
use App\Models\Course;
use App\Models\TypeEvaluation;
use App\Models\Student;

class Evaluation extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $transformer = EvaluationTransform::class;
    protected $table = 'evaluaciones';
	protected $dates = ['created_at','update_at','deleted_at'];
    protected $with = ['students'];
    protected $fillable = [
        'id',
        'nombre',
        'fecha',
        'id_tipo_evaluacion',
        'id_curso',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'id_curso');
    }

    public function typeEvaluation()
    {
        return $this->belongsTo(TypeEvaluation::class, 'id_tipo_evaluacion');
    }


    public function students()
    {
        return $this->belongsToMany(Student::class, 'evaluaciones_alumnos','id_evaluacion','id_alumno','id')
                    ->withPivot([
                                'concept',
                                'observaciones',
                                'id_concepto',
                                "listening",
                                "vocabulary",
                                "languajeFocus",
                                "reading",
                                "communication",
                                "writing",
                                "oralExam",
                                "pending",
                                "delivered",
                                "assigned",
                                ]);
    }


}
