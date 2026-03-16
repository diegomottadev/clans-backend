<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNivelesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('niveles', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('nombre', 200)->nullable();
			$table->integer('id_idioma')->nullable();
			$table->decimal('cuota', 12)->nullable();
			$table->integer('id_tipo_cursado')->nullable();
			$table->string('estado', 2)->nullable();
			$table->integer('mes_desde')->nullable();
			$table->integer('mes_hasta')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('niveles');
	}

}
