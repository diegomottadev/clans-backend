<?php

namespace App\Http\Controllers\Languaje;

use App\Http\Controllers\ApiController;
use App\Models\Course;
use App\Models\Languaje;
use App\Models\Level;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LanguajeCourseController extends ApiController
{

    /**
     *
     * @return void
     */
    public function __construct()
    {
  /*       $this->middleware(
            'auth:api'
        ); */
    }


    public function index(Request $request, Languaje $languaje)
    {
        Log::channel('single')->info('[LanguajeCourseController::index] Inicio', [
            'languaje_id' => $languaje->id,
            'query' => $request->query(),
            'schoolYear' => $request->schoolYear,
            'all' => $request->input('all'),
        ]);

        try {
            $schoolYear = null;
            if ($request->schoolYear && $request->schoolYear !== 'null') {
                $schoolYearId = (int) $request->schoolYear;
                $schoolYear = SchoolYear::find($schoolYearId);
                Log::channel('single')->info('[LanguajeCourseController::index] schoolYear por request', [
                    'schoolYearId' => $schoolYearId,
                    'found' => $schoolYear !== null,
                ]);
            }
            if ($schoolYear === null) {
                $schoolYear = cache()->remember('active_school_year', 3600, function () {
                    return SchoolYear::where('active', true)->first();
                });
                Log::channel('single')->info('[LanguajeCourseController::index] Ciclo activo desde cache', [
                    'found' => $schoolYear !== null,
                    'id' => $schoolYear ? $schoolYear->id : null,
                ]);
            }
            if ($schoolYear === null) {
                Log::channel('single')->warning('[LanguajeCourseController::index] No hay ciclo lectivo (ni por request ni activo)');
                return $this->errorResponse('No hay ciclo lectivo seleccionado ni activo', 422);
            }
            $schoolYearId = $schoolYear->id;

            $courses = Course::query()
                ->with(['teacher', 'students', 'schoolYear'])
                ->whereHas('level', function ($q) use ($languaje) {
                    $q->where('id_idioma', $languaje->id);
                })
                ->where('school_year_id', $schoolYearId)
                ->where('status', true)
                ->orderBy('nombre', 'DESC')
                ->get();

            Log::channel('single')->info('[LanguajeCourseController::index] Cursos encontrados', [
                'count' => $courses->count(),
                'schoolYearId' => $schoolYearId,
            ]);

            $collections = $request->input('all', '') == 1
                ? $this->showList($courses)
                : $this->showAll($courses);

            Log::channel('single')->info('[LanguajeCourseController::index] Respuesta enviada');
            return $collections;
        } catch (\Throwable $e) {
            Log::channel('single')->error('[LanguajeCourseController::index] Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

}
