<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPagosProfesoresTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('pagos_profesores', function(Blueprint $table)
		{
			$table->foreign('id_profesor', 'pagos_profesores_id_profesor_fkey')->references('id')->on('profesores')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('pagos_profesores', function(Blueprint $table)
		{
			$table->dropForeign('pagos_profesores_id_profesor_fkey');
		});
	}

}
