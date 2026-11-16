<?php

namespace Tests\Feature\Security\Owasp;

use Tests\TestCase;

/** H8 (A07 / API4): fuerza bruta de login limitada; el bloqueo de un email no afecta a otro. */
class H08LoginRateLimitTest extends TestCase
{
    public function test_el_intento_11_por_minuto_responde_429()
    {
        $body = ['email' => 'atacante-' . uniqid() . '@example.test', 'password' => 'incorrecta'];
        for ($i = 1; $i <= 10; $i++) {
            $this->assertNotSame(429, $this->postJson('/api/auth/login', $body)->getStatusCode(), "intento $i");
        }
        $this->postJson('/api/auth/login', $body)->assertStatus(429);
    }

    public function test_bloquear_un_email_no_bloquea_el_login_de_otro_usuario()
    {
        $body = ['email' => 'atacante-' . uniqid() . '@example.test', 'password' => 'incorrecta'];
        for ($i = 1; $i <= 11; $i++) {
            $this->postJson('/api/auth/login', $body);
        }
        $this->postJson('/api/auth/login', ['email' => $this->admin()->email, 'password' => 'incorrecta-pero-otro-email'])
            ->assertStatus(401);
    }
}
