<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Tests\TestCase;

/**
 * Oráculo:
 *  - Un docente que pide un recurso solo de admin recibe 403 Forbidden (no 404, que
 *    oculta el motivo y confunde los logs).
 *  - Ningún endpoint de reportes responde 500 por parámetros faltantes: debe responder 422.
 *  - La ruta huérfana reports/teachers (sin método en el controlador) no existe: 404, no 500.
 */
class AuthorizationAndErrorsTest extends TestCase
{
    private function teacher(): User
    {
        return User::where('role', 'user')->whereNotNull('id_profesor')->firstOrFail();
    }

    public function test_docente_en_ruta_de_admin_recibe_403()
    {
        $this->getJson('/api/students', $this->authHeaders($this->teacher()))->assertStatus(403);
    }

    public function test_sin_token_en_ruta_de_admin_recibe_401()
    {
        $this->getJson('/api/students')->assertStatus(401);
    }

    public function test_reports_teachers_no_existe()
    {
        $this->getJson('/api/reports/teachers', $this->authHeaders($this->admin()))->assertStatus(404);
    }

    public function test_reporte_analitico_sin_alumno_da_422()
    {
        $this->getJson('/api/reports/reportAnalytical', $this->authHeaders($this->admin()))
            ->assertStatus(422)->assertJsonStructure(['studentId']);
    }

    public function test_reporte_analitico_con_alumno_inexistente_da_422()
    {
        $this->getJson('/api/reports/reportAnalytical?studentId=999999999&courseId=1&dateInit=2026-01-01&dateFinish=2026-12-31',
            $this->authHeaders($this->admin()))->assertStatus(422);
    }

    public function test_descarga_analitico_sin_alumno_da_422()
    {
        $this->getJson('/api/reports/downloadReportAnalytical', $this->authHeaders($this->admin()))->assertStatus(422);
    }
}
