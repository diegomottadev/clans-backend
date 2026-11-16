<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Oráculo "Mi cuenta": el usuario logueado (admin o docente) ve y edita SUS datos
 * vía GET/PUT /api/me. El email de ingreso se muestra pero no se puede modificar.
 */
class MyAccountTest extends TestCase
{
    use DatabaseTransactions;

    private function teacherUser(): User
    {
        return User::where('role', 'user')->whereNotNull('id_profesor')->firstOrFail();
    }

    public function test_me_requiere_autenticacion()
    {
        $this->getJson('/api/me')->assertStatus(401);
    }

    public function test_me_devuelve_los_datos_del_usuario_logueado_y_su_docente()
    {
        $user = $this->teacherUser();
        $teacher = $user->teacher;

        $res = $this->getJson('/api/me', $this->authHeaders($user));

        $res->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.name', $user->name)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.role', 'user')
            ->assertJsonPath('data.has_avatar', false)
            ->assertJsonPath('data.teacher.firstName', (string) $teacher->nombre)
            ->assertJsonPath('data.teacher.lastName', (string) $teacher->apellido)
            ->assertJsonPath('data.teacher.dni', (string) $teacher->dni)
            ->assertJsonPath('data.teacher.address', (string) $teacher->domicilio);
        $this->assertArrayNotHasKey('password', $res->json('data'));
        $this->assertArrayNotHasKey('avatar_path', $res->json('data'));
    }

    private function profilePayload(User $user, array $overrides = []): array
    {
        return array_replace_recursive([
            'name' => 'Nombre Editado',
            'teacher' => [
                'firstName' => 'Ana',
                'lastName' => 'Pérez',
                'dni' => '30111222',
                'telphone' => '4111111',
                'celphone' => '3515555555',
                'email' => 'contacto@ejemplo.com',
                'address' => 'Calle Falsa 123',
            ],
        ], $overrides);
    }

    public function test_put_me_actualiza_nombre_y_datos_del_docente()
    {
        $user = $this->teacherUser();

        $res = $this->putJson('/api/me', $this->profilePayload($user), $this->authHeaders($user));

        $res->assertOk()->assertJsonPath('data.name', 'Nombre Editado');
        $user->refresh();
        $this->assertSame('Nombre Editado', $user->name);
        $t = $user->teacher;
        $this->assertSame('Ana', $t->nombre);
        $this->assertSame('Pérez', $t->apellido);
        $this->assertSame('Ana Pérez', $t->nombreCompleto);
        $this->assertSame('30111222', $t->dni);
        $this->assertSame('3515555555', $t->cel_part);
        $this->assertSame('Calle Falsa 123', $t->domicilio);
    }

    public function test_put_me_rechaza_cambiar_el_email_de_ingreso()
    {
        $user = $this->teacherUser();
        $original = $user->email;

        $res = $this->putJson('/api/me', $this->profilePayload($user, ['email' => 'otro@hack.com']), $this->authHeaders($user));

        $res->assertStatus(422)->assertJsonStructure(['email']);
        $this->assertSame($original, $user->fresh()->email);
        $this->assertNotSame('Nombre Editado', $user->fresh()->name, 'Si se rechaza, no debe aplicar nada.');
    }

    public function test_put_me_acepta_el_mismo_email_sin_cambios()
    {
        $user = $this->teacherUser();

        $this->putJson('/api/me', $this->profilePayload($user, ['email' => $user->email]), $this->authHeaders($user))
            ->assertOk();
        $this->assertSame('Nombre Editado', $user->fresh()->name);
    }

    public function test_put_me_no_permite_cambiar_rol_ni_docente_asociado()
    {
        $user = $this->teacherUser();
        $teacherId = $user->id_profesor;

        $this->putJson('/api/me', $this->profilePayload($user, ['role' => 'admin', 'id_profesor' => 1, 'teacher' => ['id' => 1]]), $this->authHeaders($user))
            ->assertOk();
        $fresh = $user->fresh();
        $this->assertSame('user', $fresh->role);
        $this->assertSame($teacherId, $fresh->id_profesor);
    }

    public function test_put_me_valida_nombre_requerido()
    {
        $user = $this->teacherUser();

        $this->putJson('/api/me', $this->profilePayload($user, ['name' => '']), $this->authHeaders($user))
            ->assertStatus(422)->assertJsonStructure(['name']);
    }

    private function userWithPassword(string $plain): User
    {
        $user = $this->teacherUser();
        $user->password = bcrypt($plain);
        $user->save();
        return $user;
    }

    public function test_cambio_de_password_con_password_actual_correcta()
    {
        $user = $this->userWithPassword('Actual1234');

        $this->putJson('/api/me', $this->profilePayload($user, [
            'current_password' => 'Actual1234',
            'password' => 'NuevaClave99',
            'password_confirmation' => 'NuevaClave99',
        ]), $this->authHeaders($user))->assertOk();

        $this->assertTrue(Hash::check('NuevaClave99', $user->fresh()->password));
    }

    public function test_cambio_de_password_rechaza_password_actual_incorrecta()
    {
        $user = $this->userWithPassword('Actual1234');

        $this->putJson('/api/me', $this->profilePayload($user, [
            'current_password' => 'equivocada',
            'password' => 'NuevaClave99',
            'password_confirmation' => 'NuevaClave99',
        ]), $this->authHeaders($user))->assertStatus(422)->assertJsonStructure(['current_password']);

        $this->assertTrue(Hash::check('Actual1234', $user->fresh()->password));
    }

    public function test_cambio_de_password_exige_password_actual()
    {
        $user = $this->userWithPassword('Actual1234');

        $this->putJson('/api/me', $this->profilePayload($user, [
            'password' => 'NuevaClave99',
            'password_confirmation' => 'NuevaClave99',
        ]), $this->authHeaders($user))->assertStatus(422)->assertJsonStructure(['current_password']);
    }

    public function test_cambio_de_password_exige_minimo_8_y_confirmacion()
    {
        $user = $this->userWithPassword('Actual1234');

        $this->putJson('/api/me', $this->profilePayload($user, [
            'current_password' => 'Actual1234',
            'password' => 'corta',
            'password_confirmation' => 'corta',
        ]), $this->authHeaders($user))->assertStatus(422)->assertJsonStructure(['password']);

        $this->putJson('/api/me', $this->profilePayload($user, [
            'current_password' => 'Actual1234',
            'password' => 'NuevaClave99',
            'password_confirmation' => 'OtraCosa99',
        ]), $this->authHeaders($user))->assertStatus(422)->assertJsonStructure(['password']);

        $this->assertTrue(Hash::check('Actual1234', $user->fresh()->password));
    }

    public function test_admin_sin_docente_puede_editar_su_nombre()
    {
        $admin = $this->admin();
        $admin->id_profesor = null;
        $admin->save();

        $this->putJson('/api/me', ['name' => 'Admin Nuevo'], $this->authHeaders($admin))
            ->assertOk()->assertJsonPath('data.teacher', null);
        $this->assertSame('Admin Nuevo', $admin->fresh()->name);
    }
}
