<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Oráculo: GET /api/courses/{course}/students solo lista alumnos de cursos habilitados del año
 * lectivo activo. Para un curso propio de otro año o deshabilitado responde 200 con lista vacía,
 * nunca 500 (antes: "Undefined offset: 0").
 */
class CourseStudentsInactiveCourseTest extends TestCase
{
    use DatabaseTransactions;

    private function teacherCourse(array $overrides): array
    {
        $teacher = User::where('role', 'user')->whereNotNull('id_profesor')->orderBy('id')->firstOrFail();
        $base = Course::where('id_profesor', $teacher->id_profesor)->first() ?? Course::firstOrFail();
        $course = $base->replicate();
        $course->id_profesor = $teacher->id_profesor;
        foreach ($overrides as $k => $v) {
            $course->{$k} = $v;
        }
        $course->save();
        return [$teacher, $course];
    }

    public function test_curso_propio_de_un_anio_anterior_devuelve_lista_vacia()
    {
        $pastYear = SchoolYear::where('active', false)->orderByDesc('id')->firstOrFail();
        [$teacher, $course] = $this->teacherCourse(['school_year_id' => $pastYear->id, 'status' => true]);

        $this->getJson("/api/courses/{$course->id}/students", $this->authHeaders($teacher))
            ->assertOk()->assertExactJson([]);
    }

    public function test_curso_propio_deshabilitado_devuelve_lista_vacia()
    {
        $activeYear = SchoolYear::where('active', true)->firstOrFail();
        [$teacher, $course] = $this->teacherCourse(['school_year_id' => $activeYear->id, 'status' => false]);

        $this->getJson("/api/courses/{$course->id}/students", $this->authHeaders($teacher))
            ->assertOk()->assertExactJson([]);
    }

    public function test_curso_activo_sigue_devolviendo_sus_alumnos()
    {
        $activeYear = SchoolYear::where('active', true)->firstOrFail();
        $course = Course::where('status', true)->where('school_year_id', $activeYear->id)
            ->whereHas('students')->orderBy('id')->firstOrFail();

        $res = $this->getJson("/api/courses/{$course->id}/students", $this->authHeaders($this->admin()))->assertOk();

        $this->assertSame($course->students()->count(), count($res->json()));
    }
}
