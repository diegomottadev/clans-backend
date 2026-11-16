<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\ApiController;
use App\Services\AvatarImage;
use App\Transformers\AccountTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * "Mi cuenta": el usuario autenticado gestiona SOLO su propio perfil.
 * No recibe ids por URL: siempre opera sobre auth()->user() (sin BOLA/IDOR posible).
 */
class MyAccountController extends ApiController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function show(Request $request)
    {
        return $this->account($request->user());
    }

    /**
     * Solo se leen campos permitidos (allow-list): name y datos del docente asociado.
     * role, id_profesor y teacher.id se ignoran. El email de ingreso es inmutable:
     * si llega distinto al actual, 422 y no se aplica nada.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $rules = [
            'name' => 'required|string|max:60',
            'email' => ['sometimes', Rule::in([$user->email])],
            'teacher' => 'sometimes|nullable|array',
            'teacher.firstName' => 'nullable|string|max:200',
            'teacher.lastName' => 'nullable|string|max:200',
            'teacher.dni' => 'nullable|string|max:40',
            'teacher.telphone' => 'nullable|string|max:50',
            'teacher.celphone' => 'nullable|string|max:50',
            'teacher.email' => 'nullable|email|max:230',
            'teacher.address' => 'nullable|string|max:200',
            // Cambio de contraseña opcional: exige la actual (ASVS V2.1.6) y mínimo 8 (V2.1.1)
            'password' => 'nullable|string|min:8|max:72|confirmed',
            'current_password' => [
                'nullable',
                'required_with:password',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value !== null && !Hash::check((string) $value, $user->password)) {
                        $fail('La contraseña actual es incorrecta.');
                    }
                },
            ],
        ];
        $messages = [
            'email.in' => 'El email de ingreso no se puede modificar.',
        ];
        $this->validate($request, $rules, $messages);

        DB::transaction(function () use ($request, $user) {
            $user->name = $request->input('name');
            if ($request->filled('password')) {
                $user->password = bcrypt($request->input('password'));
            }
            $user->save();

            $teacher = $user->teacher;
            if ($teacher && is_array($request->input('teacher'))) {
                $map = [
                    'firstName' => 'nombre',
                    'lastName' => 'apellido',
                    'dni' => 'dni',
                    'telphone' => 'tel_part',
                    'celphone' => 'cel_part',
                    'email' => 'email',
                    'address' => 'domicilio',
                ];
                foreach ($map as $field => $column) {
                    if ($request->has('teacher.' . $field)) {
                        $teacher->{$column} = $request->input('teacher.' . $field);
                    }
                }
                $teacher->nombreCompleto = trim($teacher->nombre . ' ' . $teacher->apellido);
                $teacher->save();
            }
        });

        $this->forgetUserCache($user);
        return $this->account($user->fresh());
    }

    /**
     * Sube/reemplaza la foto. Validación en servidor (no se confía en extensión ni Content-Type):
     * tamaño <= 2 MB, tipo real por contenido (finfo + getimagesize) JPEG/PNG, dimensiones 64..4000.
     * GD no tiene soporte WebP en este PHP, por eso no se acepta WebP.
     */
    public function storeAvatar(Request $request, AvatarImage $images)
    {
        $rules = [
            'avatar' => 'required|file|max:2048|mimetypes:image/jpeg,image/png,image/gif,image/webp,image/bmp,image/x-ms-bmp'
                . '|dimensions:min_width=64,min_height=64,max_width=4000,max_height=4000',
        ];
        $messages = [
            'avatar.required' => 'Seleccione una imagen.',
            'avatar.max' => 'La imagen no puede superar los 2 MB.',
            'avatar.mimetypes' => 'La imagen debe ser JPG, PNG, GIF, WebP o BMP.',
            'avatar.dimensions' => 'La imagen debe medir entre 64x64 y 4000x4000 píxeles.',
            'avatar.uploaded' => 'No se pudo subir la imagen (máximo 2 MB).',
        ];
        $this->validate($request, $rules, $messages);

        $path = $images->store($request->file('avatar'));
        if ($path === null) {
            return response()->json(['avatar' => ['La imagen está dañada o no es válida.']], 422);
        }

        $user = $request->user();
        $old = $user->avatar_path;
        $user->avatar_path = $path;
        $user->save();
        $images->delete($old);

        $this->forgetUserCache($user);
        return $this->account($user);
    }

    /**
     * Sirve SOLO la foto del usuario autenticado (disco privado, no accesible por URL pública).
     * Siempre image/jpeg (lo generamos nosotros), nosniff y caché privada.
     */
    public function showAvatar(Request $request)
    {
        $path = $request->user()->avatar_path;
        $disk = Storage::disk(AvatarImage::DISK);
        if (empty($path) || !$disk->exists($path)) {
            return $this->errorResponse('Sin foto de perfil', 404);
        }

        return response()->stream(function () use ($disk, $path) {
            echo $disk->get($path);
        }, 200, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'inline; filename="avatar.jpg"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    public function destroyAvatar(Request $request, AvatarImage $images)
    {
        $user = $request->user();
        $old = $user->avatar_path;
        $user->avatar_path = null;
        $user->save();
        $images->delete($old);

        $this->forgetUserCache($user);
        return $this->account($user);
    }

    /** AuthController::me cachea el usuario 30 min: invalidar tras cualquier cambio. */
    private function forgetUserCache($user)
    {
        cache()->forget('user_' . $user->id);
    }

    private function account($user)
    {
        $user->load('teacher');
        return $this->successResponse($this->transformData($user, AccountTransformer::class), 200);
    }
}
