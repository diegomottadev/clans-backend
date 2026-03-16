<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Models\TypeAssistance;

class TypeAssistancePivot extends Pivot
{

    protected $table = 'assistencias';
    protected $appends = ['nameTypeAssistance'];

    protected $fillable = [
    	'id_fechas_curso',
    	'id_alumno',
        'id_tipos_asistencia',
    ];

    public function typeAssistance(){
        return  $this->belongsTo(TypeAssistance::class,'id_tipos_asistencia');
    }
    public function getNameTypeAssistanceAttribute(){
        return $this->typeAssistance;
    }
}
