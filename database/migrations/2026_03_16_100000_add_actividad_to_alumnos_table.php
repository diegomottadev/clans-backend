<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActividadToAlumnosTable extends Migration
{
    public function up()
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->string('actividad', 300)->nullable()->after('horario_ed_fisica');
        });
    }

    public function down()
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropColumn('actividad');
        });
    }
}
