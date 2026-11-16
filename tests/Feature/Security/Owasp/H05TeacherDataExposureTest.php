<?php

namespace Tests\Feature\Security\Owasp;

use Tests\TestCase;

/** H5 (A01 / API3): el docente no accede al padrón de alumnos (DNI, teléfonos), deudores ni facturas. */
class H05TeacherDataExposureTest extends TestCase
{
    use TeacherFixtures;

    public function endpoints(): array
    {
        return [
            'alumnos' => ['/api/students'],
            'conteo de alumnos' => ['/api/students/counts'],
            'deudores' => ['/api/debtors'],
            'deudores auto' => ['/api/debtors/auto'],
            'facturas' => ['/api/invoices'],
        ];
    }

    /** @dataProvider endpoints */
    public function test_docente_recibe_403($uri)
    {
        $this->getJson($uri, $this->authHeaders($this->teacher()))->assertStatus(403);
    }

    public function test_docente_no_ve_la_ficha_de_un_alumno()
    {
        $id = \Illuminate\Support\Facades\DB::table('alumnos')->orderBy('id')->value('id');
        $this->getJson("/api/students/{$id}", $this->authHeaders($this->teacher()))->assertStatus(403);
    }

    public function test_docente_ve_los_alumnos_de_su_propio_curso()
    {
        $teacher = $this->teacher();
        $this->getJson('/api/courses/' . $this->ownCourse($teacher)->id . '/students', $this->authHeaders($teacher))->assertOk();
    }
}
