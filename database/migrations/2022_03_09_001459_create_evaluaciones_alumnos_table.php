<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluacionesAlumnosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('evaluaciones_alumnos', function(Blueprint $table)
		{
			$table->integer('id_evaluacion');
			$table->integer('id_alumno');
			$table->decimal('nota', 10)->nullable();
			$table->string('observaciones', 240)->nullable();
			$table->integer('id_concepto')->nullable();
			$table->primary(['id_evaluacion','id_alumno'], 'evaluaciones_alumno_pkey');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('evaluaciones_alumnos');
	}

}
