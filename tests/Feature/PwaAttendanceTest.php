<?php

namespace Tests\Feature;

use App\Models\Assistance;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Oráculo: la PWA docente (mobile/pwa-docente/src/views/AttendanceView.vue) guarda asistencias con
 *   POST /assistances            { fecha, id_curso }
 *   POST /assistances/{id}/students { students: [{ id_alumno, id_tipos_asistencia }] }
 *   PUT  /assistances/{id}/students { students: [...] }   (al editar una existente)
 * y la web sigue usando { date, course } y { assistancesStudents: [{ studentId, typeAssistanceId }] }.
 */
class PwaAttendanceTest extends TestCase
{
    use DatabaseTransactions;

    private function teacherWithCourse(): array
    {
        $teacher = User::where('role', 'user')->whereNotNull('id_profesor')
            ->whereIn('id_profesor', Course::select('id_profesor'))
            ->firstOrFail();
        $course = Course::where('id_profesor', $teacher->id_profesor)->firstOrFail();
        $foreign = Course::where('id_profesor', '!=', $teacher->id_profesor)->firstOrFail();
        $students = DB::table('alumnos')->limit(2)->pluck('id')->all();
        $types = DB::table('tipos_asistencias')->limit(2)->pluck('id')->all();
        return [$teacher, $course, $foreign, $students, $types];
    }

    private function pivot(int $assistanceId): array
    {
        return DB::table('asistencias')->where('id_fechas_curso', $assistanceId)
            ->orderBy('id_alumno')->pluck('id_tipos_asistencia', 'id_alumno')->all();
    }

    public function test_pwa_crea_asistencia_con_fecha_e_id_curso()
    {
        [$teacher, $course] = $this->teacherWithCourse();

        $res = $this->postJson('/api/assistances', ['fecha' => '2026-11-10', 'id_curso' => $course->id], $this->authHeaders($teacher));

        $res->assertSuccessful();
        $id = $res->json('data.id') ?? $res->json('id');
        $this->assertNotNull($id);
        $this->assertSame($course->id, (int) Assistance::findOrFail($id)->id_curso);
    }

    public function test_pwa_guarda_alumnos_con_post_formato_students()
    {
        [$teacher, $course, , $students, $types] = $this->teacherWithCourse();
        $a = Assistance::create(['fecha' => '2026-11-10', 'id_curso' => $course->id]);

        $res = $this->postJson("/api/assistances/{$a->id}/students", ['students' => [
            ['id_alumno' => $students[0], 'id_tipos_asistencia' => $types[0]],
            ['id_alumno' => $students[1], 'id_tipos_asistencia' => $types[1]],
        ]], $this->authHeaders($teacher));

        $res->assertSuccessful();
        $this->assertEquals([$students[0] => $types[0], $students[1] => $types[1]], $this->pivot($a->id));
    }

    public function test_pwa_edita_alumnos_con_put()
    {
        [$teacher, $course, , $students, $types] = $this->teacherWithCourse();
        $a = Assistance::create(['fecha' => '2026-11-10', 'id_curso' => $course->id]);
        $a->studentsAssisted()->attach([$students[0] => ['id_tipos_asistencia' => $types[0]]]);

        $res = $this->putJson("/api/assistances/{$a->id}/students", ['students' => [
            ['id_alumno' => $students[0], 'id_tipos_asistencia' => $types[1]],
        ]], $this->authHeaders($teacher));

        $res->assertSuccessful();
        $this->assertEquals([$students[0] => $types[1]], $this->pivot($a->id));
    }

    public function test_pwa_put_sobre_curso_ajeno_da_403_y_no_modifica()
    {
        [$teacher, , $foreign, $students, $types] = $this->teacherWithCourse();
        $a = Assistance::create(['fecha' => '2026-11-10', 'id_curso' => $foreign->id]);
        $a->studentsAssisted()->attach([$students[0] => ['id_tipos_asistencia' => $types[0]]]);

        $this->putJson("/api/assistances/{$a->id}/students", ['students' => [
            ['id_alumno' => $students[0], 'id_tipos_asistencia' => $types[1]],
        ]], $this->authHeaders($teacher))->assertStatus(403);

        $this->assertEquals([$students[0] => $types[0]], $this->pivot($a->id));
    }

    public function test_web_sigue_funcionando_con_formato_original()
    {
        [, $course, , $students, $types] = $this->teacherWithCourse();
        $admin = $this->admin();

        $res = $this->postJson('/api/assistances', ['date' => '2026-11-10', 'course' => $course->id], $this->authHeaders($admin));
        $res->assertSuccessful();
        $id = $res->json('data.id');

        $this->postJson("/api/assistances/{$id}/students", ['assistancesStudents' => [
            ['studentId' => $students[0], 'typeAssistanceId' => $types[0]],
        ]], $this->authHeaders($admin))->assertSuccessful();

        $this->assertEquals([$students[0] => $types[0]], $this->pivot($id));
    }
}
