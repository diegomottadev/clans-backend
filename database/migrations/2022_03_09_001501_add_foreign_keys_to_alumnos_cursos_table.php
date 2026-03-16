<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToAlumnosCursosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('alumnos_cursos', function(Blueprint $table)
		{
			$table->foreign('id_curso', 'alumnos_cursos_curso_fkey')->references('id')->on('cursos')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('id_alumno', 'alumnos_cursos_id_alumno_fkey')->references('id')->on('alumnos')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('alumnos_cursos', function(Blueprint $table)
		{
			$table->dropForeign('alumnos_cursos_curso_fkey');
			$table->dropForeign('alumnos_cursos_id_alumno_fkey');
		});
	}

}
