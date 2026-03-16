<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DebtorTestSeeder extends Seeder
{
    public function run()
    {
        $schoolYear = DB::table('school_years')->where('active', true)->first();
        if (!$schoolYear) {
            $this->command->warn('No hay ciclo lectivo activo.');
            return;
        }

        $enrollments = DB::table('alumnos_cursos')
            ->join('alumnos', 'alumnos.id', '=', 'alumnos_cursos.id_alumno')
            ->join('cursos', 'cursos.id', '=', 'alumnos_cursos.id_curso')
            ->where('alumnos.activo', true)
            ->where('cursos.school_year_id', $schoolYear->id)
            ->whereNull('alumnos.deleted_at')
            ->whereNull('cursos.deleted_at')
            ->select('alumnos_cursos.id as enrollment_id', 'alumnos_cursos.id_alumno', 'alumnos_cursos.id_curso')
            ->limit(20)
            ->get();

        if ($enrollments->isEmpty()) {
            $this->command->warn('No hay inscripciones activas para generar facturas.');
            return;
        }

        $currentYear = now()->year;
        $now = now();
        $created = 0;
        $deleted = 0;

        // Limpiar facturas existentes de este año para estas inscripciones
        $enrollmentIds = $enrollments->pluck('enrollment_id')->toArray();
        $deleted = DB::table('facturas')
            ->whereIn('id_alumno_curso', $enrollmentIds)
            ->whereYear('fecha_emision', $currentYear)
            ->delete();

        $this->command->info("Se eliminaron {$deleted} facturas existentes.");

        foreach ($enrollments as $i => $enrollment) {
            $course = DB::table('cursos')->where('id', $enrollment->id_curso)->first();
            $level = $course ? DB::table('niveles')->where('id', $course->id_nivel)->first() : null;
            $cuota = $level ? (float)$level->cuota : 5000;

            // Poner fecha de inscripción en enero para que deban desde mes 1
            DB::table('alumnos_cursos')
                ->where('id', $enrollment->enrollment_id)
                ->update(['fecha' => Carbon::create($currentYear, 1, 15)->format('Y-m-d')]);

            if ($i < 12) {
                // MOROSOS: no tienen facturas de enero ni febrero ni marzo
                // maxMonthDue actual (9/03) = 2 → deberán enero y febrero
                $this->command->line("#{$enrollment->enrollment_id} alumno {$enrollment->id_alumno}: MOROSO - debe enero y febrero");
            } else {
                // AL DÍA: crear facturas de enero, febrero y marzo
                for ($m = 1; $m <= 3; $m++) {
                    DB::table('facturas')->insert([
                        'id_alumno' => $enrollment->id_alumno,
                        'id_alumno_curso' => $enrollment->enrollment_id,
                        'fecha_emision' => Carbon::create($currentYear, $m, rand(1, 9))->format('Y-m-d'),
                        'mes' => $m,
                        'cuota' => $cuota,
                        'fecha_vto' => Carbon::create($currentYear, $m, 10)->format('Y-m-d'),
                        'dto_pago_termino' => 0,
                        'dto_hermano' => 0,
                        'mora' => 0,
                        'total' => $cuota,
                        'estado' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $created++;
                }
                $this->command->line("#{$enrollment->enrollment_id} alumno {$enrollment->id_alumno}: AL DÍA - facturas ene/feb/mar creadas");
            }
        }

        $this->command->info("Resumen: {$created} facturas creadas. 12 morosos deben enero y febrero.");
    }
}
