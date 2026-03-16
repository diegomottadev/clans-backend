<?php

namespace App\Http\Controllers\Assistance;

use App\Http\Controllers\ApiController;
use App\Models\Assistance;
use App\Models\Student;
use Illuminate\Http\Request;

class AssistanceStudentController extends ApiController
{
    //

     /**
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(
            'auth:api'
        );
    }

    public function store(Request $request , Assistance $assistance){

        $assistance->studentsAssisted()->detach();
        $data = [];
        foreach ($request->assistancesStudents as $assistanceStudent) {
            $data[$assistanceStudent['studentId']] = ['id_tipos_asistencia' => $assistanceStudent['typeAssistanceId']];
        }
        $assistance->studentsAssisted()->attach($data);
        $assistance->save();
        return $this->showOne($assistance);
    }

    public function update(Request $request , Assistance $assistance, Student $student){

        $assistance->studentsAssisted()->detach($student);
        $assistance->save();
        return $this->showOne($assistance);

    }

}
