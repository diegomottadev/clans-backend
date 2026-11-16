<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    //
    use ApiResponse;

    /**
     * SecDevOps (OWASP A01 / API1 BOLA): un docente (role=user) solo puede operar
     * sobre cursos que tiene asignados (cursos.id_profesor). El admin, sobre todos.
     */
    protected function canAccessCourse($courseId): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        if ($user->role === 'admin') {
            return true;
        }
        if ($user->role !== 'user' || empty($user->id_profesor) || empty($courseId)) {
            return false;
        }
        return Course::where('id', (int) $courseId)
            ->where('id_profesor', $user->id_profesor)
            ->exists();
    }

    protected function forbiddenCourse()
    {
        return $this->errorResponse('No tiene permisos sobre este curso', 403);
    }
}
