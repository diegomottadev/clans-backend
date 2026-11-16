<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Oráculo foto de perfil (OWASP File Upload Cheat Sheet / ASVS V12):
 *  - POST /api/me/avatar (campo "avatar"): solo JPEG/PNG por contenido real, <= 2 MB,
 *    entre 64x64 y 4000x4000; se re-codifica con GD a JPEG 256x256 (sin EXIF ni payloads),
 *    nombre aleatorio en el disco PRIVADO 'local' (avatars/), y se borra la anterior.
 *  - GET /api/me/avatar la sirve autenticada; DELETE /api/me/avatar la quita.
 */
class MyAccountAvatarTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function user(): User
    {
        $user = User::where('role', 'user')->whereNotNull('id_profesor')->firstOrFail();
        $user->avatar_path = null;
        $user->save();
        return $user;
    }

    private function upload(User $user, $file)
    {
        return $this->post('/api/me/avatar', ['avatar' => $file], $this->authHeaders($user));
    }

    public function test_subir_avatar_requiere_autenticacion()
    {
        $this->post('/api/me/avatar', ['avatar' => UploadedFile::fake()->image('a.jpg', 300, 300)], ['Accept' => 'application/json'])
            ->assertStatus(401);
    }

    public function test_sube_jpeg_valido_y_lo_guarda_recodificado_256_con_nombre_aleatorio()
    {
        $user = $this->user();

        $res = $this->upload($user, UploadedFile::fake()->image('mi foto original.jpg', 800, 600));

        $res->assertOk()->assertJsonPath('data.has_avatar', true);
        $path = $user->fresh()->avatar_path;
        $this->assertNotNull($path);
        $this->assertMatchesRegularExpression('#^avatars/[0-9a-f-]{36}\.jpg$#', $path);
        $this->assertStringNotContainsString('original', $path);
        Storage::disk('local')->assertExists($path);

        $info = getimagesizefromstring(Storage::disk('local')->get($path));
        $this->assertSame([256, 256, IMAGETYPE_JPEG], [$info[0], $info[1], $info[2]]);
        $this->assertNotNull($res->json('data.avatar_version'));
    }

    public function test_acepta_png_y_lo_convierte_a_jpeg()
    {
        $user = $this->user();

        $this->upload($user, UploadedFile::fake()->image('foto.png', 300, 500))->assertOk();

        $path = $user->fresh()->avatar_path;
        $this->assertStringEndsWith('.jpg', $path);
        $info = getimagesizefromstring(Storage::disk('local')->get($path));
        $this->assertSame(IMAGETYPE_JPEG, $info[2]);
    }

    public function test_rechaza_archivo_con_extension_jpg_pero_contenido_php()
    {
        $user = $this->user();
        $fake = UploadedFile::fake()->createWithContent('foto.jpg', "<?php system(\$_GET['c']); ?>");

        $this->upload($user, $fake)->assertStatus(422)->assertJsonStructure(['avatar']);
        $this->assertNull($user->fresh()->avatar_path);
        $this->assertSame([], Storage::disk('local')->allFiles('avatars'));
    }

    public function test_elimina_payload_oculto_anexado_a_un_jpeg_valido()
    {
        $user = $this->user();
        $jpeg = UploadedFile::fake()->image('poly.jpg', 300, 300);
        $content = file_get_contents($jpeg->getPathname()) . "<?php echo 'pwned'; ?>";
        $poly = UploadedFile::fake()->createWithContent('poly.jpg', $content);

        $this->upload($user, $poly)->assertOk();

        $stored = Storage::disk('local')->get($user->fresh()->avatar_path);
        $this->assertStringNotContainsString('<?php', $stored);
    }

    public function test_rechaza_tipo_no_permitido_gif()
    {
        $user = $this->user();

        $this->upload($user, UploadedFile::fake()->image('anim.gif', 300, 300))
            ->assertStatus(422)->assertJsonStructure(['avatar']);
    }

    public function test_rechaza_archivo_mayor_a_2mb()
    {
        $user = $this->user();
        $big = UploadedFile::fake()->image('grande.jpg', 300, 300)->size(2049);

        $this->upload($user, $big)->assertStatus(422)->assertJsonStructure(['avatar']);
    }

    public function test_rechaza_dimensiones_menores_al_minimo()
    {
        $user = $this->user();

        $this->upload($user, UploadedFile::fake()->image('mini.jpg', 40, 40))
            ->assertStatus(422)->assertJsonStructure(['avatar']);
    }

    public function test_rechaza_dimensiones_mayores_al_maximo()
    {
        $user = $this->user();

        $this->upload($user, UploadedFile::fake()->image('enorme.png', 4100, 10))
            ->assertStatus(422)->assertJsonStructure(['avatar']);
    }

    public function test_reemplazar_avatar_borra_el_archivo_anterior()
    {
        $user = $this->user();
        $this->upload($user, UploadedFile::fake()->image('a.jpg', 300, 300))->assertOk();
        $first = $user->fresh()->avatar_path;

        $this->upload($user, UploadedFile::fake()->image('b.jpg', 300, 300))->assertOk();
        $second = $user->fresh()->avatar_path;

        $this->assertNotSame($first, $second);
        Storage::disk('local')->assertMissing($first);
        Storage::disk('local')->assertExists($second);
    }

    public function test_delete_avatar_borra_archivo_y_limpia_el_usuario()
    {
        $user = $this->user();
        $this->upload($user, UploadedFile::fake()->image('a.jpg', 300, 300))->assertOk();
        $path = $user->fresh()->avatar_path;

        $this->deleteJson('/api/me/avatar', [], $this->authHeaders($user))
            ->assertOk()->assertJsonPath('data.has_avatar', false);

        $this->assertNull($user->fresh()->avatar_path);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_get_avatar_sirve_la_imagen_propia_con_cabeceras_seguras()
    {
        $user = $this->user();
        $this->upload($user, UploadedFile::fake()->image('a.jpg', 300, 300))->assertOk();

        $res = $this->get('/api/me/avatar', $this->authHeaders($user));

        $res->assertOk();
        $this->assertSame('image/jpeg', $res->headers->get('Content-Type'));
        $this->assertSame('nosniff', $res->headers->get('X-Content-Type-Options'));
        $this->assertStringContainsString('private', $res->headers->get('Cache-Control'));
        $this->assertSame(Storage::disk('local')->get($user->fresh()->avatar_path), $res->streamedContent());
    }

    public function test_get_avatar_sin_foto_devuelve_404()
    {
        $user = $this->user();

        $this->get('/api/me/avatar', $this->authHeaders($user))->assertStatus(404);
    }

    public function test_get_avatar_requiere_autenticacion()
    {
        $this->get('/api/me/avatar', ['Accept' => 'application/json'])->assertStatus(401);
    }

    public function test_auth_me_informa_si_tiene_avatar()
    {
        $user = $this->user();
        $this->upload($user, UploadedFile::fake()->image('a.jpg', 300, 300))->assertOk();

        $this->getJson('/api/auth/me', $this->authHeaders($user))
            ->assertOk()->assertJsonPath('has_avatar', true)
            ->assertJsonPath('avatar_version', substr(basename($user->fresh()->avatar_path), 0, 8));
    }

    public function test_subida_de_avatar_tiene_rate_limit()
    {
        $user = $this->user();
        $headers = $this->authHeaders($user);
        for ($i = 0; $i < 10; $i++) {
            $this->post('/api/me/avatar', [], $headers)->assertStatus(422);
        }

        $this->post('/api/me/avatar', [], $headers)->assertStatus(429);
    }

    public function test_rechaza_peticion_sin_archivo()
    {
        $user = $this->user();

        $this->post('/api/me/avatar', [], $this->authHeaders($user))
            ->assertStatus(422)->assertJsonStructure(['avatar']);
    }
}
