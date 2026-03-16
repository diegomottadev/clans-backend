<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentFixController extends ApiController
{
    /**
     * List all students where nombre and apellido are swapped.
     * A student is "swapped" when: nombreCompleto = CONCAT(apellido, ' ', nombre)
     *
     * GET /api/students/swapped-names
     */
    public function swappedNames(\Illuminate\Http\Request $request)
    {
        try {
            $filter = $request->input('filter', 'swapped');

            $query = DB::table('alumnos')
                ->whereNull('deleted_at')
                ->select(
                    'id', 'nombre', 'apellido', 'nombreCompleto',
                    DB::raw('CASE WHEN "nombreCompleto" = CONCAT(apellido, chr(32), nombre) THEN true ELSE false END as swapped')
                );

            if ($filter === 'swapped') {
                $query->whereRaw('"nombreCompleto" = CONCAT(apellido, chr(32), nombre)');
            } elseif ($filter === 'correct') {
                $query->whereRaw('"nombreCompleto" <> CONCAT(apellido, chr(32), nombre)');
            }

            $query->orderBy('apellido')->orderBy('nombre');
            $students = $query->get();

            $swappedTotal = DB::table('alumnos')
                ->whereNull('deleted_at')
                ->whereRaw('"nombreCompleto" = CONCAT(apellido, chr(32), nombre)')
                ->count();

            $total = DB::table('alumnos')->whereNull('deleted_at')->count();

            return $this->successResponse([
                'data' => $students,
                'count' => $students->count(),
                'swapped_count' => $swappedTotal,
                'total' => $total,
            ], 200);
        } catch (\Exception $e) {
            Log::channel('single')->error('[StudentFixController::swappedNames] Error', [
                'message' => $e->getMessage(),
            ]);
            return $this->errorResponse('Error al obtener alumnos con nombres invertidos: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Swap nombre and apellido for all students where they are inverted.
     * Condition: nombreCompleto = CONCAT(apellido, ' ', nombre) AND deleted_at IS NULL
     *
     * POST /api/students/fix-swapped-names
     */
    public function fixSwappedNames(\Illuminate\Http\Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return $this->errorResponse('Debe seleccionar al menos un alumno.', 422);
            }

            $affected = DB::table('alumnos')
                ->whereIn('id', $ids)
                ->whereNull('deleted_at')
                ->update([
                    'nombre' => DB::raw('apellido'),
                    'apellido' => DB::raw('nombre'),
                    'nombreCompleto' => DB::raw('CONCAT(apellido, chr(32), nombre)'),
                ]);

            Log::channel('single')->info('[StudentFixController::fixSwappedNames] Fixed swapped names', [
                'count' => $affected,
            ]);

            return $this->successResponse([
                'message' => 'Nombres corregidos exitosamente',
                'count' => $affected,
            ], 200);
        } catch (\Exception $e) {
            Log::channel('single')->error('[StudentFixController::fixSwappedNames] Error', [
                'message' => $e->getMessage(),
            ]);
            return $this->errorResponse('Error al corregir nombres invertidos: ' . $e->getMessage(), 500);
        }
    }
}
