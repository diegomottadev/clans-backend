<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToEvaluacionesAlumnosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('evaluaciones_alumnos', function(Blueprint $table)
		{
			$table->foreign('id_alumno', 'evaluaciones_alumnos_id_alumno_fkey')->references('id')->on('alumnos')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('id_concepto', 'evaluaciones_alumnos_id_concepto_fkey')->references('id')->on('conceptos')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('id_evaluacion', 'evaluaciones_alumnos_id_evaluacion_fkey')->references('id')->on('evaluaciones')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('evaluaciones_alumnos', function(Blueprint $table)
		{
			$table->dropForeign('evaluaciones_alumnos_id_alumno_fkey');
			$table->dropForeign('evaluaciones_alumnos_id_concepto_fkey');
			$table->dropForeign('evaluaciones_alumnos_id_evaluacion_fkey');
		});
	}

}
