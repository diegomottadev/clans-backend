<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTutoresTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tutores', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('apellido', 200)->nullable();
			$table->string('nombre', 200)->nullable();
			$table->string('telefono', 30)->nullable();
			$table->integer('dni')->nullable();
			$table->string('domicilio', 200)->nullable();
			$table->string('telefono_urgencias', 30)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tutores');
	}

}
