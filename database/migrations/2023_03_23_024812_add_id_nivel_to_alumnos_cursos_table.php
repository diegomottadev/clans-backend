<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdNivelToAlumnosCursosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alumnos_cursos', function (Blueprint $table) {
            //
            $table->unsignedInteger('id_nivel')->nullable();
            $table->foreign('id_nivel', 'alumnos_cursos_id_nivel_fkey')->references('id')->on('niveles')->onUpdate('CASCADE')->onDelete('RESTRICT');
            $table->unsignedInteger('id_idioma')->nullable();
            $table->foreign('id_idioma', 'alumnos_cursos_id_idioma_fkey')->references('id')->on('idiomas')->onUpdate('CASCADE')->onDelete('RESTRICT');
        
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('alumnos_cursos', function (Blueprint $table) {
            //
            $table->dropForeign('alumnos_cursos_id_nivel_fkey');
            $table->dropColumn('id_nivel');
            $table->dropForeign('alumnos_cursos_id_idioma_fkey');
            $table->dropColumn('id_idioma');
        });
    }
}
