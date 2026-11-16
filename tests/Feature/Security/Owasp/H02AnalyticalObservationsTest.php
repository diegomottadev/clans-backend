<?php

namespace Tests\Feature\Security\Owasp;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * H2 (A03/A08, RCE): las observaciones del PDF analítico llegan del request y se imprimen
 * con {!! !!}. No deben llegar a la vista ni <script> (incl. text/php) ni atributos (on*, src...).
 */
class H02AnalyticalObservationsTest extends TestCase
{
    public function test_observaciones_maliciosas_se_sanean_antes_de_la_vista()
    {
        $row = DB::table('evaluaciones_alumnos as ae')
            ->join('evaluaciones as e', 'e.id', '=', 'ae.id_evaluacion')
            ->select('ae.id_alumno', 'e.id_curso')->orderByDesc('e.fecha')->first();
        $this->assertNotNull($row, 'se necesita un alumno con evaluaciones en clans_test');

        $captured = null;
        View::composer('reportes.analytical', function ($view) use (&$captured) {
            $captured = $view->getData()['result']['observations'] ?? null;
        });

        $payload = '<p>Bien <b>hecho</b></p><script type="text/php">system("id");</script>'
            . '<img src="x" onerror="alert(1)"><span style="x" onclick="alert(2)">ok</span>';

        $res = $this->get('/api/reports/downloadReportAnalytical?' . http_build_query([
            'studentId' => $row->id_alumno, 'courseId' => $row->id_curso,
            'dateInit' => '2000-01-01', 'dateFinish' => '2100-12-31',
            'oralidad' => 'Muy bien', 'participacion' => 'Bien', 'cumplimiento' => 'Bien',
            'observations' => $payload,
        ]), $this->authHeaders($this->admin()));

        $res->assertOk();
        $this->assertNotNull($captured, 'la vista reportes.analytical no se renderizó');
        $this->assertStringNotContainsStringIgnoringCase('<script', $captured);
        $this->assertStringNotContainsStringIgnoringCase('<img', $captured);
        $this->assertStringNotContainsStringIgnoringCase('onerror', $captured);
        $this->assertStringNotContainsStringIgnoringCase('onclick', $captured);
        $this->assertStringContainsString('<b>hecho</b>', $captured);
    }
}
