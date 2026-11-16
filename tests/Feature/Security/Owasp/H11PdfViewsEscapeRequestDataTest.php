<?php

namespace Tests\Feature\Security\Owasp;

use Tests\TestCase;

/** H11 (A03, inyección de HTML en PDF): fechas y mes del request se imprimen escapados. */
class H11PdfViewsEscapeRequestDataTest extends TestCase
{
    private const XSS = '<script>alert(1)</script>';
    private const IMG = '<img src=x onerror=alert(2)>';

    private function assertEscaped(string $html): void
    {
        $this->assertStringNotContainsString(self::XSS, $html);
        $this->assertStringNotContainsString(self::IMG, $html);
        $this->assertStringContainsString(e(self::XSS), $html);
    }

    private function student(): object
    {
        return (object) ['nombreCompleto' => 'Alumno Test'];
    }

    public function test_asistencias_por_curso()
    {
        $named = (object) ['nombre' => 'X'];
        $this->assertEscaped(view('reportes.assistencesByCourse', [
            'languaje' => $named, 'level' => $named, 'course' => $named,
            'dateInit' => self::XSS, 'dateFinish' => self::IMG, 'month' => self::XSS, 'assistences' => [],
        ])->render());
    }

    public function test_asistencias_por_alumno()
    {
        $this->assertEscaped(view('reportes.assistenceByStudent', [
            'student' => $this->student(), 'dateInit' => self::XSS, 'dateFinish' => self::IMG,
            'month' => self::XSS, 'assistences' => [], 'countByTypeAssistence' => [],
        ])->render());
    }

    public function test_informe_completo_por_alumno()
    {
        $this->assertEscaped(view('reportes.completeByStudent', [
            'student' => $this->student(), 'dateInit' => self::XSS, 'dateFinish' => self::IMG,
            'assistences' => [], 'evaluations' => [], 'invoices' => [], 'countByTypeAssistence' => [],
        ])->render());
    }

    public function test_analitico()
    {
        $row = \Illuminate\Support\Facades\DB::table('evaluaciones_alumnos as ae')
            ->join('evaluaciones as e', 'e.id', '=', 'ae.id_evaluacion')
            ->select('ae.id_alumno', 'e.id_curso')->orderByDesc('e.fecha')->first();

        // Se pasa por el controlador real y se capturan los datos que recibe la vista
        $data = null;
        \Illuminate\Support\Facades\View::composer('reportes.analytical', function ($view) use (&$data) {
            $data = $data ?? $view->getData();
        });
        $this->get('/api/reports/downloadReportAnalytical?' . http_build_query([
            'studentId' => $row->id_alumno, 'courseId' => $row->id_curso,
            'dateInit' => self::XSS, 'dateFinish' => self::IMG,
        ]), $this->authHeaders($this->admin()))->assertOk();

        $this->assertNotNull($data);
        $this->assertEscaped(view('reportes.analytical', $data)->render());
    }
}
