<?php

namespace Tests\Feature\Security\Owasp;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * H9 (A05): isAdmin sin usuario responde 401 JSON (antes: error al leer ->role de null).
 * H10 (A04): el rol solo puede ser admin|user y el email debe ser un email válido.
 */
class H09H10AdminMiddlewareAndUserValidationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_ruta_de_admin_sin_token_responde_401_json()
    {
        $this->getJson('/api/users')->assertStatus(401)->assertJson(['code' => 401]);
    }

    private function payload(array $override): array
    {
        return array_merge([
            'name' => 'Test', 'email' => 'tdd-' . uniqid() . '@example.test', 'password' => '12345678',
            'teacher' => DB::table('profesores')->value('id'), 'role' => 'user',
        ], $override);
    }

    public function test_no_se_puede_crear_un_rol_inventado()
    {
        $p = $this->payload(['role' => 'superadmin']);
        $this->postJson('/api/users', $p, $this->authHeaders($this->admin()))->assertStatus(422)->assertJsonStructure(['role']);
        $this->assertFalse(User::where('email', $p['email'])->exists());
    }

    public function test_no_se_puede_cambiar_a_un_rol_inventado()
    {
        $user = User::where('role', 'user')->whereNotNull('id_profesor')->firstOrFail();
        $this->putJson("/api/users/{$user->id}", [
            'name' => $user->name, 'email' => $user->email, 'teacher' => $user->id_profesor, 'role' => 'root',
        ], $this->authHeaders($this->admin()))->assertStatus(422);
        $this->assertSame('user', $user->fresh()->role);
    }

    public function test_email_invalido_responde_422()
    {
        $p = $this->payload(['email' => 'no-es-un-email']);
        $this->postJson('/api/users', $p, $this->authHeaders($this->admin()))->assertStatus(422)->assertJsonStructure(['email']);
        $this->assertFalse(User::where('email', 'no-es-un-email')->exists());
    }
}
