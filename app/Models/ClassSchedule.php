<?php

namespace App\Models;

use App\Transformers\ClassScheduleTransform;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassSchedule extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'clases';
    public $transformer = ClassScheduleTransform::class;

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        'id',
        'id_curso',
        'id_profesor',
        'id_aula',
        'fecha',
        'hora_inicio',
        'hora_fin',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'id_curso');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'id_profesor');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'id_aula');
    }
}
