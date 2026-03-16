<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToCursosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('cursos', function(Blueprint $table)
		{
			$table->foreign('id_nivel', 'cursos_id_nivel_fkey')->references('id')->on('niveles')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('id_profesor', 'cursos_id_profesor_fkey')->references('id')->on('profesores')->onUpdate('CASCADE')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('cursos', function(Blueprint $table)
		{
			$table->dropForeign('cursos_id_nivel_fkey');
			$table->dropForeign('cursos_id_profesor_fkey');
		});
	}

}
