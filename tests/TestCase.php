<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Protección: no correr como root. Los archivos que crea la app en storage/ (caché,
        // vistas compiladas, logs) quedarían de root y php-fpm (www-data) no podría escribirlos.
        // Usar: docker exec -u www-data clans_app php vendor/bin/phpunit
        if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
            $this->fail('Tests abortados: no ejecutar como root. Use: docker exec -u www-data clans_app php vendor/bin/phpunit');
        }

        // Protección: abortar si la conexión no apunta a la base de pruebas.
        $database = DB::connection()->getDatabaseName();
        if ($database !== 'clans_test') {
            $this->fail("Tests abortados: la base es '{$database}', se esperaba 'clans_test'.");
        }
    }

    /** Cabeceras con un JWT válido para el usuario dado. */
    protected function authHeaders(User $user): array
    {
        return [
            'Authorization' => 'Bearer ' . JWTAuth::fromUser($user),
            'Accept' => 'application/json',
        ];
    }

    protected function admin(): User
    {
        return User::where('role', 'admin')->firstOrFail();
    }
}
