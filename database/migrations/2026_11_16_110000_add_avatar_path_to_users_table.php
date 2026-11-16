<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Foto de perfil ("Mi cuenta"): ruta relativa dentro del disco privado 'local'
 * (storage/app/avatars/<uuid>.jpg). Nunca se guarda el nombre original del archivo.
 */
class AddAvatarPathToUsersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('users', 'avatar_path')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('avatar_path', 100)->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'avatar_path')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('avatar_path');
            });
        }
    }
}
