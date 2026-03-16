<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insertar configuración inicial de tope de interés
        DB::table('settings')->insert([
            'key' => 'interest_rate',
            'value' => '0',
            'description' => 'Tope de interés mensual por mora (%)',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
