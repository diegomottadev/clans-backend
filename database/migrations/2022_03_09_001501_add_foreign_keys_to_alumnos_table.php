<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToAlumnosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('alumnos', function(Blueprint $table)
		{
			$table->foreign('id_tutor', 'alumnos_id_tutor_fkey')->references('id')->on('tutores')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('alumnos', function(Blueprint $table)
		{
			$table->dropForeign('alumnos_id_tutor_fkey');
		});
	}

}
