<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlumnosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('alumnos', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('apellido', 200)->nullable();
			$table->string('nombre', 200)->nullable();
			$table->date('fecha_nac')->nullable();
			$table->string('dni', 40)->nullable();
			$table->string('domicilio', 200)->nullable();
			$table->string('telefono', 80)->nullable();
			$table->string('barrio', 200)->nullable();
			$table->text('observaciones')->nullable();
			$table->integer('id_tutor')->nullable();
			$table->string('escuela', 200)->nullable();
			$table->string('turno_escolar', 200)->nullable();
			$table->string('horario_ed_fisica', 200)->nullable();
			$table->char('estado', 2)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('alumnos');
	}

}
