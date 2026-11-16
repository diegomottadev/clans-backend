<?php

/*
|--------------------------------------------------------------------------
| Mi cuenta (incluido desde routes/api.php: prefijo /api y grupo 'api')
|--------------------------------------------------------------------------
| El usuario logueado (admin o docente) gestiona SOLO su propio perfil:
| ninguna ruta recibe un id de usuario.
*/

use App\Http\Controllers\Account\MyAccountController;
use Illuminate\Support\Facades\Route;

Route::get('me', [MyAccountController::class, 'show'])->middleware(['isUser']);
Route::put('me', [MyAccountController::class, 'update'])->middleware(['isUser']);
// Subida: además del límite general 'api', máx. 10 por minuto por usuario
Route::post('me/avatar', [MyAccountController::class, 'storeAvatar'])->middleware(['isUser', 'throttle:10,1']);
Route::get('me/avatar', [MyAccountController::class, 'showAvatar'])->middleware(['isUser']);
Route::delete('me/avatar', [MyAccountController::class, 'destroyAvatar'])->middleware(['isUser']);
