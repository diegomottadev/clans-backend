<?php

use Illuminate\Database\Migrations\Migration;

class EnableUnaccentExtension extends Migration
{
    public function up()
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS unaccent');
    }

    public function down()
    {
        // No se elimina unaccent porque puede estar siendo usada por otros objetos de la DB
    }
}
