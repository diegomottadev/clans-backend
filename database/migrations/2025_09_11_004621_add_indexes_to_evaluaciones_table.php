<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToEvaluacionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('evaluaciones', function (Blueprint $table) {
            $table->index('id_curso');
            $table->index('id_tipo_evaluacion');
            $table->index('fecha');
            $table->index(['id_curso', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('evaluaciones', function (Blueprint $table) {
            $table->dropIndex(['id_curso']);
            $table->dropIndex(['id_tipo_evaluacion']);
            $table->dropIndex(['fecha']);
            $table->dropIndex(['id_curso', 'fecha']);
        });
    }
}
