<?php

namespace App\Transformers;

use App\Models\SchoolYear;
use App\Models\Student;
use Illuminate\Support\Facades\Date;
use League\Fractal\TransformerAbstract;

class StudentTransform extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        //
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Student $student)
    {
        static $schoolYear = null;
        if ($schoolYear === null) {
            $schoolYear = cache()->remember('active_school_year', 3600, function () {
                return SchoolYear::where('active', true)->first();
            });
        }

        $studentsArray = [];

        $students = $student->courses->where('school_year_id', $schoolYear->id);


        foreach ($students as $student1){

            $studentsArray[]=$student1;
        }

        return [
            //
            'id' => (int)$student->id,
            'firstName' => (string) $student->nombre,
            'lastName'=> (string) $student->apellido,
            'name_complete' => (string) $student->nombreCompleto,
            'apellido_nombre_completo' => (string) $student->apellido_nombre_completo,
            'dateOfBirth'=> (string)$student->fecha_nac,
            'dni'=> (string)$student->dni,
            'address'=> (string)$student->domicilio,
            'observations' =>(string)$student->observaciones,
            'telphone'=>(string) $student->telefono,
            'neighborhood'=> (string)$student->barrio,
            'school'=> (string)$student->escuela,
            'schoolShift'=>(string) $student->turno_escolar,
            'physicalEducationSchedule'=> (string)$student->horario_ed_fisica,
            'activity'=> (string)$student->actividad,
            'status'=>  (bool)$student->activo,
            'courses' => (array)$studentsArray,
            'created_at' =>(string)$student->created_at,
            'updated_at' => (string)$student->updated_at,
            'deletd_at' => isset($student->deleted_at) ? (string) $student->deleted_at : null
        ];
    }

    public static function originalAttributes($index){
        $attributes = [
            //
            'id' => 'id',
            'firstName' => 'nombre',
            'lastName'=> 'apellido',
            'completeName'=> 'nombreCompleto',
            'DateOfBirth'=> 'fecha_nac',
            'dni'=> 'dni',
            'address'=> 'domicilio',
            'observations' =>'observaciones',
            'telphone'=>'telefono',
            'neighborhood'=> 'barrio',
            'school'=> 'escuela',
            'schoolShift'=>'turno_escolar',
            'physicalEducationSchedule'=> 'horario_ed_fisica',
            'activity'=> 'actividad',
            'status'=>  'activo',
            'courses'=> 'courses',
            'created_at' =>'created_at',
            'updated_at' =>'updated_at',
            'deletd_at' => 'deleted_at'
        ];

        return isset($attributes[$index]) ? $attributes[$index]:null;
    }
}
