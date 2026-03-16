<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagosCuentasTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pagos_cuentas', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('id_tipo_egreso')->nullable();
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
		Schema::drop('pagos_cuentas');
	}

}
