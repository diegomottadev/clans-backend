<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAsistenciasTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('asistencias', function(Blueprint $table)
		{
			$table->integer('id_fechas_curso');
			$table->integer('id_alumno');
			$table->integer('id_tipos_asistencia')->nullable();
			$table->primary(['id_fechas_curso','id_alumno'], 'id_asistencia_pkey');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('asistencias');
	}

}
