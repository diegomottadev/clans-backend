<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScoreToEvaluacionesAlumnosTable extends Migration
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
            $table->integer('listening')->nullable();
            $table->integer('vocabulary')->nullable();
            $table->integer('languajeFocus')->nullable();
            $table->integer('reading')->nullable();
            $table->integer('communication')->nullable();
            $table->integer('writing')->nullable();
            $table->integer('oralExam')->nullable();
            $table->boolean('assigned')->nullable();;
            $table->boolean('delivered')->nullable();;
            $table->boolean('pending')->nullable();;
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
            $table->dropColumn('listening');
            $table->dropColumn('vocabulary');
            $table->dropColumn('languajeFocus');
            $table->dropColumn('reading');
            $table->dropColumn('communication');
            $table->dropColumn('writing');
            $table->dropColumn('oralExam');
            $table->dropColumn('assigned');
            $table->dropColumn('delivered');
            $table->dropColumn('pending');
        });
    }
}
