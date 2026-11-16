<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Oráculo:
 *  - OWASP API4 (consumo de recursos): toda la API tiene un límite global de 300 peticiones
 *    por minuto por usuario (holgado para la UI, corta scraping/abuso). Se ve en X-RateLimit-Limit.
 *  - OWASP A07: no se pueden crear usuarios con contraseñas de menos de 8 caracteres.
 */
class RateLimitAndPasswordTest extends TestCase
{
    use DatabaseTransactions;

    public function test_la_api_tiene_limite_global_de_300_por_minuto()
    {
        $res = $this->getJson('/api/courses', $this->authHeaders($this->admin()));
        $res->assertOk();
        $res->assertHeader('X-RateLimit-Limit', '300');
    }

    private function newUserPayload(string $password): array
    {
        return [
            'name' => 'Test TDD',
            'email' => 'tdd-' . uniqid() . '@example.test',
            'password' => $password,
            'teacher' => DB::table('profesores')->value('id'),
            'role' => 'user',
        ];
    }

    public function test_no_se_crea_usuario_con_contrasena_corta()
    {
        $payload = $this->newUserPayload('1234567');
        $this->postJson('/api/users', $payload, $this->authHeaders($this->admin()))
            ->assertStatus(422)->assertJsonStructure(['password']);
        $this->assertFalse(User::where('email', $payload['email'])->exists());
    }

    public function test_se_crea_usuario_con_contrasena_de_8()
    {
        $payload = $this->newUserPayload('12345678');
        $this->postJson('/api/users', $payload, $this->authHeaders($this->admin()))->assertSuccessful();
        $this->assertTrue(User::where('email', $payload['email'])->exists());
    }
}
