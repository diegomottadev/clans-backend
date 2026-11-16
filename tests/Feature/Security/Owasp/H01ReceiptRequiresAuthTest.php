<?php

namespace Tests\Feature\Security\Owasp;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** H1 (A01 / API1 BOLA): el recibo de pago a docente no se descarga sin autenticación de admin. */
class H01ReceiptRequiresAuthTest extends TestCase
{
    use TeacherFixtures;

    private function url(): string
    {
        return '/api/reports/downloadReceiptByPaymentTeacher/' . DB::table('pagos_profesores')->orderByDesc('id')->value('id');
    }

    public function test_sin_token_responde_401_y_no_entrega_el_pdf()
    {
        $res = $this->get($this->url(), ['Accept' => 'application/json']);
        $res->assertStatus(401);
        $this->assertStringStartsNotWith('%PDF', $res->getContent());
    }

    public function test_docente_no_puede_descargar_recibos()
    {
        $this->get($this->url(), $this->authHeaders($this->teacher()))->assertStatus(403);
    }
}
