<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFechaYearIndexToFechasCurso extends Migration
{
    public function up()
    {
        // Índice funcional para WHERE EXTRACT(YEAR FROM fecha) = ? — usado al filtrar por año lectivo
        DB::statement('CREATE INDEX IF NOT EXISTS idx_fechas_curso_fecha_year ON fechas_curso (EXTRACT(YEAR FROM fecha))');
    }

    public function down()
    {
        DB::statement('DROP INDEX IF EXISTS idx_fechas_curso_fecha_year');
    }
}
