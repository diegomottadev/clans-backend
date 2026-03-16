<?php

namespace App\Http\Controllers\ClassSchedule;

use App\Http\Controllers\ApiController;
use App\Models\ClassSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClassScheduleController extends ApiController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        $classes = ClassSchedule::query()
            ->with(['course.level.languaje', 'teacher', 'classroom'])
            ->orderBy('fecha', 'DESC')
            ->orderBy('hora_inicio', 'ASC');

        $collections = $request->input('all', '') == 1
            ? $this->showList($classes->get())
            : $this->showAll($classes);

        return $collections;
    }

    public function store(Request $request)
    {
        $rules = [
            'course_id' => 'required|integer|exists:cursos,id',
            'teacher_id' => 'required|integer|exists:profesores,id',
            'classroom_id' => 'required|integer|exists:aulas,id',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
        ];

        $this->validate($request, $rules);

        // Validar superposición de aula
        $overlap = ClassSchedule::where('id_aula', $request->classroom_id)
            ->where('fecha', $request->date)
            ->where('hora_inicio', '<', $request->end_time)
            ->where('hora_fin', '>', $request->start_time)
            ->whereNull('deleted_at')
            ->exists();

        if ($overlap) {
            return $this->errorResponse('El aula ya está ocupada en ese horario.', 422);
        }

        $classSchedule = ClassSchedule::create([
            'id_curso' => $request->course_id,
            'id_profesor' => $request->teacher_id,
            'id_aula' => $request->classroom_id,
            'fecha' => $request->date,
            'hora_inicio' => $request->start_time,
            'hora_fin' => $request->end_time,
        ]);

        $classSchedule->load(['course.level.languaje', 'teacher', 'classroom']);

        return $this->showOne($classSchedule);
    }

    public function update(Request $request, ClassSchedule $classSchedule)
    {
        $rules = [
            'course_id' => 'required|integer|exists:cursos,id',
            'teacher_id' => 'required|integer|exists:profesores,id',
            'classroom_id' => 'required|integer|exists:aulas,id',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
        ];

        $this->validate($request, $rules);

        // Validar superposición excluyendo la clase actual
        $overlap = ClassSchedule::where('id_aula', $request->classroom_id)
            ->where('fecha', $request->date)
            ->where('hora_inicio', '<', $request->end_time)
            ->where('hora_fin', '>', $request->start_time)
            ->where('id', '!=', $classSchedule->id)
            ->whereNull('deleted_at')
            ->exists();

        if ($overlap) {
            return $this->errorResponse('El aula ya está ocupada en ese horario.', 422);
        }

        $classSchedule->id_curso = $request->course_id;
        $classSchedule->id_profesor = $request->teacher_id;
        $classSchedule->id_aula = $request->classroom_id;
        $classSchedule->fecha = $request->date;
        $classSchedule->hora_inicio = $request->start_time;
        $classSchedule->hora_fin = $request->end_time;
        $classSchedule->save();

        $classSchedule->load(['course.level.languaje', 'teacher', 'classroom']);

        return $this->showOne($classSchedule);
    }

    public function storeBulk(Request $request)
    {
        $rules = [
            'course_id' => 'required|integer|exists:cursos,id',
            'teacher_id' => 'required|integer|exists:profesores,id',
            'classroom_id' => 'required|integer|exists:aulas,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required',
            'end_time' => 'required',
            'days_of_week' => 'required|array|min:1',
            'days_of_week.*' => 'integer|between:0,6',
        ];

        $this->validate($request, $rules);

        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        $daysOfWeek = $request->days_of_week; // 0=domingo, 1=lunes, ..., 6=sábado

        $created = 0;
        $skipped = 0;
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            if (in_array($current->dayOfWeek, $daysOfWeek)) {
                $dateStr = $current->format('Y-m-d');

                // Verificar superposición de aula
                $overlap = ClassSchedule::where('id_aula', $request->classroom_id)
                    ->where('fecha', $dateStr)
                    ->where('hora_inicio', '<', $request->end_time)
                    ->where('hora_fin', '>', $request->start_time)
                    ->whereNull('deleted_at')
                    ->exists();

                if ($overlap) {
                    $skipped++;
                } else {
                    ClassSchedule::create([
                        'id_curso' => $request->course_id,
                        'id_profesor' => $request->teacher_id,
                        'id_aula' => $request->classroom_id,
                        'fecha' => $dateStr,
                        'hora_inicio' => $request->start_time,
                        'hora_fin' => $request->end_time,
                    ]);
                    $created++;
                }
            }
            $current->addDay();
        }

        return response()->json([
            'message' => "Se crearon {$created} clases." . ($skipped > 0 ? " {$skipped} omitidas por superposición de aula." : ''),
            'created' => $created,
            'skipped' => $skipped,
        ]);
    }

    public function destroy(ClassSchedule $classSchedule)
    {
        $classSchedule->delete();
        return $this->showOne($classSchedule);
    }
}
