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
        if (!$this->canAccessCourse($assistance->id_curso)) {
            return $this->forbiddenCourse();
        }

        $this->validate($request, [
            'assistancesStudents' => 'required_without:students|array',
            'students' => 'required_without:assistancesStudents|array',
        ]);

        $assistance->studentsAssisted()->detach();
        $assistance->studentsAssisted()->attach($this->attendanceData($request));
        $assistance->save();
        return $this->showOne($assistance);
    }

    /** PUT /assistances/{assistance}/students (PWA docente): reemplaza la asistencia completa. */
    public function replace(Request $request, Assistance $assistance)
    {
        return $this->store($request, $assistance);
    }

    /**
     * Acepta el formato web  { assistancesStudents: [{ studentId, typeAssistanceId }] }
     * y el de la PWA          { students: [{ id_alumno, id_tipos_asistencia }] }.
     */
    private function attendanceData(Request $request): array
    {
        $data = [];
        foreach ((array) $request->input('assistancesStudents', []) as $row) {
            $data[(int) $row['studentId']] = ['id_tipos_asistencia' => $row['typeAssistanceId']];
        }
        foreach ((array) $request->input('students', []) as $row) {
            $data[(int) $row['id_alumno']] = ['id_tipos_asistencia' => $row['id_tipos_asistencia']];
        }
        return $data;
    }

    public function update(Request $request , Assistance $assistance, Student $student){
        if (!$this->canAccessCourse($assistance->id_curso)) {
            return $this->forbiddenCourse();
        }

        $assistance->studentsAssisted()->detach($student);
        $assistance->save();
        return $this->showOne($assistance);

    }

}
