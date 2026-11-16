<?php

namespace Tests\Feature\Security;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Oráculo (OWASP A05/A06, CVE-2022-28368 en dompdf 1.x):
 *  - dompdf no descarga recursos remotos (enable_remote = false).
 *  - dompdf solo puede leer archivos dentro de public/ (chroot), nunca .env ni el código.
 *  - Los PDF se siguen generando y el layout referencia el CSS como archivo local.
 */
class PdfHardeningTest extends TestCase
{
    public function test_dompdf_no_descarga_recursos_remotos()
    {
        $this->assertFalse((bool) config('dompdf.defines.enable_remote'));
    }

    public function test_dompdf_no_ejecuta_php()
    {
        $this->assertFalse((bool) config('dompdf.defines.enable_php'));
    }

    public function test_chroot_de_dompdf_es_public_y_no_incluye_env()
    {
        $chroot = config('dompdf.defines.chroot');
        $this->assertSame(realpath(public_path()), $chroot);
        $this->assertStringStartsNotWith($chroot . DIRECTORY_SEPARATOR, base_path('.env'));
    }

    public function test_layout_pdf_usa_css_local_dentro_del_chroot()
    {
        $html = view('layouts.pdf')->render();
        $this->assertStringContainsString(public_path('libs/bootstrap/bootstrap.min.css'), $html);
        $this->assertStringNotContainsString('http://', $html);
        $this->assertStringNotContainsString('https://', $html);
    }

    public function test_pdf_de_recibo_de_factura_se_sigue_generando()
    {
        $invoiceId = DB::table('facturas')->whereNull('deleted_at')->orderByDesc('id')->value('id');

        $res = $this->get("/api/reports/downloadReportByInvoice/{$invoiceId}", $this->authHeaders($this->admin()));

        $res->assertOk();
        $this->assertStringStartsWith('%PDF', $res->getContent());
    }

    public function test_pdf_de_recibo_de_pago_a_docente_se_sigue_generando()
    {
        $id = DB::table('pagos_profesores')->orderByDesc('id')->value('id');
        $res = $this->get("/api/reports/downloadReceiptByPaymentTeacher/{$id}", $this->authHeaders($this->admin()));
        $res->assertOk();
        $this->assertStringStartsWith('%PDF', $res->getContent());
    }

    public function test_pdf_de_asistencias_por_curso_se_sigue_generando()
    {
        $courseId = DB::table('fechas_curso')->orderByDesc('id')->value('id_curso');
        $res = $this->get("/api/reports/downloadReportByCourse?courseId={$courseId}", $this->authHeaders($this->admin()));
        $res->assertOk();
        $this->assertStringStartsWith('%PDF', $res->getContent());
    }
}
