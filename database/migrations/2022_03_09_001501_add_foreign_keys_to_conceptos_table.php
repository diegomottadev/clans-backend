<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToConceptosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('conceptos', function(Blueprint $table)
		{
			$table->foreign('id_idioma', 'conceptos_id_idioma_fkey')->references('id')->on('idiomas')->onUpdate('CASCADE')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('conceptos', function(Blueprint $table)
		{
			$table->dropForeign('conceptos_id_idioma_fkey');
		});
	}

}
