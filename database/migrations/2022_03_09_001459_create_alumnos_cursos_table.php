<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlumnosCursosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('alumnos_cursos', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('id_alumno')->nullable();
			$table->date('fecha')->nullable();
			$table->smallInteger('anio_lectivo')->nullable();
			$table->decimal('importe')->nullable();
			$table->decimal('pagado')->nullable();
			$table->decimal('dto_hermano')->nullable();
			$table->boolean('estado')->nullable();
			$table->integer('id_curso')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('alumnos_cursos');
	}

}
