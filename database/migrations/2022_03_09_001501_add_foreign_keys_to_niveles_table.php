<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToNivelesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('niveles', function(Blueprint $table)
		{
			$table->foreign('id_idioma', 'nivel_id_idioma_fkey')->references('id')->on('idiomas')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('id_tipo_cursado', 'niveles_id_tipo_cursado_fkey')->references('id')->on('tipos_cursado')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('niveles', function(Blueprint $table)
		{
			$table->dropForeign('nivel_id_idioma_fkey');
			$table->dropForeign('niveles_id_tipo_cursado_fkey');
		});
	}

}
