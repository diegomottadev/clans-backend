<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeScoreFieldsToDecimalInEvaluacionesAlumnosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('evaluaciones_alumnos', function (Blueprint $table) {
            $table->decimal('listening', 5, 2)->nullable()->change();
            $table->decimal('vocabulary', 5, 2)->nullable()->change();
            $table->decimal('languajeFocus', 5, 2)->nullable()->change();
            $table->decimal('reading', 5, 2)->nullable()->change();
            $table->decimal('communication', 5, 2)->nullable()->change();
            $table->decimal('writing', 5, 2)->nullable()->change();
            $table->decimal('oralExam', 5, 2)->nullable()->change();
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
            $table->integer('listening')->nullable()->change();
            $table->integer('vocabulary')->nullable()->change();
            $table->integer('languajeFocus')->nullable()->change();
            $table->integer('reading')->nullable()->change();
            $table->integer('communication')->nullable()->change();
            $table->integer('writing')->nullable()->change();
            $table->integer('oralExam')->nullable()->change();
        });
    }
}
