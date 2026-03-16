<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Models\Concept;
class ConceptStudentPivot extends Pivot
{

    protected $appends = ['nameStudentConcept'];

    protected $fillable = [
        "id_alumno",
        "id_evaluacion",
        "concept",
        "observaciones",
        "id_concepto"
    ];

    public function conceptoSt(){
        return  $this->belongsTo(Concept::class,'id_concepto');
    }
    public function getNameStudentConceptAttribute(){

        return $this->conceptoSt()->first();
    }

}
