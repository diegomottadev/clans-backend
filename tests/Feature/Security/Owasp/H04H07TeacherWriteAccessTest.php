<?php

namespace Tests\Feature\Security\Owasp;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * H4 / H7 (A01 / API5): un docente (role=user) no puede crear, editar ni borrar datos maestros
 * (alumnos, docentes, niveles, cursos, tipos) ni inscribir alumnos. Conserva la lectura que usa.
 */
class H04H07TeacherWriteAccessTest extends TestCase
{
    use DatabaseTransactions, TeacherFixtures;

    public function requests(): array
    {
        return [
            'crear alumno'          => ['POST', '/api/students', ['name' => 'X', 'lastname' => 'Y']],
            'editar alumno'         => ['PUT', '/api/students/{student}', ['name' => 'X']],
            'borrar alumno'         => ['DELETE', '/api/students/{student}', []],
            'inscribir alumno'      => ['POST', '/api/students/{student}/courses', ['courses' => [1]]],
            'crear docente'         => ['POST', '/api/teachers', ['name' => 'X']],
            'borrar docente'        => ['DELETE', '/api/teachers/{teacher}', []],
            'crear nivel'           => ['POST', '/api/levels', ['name' => 'X']],
            'borrar nivel'          => ['DELETE', '/api/levels/{level}', []],
            'crear curso'           => ['POST', '/api/courses', ['name' => 'X']],
            'editar curso'          => ['PUT', '/api/courses/{course}', ['name' => 'X']],
            'borrar curso'          => ['DELETE', '/api/courses/{course}', []],
            'crear tipo asistencia' => ['POST', '/api/typeAssistances', ['name' => 'X']],
            'crear tipo evaluacion' => ['POST', '/api/typeEvaluations', ['name' => 'X']],
            'crear tipo de gasto'   => ['POST', '/api/typeExpenses', ['name' => 'X']],
            'borrar tipo de gasto'  => ['DELETE', '/api/typeExpenses/{typeExpense}', []],
            'crear tipo de curso'   => ['POST', '/api/typeCourses', ['name' => 'X']],
            'listar tipos de curso' => ['GET', '/api/typeCourses', []],
            'listar tipos de gasto' => ['GET', '/api/typeExpenses', []],
        ];
    }

    /** @dataProvider requests */
    public function test_docente_recibe_403_y_no_se_modifica_nada($method, $uri, $body)
    {
        $ids = [
            '{student}' => DB::table('alumnos')->orderBy('id')->value('id'),
            '{teacher}' => DB::table('profesores')->orderBy('id')->value('id'),
            '{level}' => DB::table('niveles')->orderBy('id')->value('id'),
            '{course}' => DB::table('cursos')->orderBy('id')->value('id'),
            '{typeExpense}' => DB::table('tipos_egresos')->orderBy('id')->value('id'),
        ];
        $tables = ['alumnos', 'profesores', 'niveles', 'cursos', 'tipos_asistencias', 'tipo_evaluacion', 'tipos_egresos', 'tipos_cursado'];
        $count = function () use ($tables) {
            return array_map(function ($t) { return DB::table($t)->count(); }, $tables);
        };
        $before = $count();

        $res = $this->json($method, strtr($uri, $ids), $body, $this->authHeaders($this->teacher()));

        $res->assertStatus(403);
        $this->assertSame($before, $count());
    }

    public function test_docente_sigue_pudiendo_leer_lo_que_usa()
    {
        $headers = $this->authHeaders($this->teacher());
        foreach (['/api/courses', '/api/levels', '/api/teachers', '/api/typeAssistances', '/api/typeEvaluations', '/api/calendar'] as $uri) {
            $this->getJson($uri, $headers)->assertOk();
        }
    }
}
