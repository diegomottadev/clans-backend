<?php

namespace App\Models;

use App\Transformers\ClassroomTransform;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classroom extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'aulas';
    public $transformer = ClassroomTransform::class;

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        'id',
        'nombre',
        'capacidad',
    ];

    public function classes()
    {
        return $this->hasMany(ClassSchedule::class, 'id_aula');
    }
}
