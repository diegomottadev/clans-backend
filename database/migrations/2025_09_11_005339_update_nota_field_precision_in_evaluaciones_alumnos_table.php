<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateNotaFieldPrecisionInEvaluacionesAlumnosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('evaluaciones_alumnos', function (Blueprint $table) {
            $table->decimal('nota', 5, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('evaluaciones_alumnos', function (Blueprint $table) {
            $table->decimal('nota', 10)->nullable()->change();
        });
    }
}
