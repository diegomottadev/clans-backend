<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;

class isUser
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('No autenticado', 401);
        }

        if ($user->role === 'admin' || $user->role === 'user') {
            return $next($request);
        }

        return $this->errorResponse('No tiene permisos para acceder al contenido de la página', 403);
    }
}
