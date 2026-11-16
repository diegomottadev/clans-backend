<?php

namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;

/**
 * Datos de "Mi cuenta" del usuario logueado. No expone password ni la ruta interna
 * del avatar: solo si tiene foto y una versión para invalidar caché en el cliente.
 */
class AccountTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        $teacher = $user->teacher;

        return [
            'id' => (int) $user->id,
            'name' => (string) $user->name,
            'email' => (string) $user->email,
            'role' => (string) $user->role,
            'has_avatar' => !empty($user->avatar_path),
            'avatar_version' => self::avatarVersion($user),
            'teacher' => $teacher ? [
                'id' => (int) $teacher->id,
                'firstName' => (string) $teacher->nombre,
                'lastName' => (string) $teacher->apellido,
                'dni' => (string) $teacher->dni,
                'telphone' => (string) $teacher->tel_part,
                'celphone' => (string) $teacher->cel_part,
                'email' => (string) $teacher->email,
                'address' => (string) $teacher->domicilio,
            ] : null,
        ];
    }

    /** Fragmento aleatorio del nombre del archivo: cambia en cada subida (cache-busting). */
    public static function avatarVersion(User $user)
    {
        return empty($user->avatar_path) ? null : substr(basename($user->avatar_path), 0, 8);
    }

    public static function originalAttributes($index)
    {
        $attributes = [
            'id' => 'id',
            'name' => 'name',
            'email' => 'email',
            'role' => 'role',
        ];
        return $attributes[$index] ?? null;
    }
}
