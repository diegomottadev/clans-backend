<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfesoresTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('profesores', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('apellido', 200)->nullable();
			$table->string('nombre', 200)->nullable();
			$table->string('dni', 40)->nullable();
			$table->string('tel_part', 50)->nullable();
			$table->string('cel_part', 50)->nullable();
			$table->string('email', 230)->nullable();
			$table->string('domicilio', 200)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('profesores');
	}

}
