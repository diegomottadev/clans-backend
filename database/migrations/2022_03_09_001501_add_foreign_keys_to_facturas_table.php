<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToFacturasTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('facturas', function(Blueprint $table)
		{
			$table->foreign('id_alumno_curso', 'facturas_id_alumno_cursos')->references('id')->on('alumnos_cursos')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('id_alumno', 'facturas_id_aulmno_fkey')->references('id')->on('alumnos')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('facturas', function(Blueprint $table)
		{
			$table->dropForeign('facturas_id_alumno_cursos');
			$table->dropForeign('facturas_id_aulmno_fkey');
		});
	}

}
