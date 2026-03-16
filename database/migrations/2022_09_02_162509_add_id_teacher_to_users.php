<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdTeacherToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->unsignedInteger('id_profesor')->nullable();
            $table->foreign('id_profesor', 'users_id_profesor_fkey')->references('id')->on('profesores')->onUpdate('CASCADE')->onDelete('RESTRICT');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropForeign('users_id_profesor_fkey');
            $table->dropColumn('id_profesor');
        });
    }
}
