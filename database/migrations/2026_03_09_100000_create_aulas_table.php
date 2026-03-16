<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAulasTable extends Migration
{
    public function up()
    {
        Schema::create('aulas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->integer('capacidad')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('aulas');
    }
}
