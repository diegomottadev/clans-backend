<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddObservacionToPagosProfesoresTable extends Migration
{
    public function up()
    {
        Schema::table('pagos_profesores', function (Blueprint $table) {
            $table->string('observacion', 500)->nullable();
        });
    }

    public function down()
    {
        Schema::table('pagos_profesores', function (Blueprint $table) {
            $table->dropColumn('observacion');
        });
    }
}
