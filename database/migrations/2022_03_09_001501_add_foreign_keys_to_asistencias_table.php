<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToAsistenciasTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('asistencias', function(Blueprint $table)
		{
			$table->foreign('id_alumno', 'asistencias_id_alumno_fkey')->references('id')->on('alumnos')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('id_fechas_curso', 'asistencias_id_fechas_curso_fkey')->references('id')->on('fechas_curso')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('id_tipos_asistencia', 'asistencias_id_tipos_asistencia_fkey')->references('id')->on('tipos_asistencias')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('asistencias', function(Blueprint $table)
		{
			$table->dropForeign('asistencias_id_alumno_fkey');
			$table->dropForeign('asistencias_id_fechas_curso_fkey');
			$table->dropForeign('asistencias_id_tipos_asistencia_fkey');
		});
	}

}
