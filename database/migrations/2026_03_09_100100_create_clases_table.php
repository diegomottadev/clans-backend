<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClasesTable extends Migration
{
    public function up()
    {
        Schema::create('clases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_curso');
            $table->unsignedBigInteger('id_profesor');
            $table->unsignedBigInteger('id_aula');
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_curso')->references('id')->on('cursos')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('id_profesor')->references('id')->on('profesores')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('id_aula')->references('id')->on('aulas')->onUpdate('CASCADE')->onDelete('CASCADE');

            $table->index('fecha');
            $table->index('id_curso');
            $table->index('id_profesor');
            $table->index('id_aula');
            $table->index(['fecha', 'hora_inicio']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('clases');
    }
}
