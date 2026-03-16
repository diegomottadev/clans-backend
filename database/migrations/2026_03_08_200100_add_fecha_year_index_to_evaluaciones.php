<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFechaYearIndexToEvaluaciones extends Migration
{
    public function up()
    {
        // Índice funcional para WHERE EXTRACT(YEAR FROM fecha) = ? — usado al filtrar por año lectivo
        DB::statement('CREATE INDEX IF NOT EXISTS idx_evaluaciones_fecha_year ON evaluaciones (EXTRACT(YEAR FROM fecha))');
    }

    public function down()
    {
        DB::statement('DROP INDEX IF EXISTS idx_evaluaciones_fecha_year');
    }
}
