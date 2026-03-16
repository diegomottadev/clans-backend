<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagosProfesoresTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pagos_profesores', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('id_profesor')->nullable();
			$table->date('fecha')->nullable();
			$table->decimal('monto')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('pagos_profesores');
	}

}
