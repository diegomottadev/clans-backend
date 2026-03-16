<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacturasTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('facturas', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('id_alumno')->nullable();
			$table->integer('id_alumno_curso')->nullable();
			$table->date('fecha_emision')->nullable();
			$table->smallInteger('mes')->nullable();
			$table->date('fecha_vto')->nullable();
			$table->decimal('cuota')->nullable();
			$table->decimal('dto_pago_termino')->nullable();
			$table->decimal('dto_hermano')->nullable();
			$table->decimal('mora')->nullable();
			$table->decimal('total')->nullable();
			$table->boolean('estado')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('facturas');
	}

}
