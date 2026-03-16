<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddPerformanceIndexesToAssistanceTables extends Migration
{
    public function up()
    {
        // asistencias.id_fechas_curso — eager load studentsAssisted: 72k filas sin índice → full scan por página
        DB::statement('CREATE INDEX IF NOT EXISTS idx_asistencias_id_fechas_curso ON asistencias (id_fechas_curso)');

        // fechas_curso.id_curso — filtrado por curso y año lectivo
        DB::statement('CREATE INDEX IF NOT EXISTS idx_fechas_curso_id_curso ON fechas_curso (id_curso)');

        // fechas_curso.deleted_at — soft deletes en todas las queries
        DB::statement('CREATE INDEX IF NOT EXISTS idx_fechas_curso_deleted_at ON fechas_curso (deleted_at)');

        // cursos.school_year_id — whereHas para filtrar por año lectivo
        DB::statement('CREATE INDEX IF NOT EXISTS idx_cursos_school_year_id ON cursos (school_year_id)');

        // evaluaciones — índices equivalentes
        DB::statement('CREATE INDEX IF NOT EXISTS idx_evaluaciones_id_curso ON evaluaciones (id_curso)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_evaluaciones_deleted_at ON evaluaciones (deleted_at)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_evaluaciones_alumnos_id_evaluacion ON evaluaciones_alumnos (id_evaluacion)');
    }

    public function down()
    {
        DB::statement('DROP INDEX IF EXISTS idx_asistencias_id_fechas_curso');
        DB::statement('DROP INDEX IF EXISTS idx_fechas_curso_id_curso');
        DB::statement('DROP INDEX IF EXISTS idx_fechas_curso_deleted_at');
        DB::statement('DROP INDEX IF EXISTS idx_cursos_school_year_id');
        DB::statement('DROP INDEX IF EXISTS idx_evaluaciones_id_curso');
        DB::statement('DROP INDEX IF EXISTS idx_evaluaciones_deleted_at');
        DB::statement('DROP INDEX IF EXISTS idx_evaluaciones_alumnos_id_evaluacion');
    }
}
