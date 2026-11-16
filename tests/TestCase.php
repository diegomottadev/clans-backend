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
