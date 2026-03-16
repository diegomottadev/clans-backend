<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluacionesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('evaluaciones', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('nombre', 240)->nullable();
			$table->date('fecha')->nullable();
			$table->integer('id_tipo_evaluacion')->nullable();
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
		Schema::drop('evaluaciones');
	}

}
