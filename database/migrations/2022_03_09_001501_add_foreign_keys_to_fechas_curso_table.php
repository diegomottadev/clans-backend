<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToFechasCursoTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('fechas_curso', function(Blueprint $table)
		{
			$table->foreign('id_curso', 'fechas_curso_id_curso_fkey')->references('id')->on('cursos')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('fechas_curso', function(Blueprint $table)
		{
			$table->dropForeign('fechas_curso_id_curso_fkey');
		});
	}

}
