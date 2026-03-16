<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Evaluation;
use App\Models\Student;

class EvaluationStudent extends Model
{
    use HasFactory;
    protected $table = 'evaluaciones_alumnos';

    protected $fillable = [
        'id_evaluacion',
        'id_alumno',
        'nota',
        'observaciones',
        'concept',
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
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class,'id_alumno');
    }

    public function student(){
        return $this->belongsTo(Student::class,'id_alumno');
    }

    /**
     * Mutator para convertir coma a punto antes de guardar en BD
     */
    public function setNotaAttribute($value)
    {
        if ($value !== null) {
            // Convertir coma a punto para almacenamiento en BD
            $this->attributes['nota'] = str_replace(',', '.', $value);
        }
    }

    /**
     * Accessor para formatear la nota al mostrar
     */
    public function getNotaAttribute($value)
    {
        if ($value !== null) {
            // Mantener el formato decimal con punto para consistencia
            return number_format($value, 2, '.', '');
        }
        return $value;
    }

    /**
     * Accessor para obtener la nota formateada con coma (formato local)
     */
    public function getNotaFormateadaAttribute()
    {
        if ($this->attributes['nota'] !== null) {
            // Formatear con coma como separador decimal (formato local)
            return number_format($this->attributes['nota'], 2, ',', '');
        }
        return null;
    }

}
