<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\ApiController;
use App\Models\ClassSchedule;
use App\Models\Languaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CalendarController extends ApiController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Endpoint optimizado para el calendario.
     * GET /api/calendar?start_date=&end_date=&teacher_id=&course_id=&classroom_id=&language_id=
     */
    public function index(Request $request)
    {
        try {
            $startDate = $request->input('start_date', date('Y-m-01'));
            $endDate = $request->input('end_date', date('Y-m-t'));

            // Mapa de colores por idioma
            $languageColors = $this->getLanguageColors();

            // Query principal con eager loading
            $query = ClassSchedule::with(['course.level.languaje', 'teacher', 'classroom'])
                ->whereBetween('fecha', [$startDate, $endDate])
                ->whereNull('deleted_at');

            if ($request->teacher_id) {
                $query->where('id_profesor', (int) $request->teacher_id);
            }
            if ($request->course_id) {
                $query->where('id_curso', (int) $request->course_id);
            }
            if ($request->classroom_id) {
                $query->where('id_aula', (int) $request->classroom_id);
            }
            if ($request->language_id) {
                $langId = (int) $request->language_id;
                $query->whereHas('course.level', function ($q) use ($langId) {
                    $q->where('id_idioma', $langId);
                });
            }

            $classes = $query->orderBy('fecha')->orderBy('hora_inicio')->get();

            // Transformar a formato FullCalendar
            $events = $classes->map(function ($class) use ($languageColors) {
                $langId = optional(optional(optional($class->course)->level)->languaje)->id;
                $langName = optional(optional(optional($class->course)->level)->languaje)->nombre ?? '';
                $color = $languageColors[$langId] ?? '#607D8B';

                return [
                    'id' => $class->id,
                    'title' => optional($class->course)->nombre ?? '',
                    'start' => $class->fecha . 'T' . $class->hora_inicio,
                    'end' => $class->fecha . 'T' . $class->hora_fin,
                    'color' => $color,
                    'extendedProps' => [
                        'classId' => $class->id,
                        'courseId' => optional($class->course)->id,
                        'courseName' => optional($class->course)->nombre ?? '',
                        'languageName' => $langName,
                        'languageId' => $langId,
                        'teacherName' => $class->teacher
                            ? $class->teacher->apellido . ', ' . $class->teacher->nombre
                            : '',
                        'teacherId' => optional($class->teacher)->id,
                        'classroomName' => optional($class->classroom)->nombre ?? '',
                        'classroomId' => optional($class->classroom)->id,
                        'startTime' => substr($class->hora_inicio, 0, 5),
                        'endTime' => substr($class->hora_fin, 0, 5),
                        'date' => $class->fecha,
                    ],
                ];
            })->values();

            // Panel lateral: si hay filtros de fecha usa el rango filtrado, si no usa hoy
            $sidebarClasses = $events->values();

            // Contadores sobre el rango consultado
            $counters = [
                'classesToday' => $classes->count(),
                'activeTeachers' => $classes->pluck('id_profesor')->unique()->count(),
                'classroomsInUse' => $classes->pluck('id_aula')->unique()->count(),
                'activeCourses' => $classes->pluck('id_curso')->unique()->count(),
            ];

            return $this->successResponse([
                'events' => $events,
                'todayClasses' => $sidebarClasses,
                'counters' => $counters,
                'languageColors' => $languageColors,
            ], 200);
        } catch (\Exception $e) {
            Log::channel('single')->error('[CalendarController::index] Error', [
                'message' => $e->getMessage(),
            ]);
            return $this->errorResponse('Error al obtener datos del calendario: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Genera mapa de colores por idioma.
     */
    private function getLanguageColors()
    {
        $palette = ['#1976D2', '#388E3C', '#E64A19', '#7B1FA2', '#FBC02D', '#00838F', '#C62828', '#4E342E', '#1565C0', '#2E7D32'];
        $languages = Languaje::whereNull('deleted_at')->orderBy('id')->get();
        $colors = [];
        foreach ($languages as $i => $lang) {
            $colors[$lang->id] = $palette[$i % count($palette)];
        }
        return $colors;
    }
}
