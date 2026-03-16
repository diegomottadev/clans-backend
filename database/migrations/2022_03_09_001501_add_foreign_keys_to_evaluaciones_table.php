<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToEvaluacionesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('evaluaciones', function(Blueprint $table)
		{
			$table->foreign('id_curso', 'evaluaciones_id_curso_fkey')->references('id')->on('cursos')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('id_tipo_evaluacion', 'evaluaciones_id_tipo_evaluacion_fkey')->references('id')->on('tipo_evaluacion')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('evaluaciones', function(Blueprint $table)
		{
			$table->dropForeign('evaluaciones_id_curso_fkey');
			$table->dropForeign('evaluaciones_id_tipo_evaluacion_fkey');
		});
	}

}
