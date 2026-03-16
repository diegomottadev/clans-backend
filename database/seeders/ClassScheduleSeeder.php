<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClassScheduleSeeder extends Seeder
{
    public function run()
    {
        $courseIds = DB::table('cursos')->whereNull('deleted_at')->pluck('id')->toArray();
        $teacherIds = DB::table('profesores')->whereNull('deleted_at')->pluck('id')->toArray();
        $classroomIds = DB::table('aulas')->whereNull('deleted_at')->pluck('id')->toArray();

        if (empty($courseIds) || empty($teacherIds) || empty($classroomIds)) {
            $this->command->warn('Faltan cursos, profesores o aulas. Ejecute ClassroomSeeder primero.');
            return;
        }

        $timeSlots = [
            ['08:00', '09:30'],
            ['09:30', '11:00'],
            ['11:00', '12:30'],
            ['14:00', '15:30'],
            ['15:30', '17:00'],
            ['17:00', '18:30'],
            ['18:30', '20:00'],
        ];

        // Combinaciones de días típicas
        $dayPatterns = [
            [1, 3],       // Lunes y Miércoles
            [2, 4],       // Martes y Jueves
            [1, 3, 5],    // Lunes, Miércoles y Viernes
            [2, 4],       // Martes y Jueves
            [5],           // Solo Viernes
            [1, 4],       // Lunes y Jueves
            [2, 5],       // Martes y Viernes
            [3, 5],       // Miércoles y Viernes
        ];

        // Rango: cuatrimestre desde hoy hasta 4 meses después
        $startDate = Carbon::today();
        $endDate = $startDate->copy()->addMonths(4);

        $now = now();
        $records = [];
        $usedSlots = []; // fecha-aula-horario para evitar doble booking

        // Crear asignaciones: cada curso tiene un profesor, aula, horario y días fijos
        $numAssignments = min(count($courseIds), 12); // hasta 12 asignaciones
        $usedCourses = [];

        for ($a = 0; $a < $numAssignments; $a++) {
            // Elegir curso sin repetir si es posible
            $courseId = null;
            foreach ($courseIds as $cid) {
                if (!in_array($cid, $usedCourses)) {
                    $courseId = $cid;
                    $usedCourses[] = $cid;
                    break;
                }
            }
            if (!$courseId) {
                $courseId = $courseIds[array_rand($courseIds)];
            }

            $teacherId = $teacherIds[array_rand($teacherIds)];
            $classroomId = $classroomIds[array_rand($classroomIds)];
            $slot = $timeSlots[array_rand($timeSlots)];
            $days = $dayPatterns[array_rand($dayPatterns)];

            // Generar clases para los días seleccionados en el rango
            $current = $startDate->copy();
            while ($current->lte($endDate)) {
                if (in_array($current->dayOfWeek, $days)) {
                    $dateStr = $current->format('Y-m-d');
                    $slotKey = $dateStr . '-' . $classroomId . '-' . $slot[0];

                    if (!isset($usedSlots[$slotKey])) {
                        $usedSlots[$slotKey] = true;
                        $records[] = [
                            'id_curso' => $courseId,
                            'id_profesor' => $teacherId,
                            'id_aula' => $classroomId,
                            'fecha' => $dateStr,
                            'hora_inicio' => $slot[0],
                            'hora_fin' => $slot[1],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
                $current->addDay();
            }
        }

        // Insertar en bloques
        foreach (array_chunk($records, 50) as $chunk) {
            DB::table('clases')->insert($chunk);
        }

        $this->command->info("Se crearon " . count($records) . " clases para {$numAssignments} asignaciones (cuatrimestre de 4 meses).");
    }
}
