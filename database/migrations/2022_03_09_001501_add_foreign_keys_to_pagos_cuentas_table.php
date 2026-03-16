<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPagosCuentasTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('pagos_cuentas', function(Blueprint $table)
		{
			$table->foreign('id_tipo_egreso', 'pagos_cuentas_id_tipo_egreso_fkey')->references('id')->on('tipos_egresos')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('pagos_cuentas', function(Blueprint $table)
		{
			$table->dropForeign('pagos_cuentas_id_tipo_egreso_fkey');
		});
	}

}
