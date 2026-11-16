<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

/**
 * Oráculo (OWASP A07 / ASVS 3.3.1): cerrar sesión invalida el JWT en el servidor.
 * Después de POST /api/auth/logout, el mismo token ya no sirve (401).
 *
 * La lista negra del JWT vive en la caché. En tests la caché es "array" (en memoria) y se
 * pierde al recrear la aplicación, así que este test usa la caché en archivo, como la app real.
 */
class LogoutInvalidatesTokenTest extends TestCase
{
    private $previousCacheDriver;

    private function useFileCache(): void
    {
        $this->previousCacheDriver = $_SERVER['CACHE_DRIVER'] ?? null;
        $_SERVER['CACHE_DRIVER'] = $_ENV['CACHE_DRIVER'] = 'file';
        putenv('CACHE_DRIVER=file');
        $this->refreshApplication();
    }


    protected function tearDown(): void
    {
        if ($this->previousCacheDriver !== null) {
            $_SERVER['CACHE_DRIVER'] = $_ENV['CACHE_DRIVER'] = $this->previousCacheDriver;
            putenv('CACHE_DRIVER=' . $this->previousCacheDriver);
        }
        parent::tearDown();
    }

    public function test_el_token_deja_de_servir_despues_del_logout()
    {
        $this->useFileCache();
        $headers = $this->authHeaders($this->admin());

        $this->getJson('/api/auth/me', $headers)->assertOk();
        $this->postJson('/api/auth/logout', [], $headers)->assertOk();

        // nueva instancia de la app: nada en memoria, solo la lista negra persistida
        $this->refreshApplication();
        $this->getJson('/api/auth/me', $headers)->assertStatus(401);
    }

    public function test_logout_sin_token_responde_401()
    {
        $this->postJson('/api/auth/logout')->assertStatus(401);
    }
}
