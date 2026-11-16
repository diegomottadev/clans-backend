<?php

namespace Tests\Feature\Security\Owasp;

use App\Http\Middleware\isAdmin;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * H9 (A05): el middleware isAdmin, invocado sin usuario autenticado, responde 401 en vez de
 * fallar al leer ->role de null. Por HTTP no se ve (auth:api corre antes por prioridad), por eso
 * se prueba el middleware directamente.
 */
class H09IsAdminMiddlewareUnitTest extends TestCase
{
    private function run_(?User $user)
    {
        $request = Request::create('/api/users', 'GET');
        $request->setUserResolver(function () use ($user) { return $user; });
        return (new isAdmin())->handle($request, function () { return response('next', 200); });
    }

    public function test_sin_usuario_responde_401()
    {
        $this->assertSame(401, $this->run_(null)->getStatusCode());
    }

    public function test_docente_responde_403()
    {
        $teacher = User::where('role', 'user')->firstOrFail();
        $this->assertSame(403, $this->run_($teacher)->getStatusCode());
    }

    public function test_admin_pasa()
    {
        $this->assertSame('next', $this->run_($this->admin())->getContent());
    }
}
