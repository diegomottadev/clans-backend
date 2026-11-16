<?php

namespace Tests\Feature\Security\Owasp;

use App\Models\Assistance;
use App\Models\Evaluation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * H6 (A01 / API1 BOLA): un docente no puede ver ni modificar cursos, asistencias ni
 * evaluaciones de cursos de otros docentes. Sobre los propios, sí.
 */
class H06ForeignCourseAccessTest extends TestCase
{
    use DatabaseTransactions, TeacherFixtures;

    public function test_ver_curso_ajeno_403_y_propio_200()
    {
        $t = $this->teacher();
        $h = $this->authHeaders($t);
        $this->getJson('/api/courses/' . $this->foreignCourse($t)->id, $h)->assertStatus(403);
        $this->getJson('/api/courses/' . $this->ownCourse($t)->id, $h)->assertOk();
    }

    public function test_alumnos_de_curso_ajeno_403()
    {
        $t = $this->teacher();
        $this->getJson('/api/courses/' . $this->foreignCourse($t)->id . '/students', $this->authHeaders($t))->assertStatus(403);
    }

    public function test_crear_asistencia_en_curso_ajeno_403()
    {
        $t = $this->teacher();
        $before = Assistance::count();
        $this->postJson('/api/assistances', ['date' => '2026-11-10', 'course' => $this->foreignCourse($t)->id], $this->authHeaders($t))
            ->assertStatus(403);
        $this->assertSame($before, Assistance::count());
    }

    public function test_editar_y_borrar_asistencia_ajena_403()
    {
        $t = $this->teacher();
        $a = Assistance::create(['fecha' => '2026-11-10', 'id_curso' => $this->foreignCourse($t)->id]);
        $h = $this->authHeaders($t);

        $this->putJson("/api/assistances/{$a->id}", ['date' => '2026-11-11'], $h)->assertStatus(403);
        $this->deleteJson("/api/assistances/{$a->id}", [], $h)->assertStatus(403);

        $fresh = Assistance::find($a->id);
        $this->assertNotNull($fresh);
        $this->assertSame('2026-11-10', substr((string) $fresh->getRawOriginal('fecha'), 0, 10));
    }

    public function test_guardar_alumnos_en_asistencia_ajena_403()
    {
        $t = $this->teacher();
        $a = Assistance::create(['fecha' => '2026-11-10', 'id_curso' => $this->foreignCourse($t)->id]);
        $this->postJson("/api/assistances/{$a->id}/students", ['assistancesStudents' => [
            ['studentId' => DB::table('alumnos')->value('id'), 'typeAssistanceId' => DB::table('tipos_asistencias')->value('id')],
        ]], $this->authHeaders($t))->assertStatus(403);
        $this->assertSame(0, DB::table('asistencias')->where('id_fechas_curso', $a->id)->count());
    }

    public function test_editar_borrar_y_calificar_evaluacion_ajena_403()
    {
        $t = $this->teacher();
        $evaluation = Evaluation::whereIn('id_curso', DB::table('cursos')->where('id_profesor', '!=', $t->id_profesor)->select('id'))
            ->orderBy('id')->firstOrFail();
        $h = $this->authHeaders($t);

        // cuerpo válido: así la validación pasa y lo que se prueba es la autorización
        $this->putJson("/api/evaluations/{$evaluation->id}", [
            'name' => 'hackeado', 'course' => $evaluation->id_curso,
            'typeEvaluation' => $evaluation->id_tipo_evaluacion ?? DB::table('tipo_evaluacion')->value('id'),
            'date' => '2026-11-10',
        ], $h)->assertStatus(403);
        $this->deleteJson("/api/evaluations/{$evaluation->id}", [], $h)->assertStatus(403);
        $this->postJson("/api/evaluations/{$evaluation->id}/students", ['student' => 1, 'concept' => 1], $h)->assertStatus(403);

        $fresh = Evaluation::find($evaluation->id);
        $this->assertNotNull($fresh);
        $this->assertNotSame('hackeado', $fresh->nombre);
    }
}
