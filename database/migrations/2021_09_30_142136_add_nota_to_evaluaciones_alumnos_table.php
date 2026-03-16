<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotaToEvaluacionesAlumnosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('evaluaciones_alumnos', function (Blueprint $table) {
            //
            $table->string('concept', 20)->nullable();

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
            //
            $table->dropColumn('concept');

        });
    }
}
