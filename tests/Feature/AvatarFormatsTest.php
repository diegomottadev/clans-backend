<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Oráculo: cualquier foto habitual (JPEG, PNG, GIF, WebP, BMP) se acepta y se guarda
 * convertida al formato interno (JPEG 256x256), respetando la orientación EXIF de las
 * fotos de celular. Lo que no es imagen se sigue rechazando.
 */
class AvatarFormatsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function user(): User
    {
        return User::where('role', 'user')->firstOrFail();
    }

    /** Crea un archivo de imagen real con GD en el formato pedido. */
    private function imageFile(string $format, int $w = 300, int $h = 300): UploadedFile
    {
        $img = imagecreatetruecolor($w, $h);
        imagefill($img, 0, 0, imagecolorallocate($img, 30, 120, 200));
        $path = tempnam(sys_get_temp_dir(), 'av');
        $writers = ['gif' => 'imagegif', 'bmp' => 'imagebmp', 'webp' => 'imagewebp', 'png' => 'imagepng', 'jpeg' => 'imagejpeg'];
        $this->assertTrue(function_exists($writers[$format]), "GD no puede escribir $format");
        $writers[$format]($img, $path);
        imagedestroy($img);
        $mime = ['gif' => 'image/gif', 'bmp' => 'image/bmp', 'webp' => 'image/webp', 'png' => 'image/png', 'jpeg' => 'image/jpeg'][$format];
        return new UploadedFile($path, "foto.$format", $mime, null, true);
    }

    private function assertStoredAsJpeg256(User $user): void
    {
        $path = $user->fresh()->avatar_path;
        $this->assertNotEmpty($path);
        $info = getimagesizefromstring(Storage::disk('local')->get($path));
        $this->assertSame([256, 256, IMAGETYPE_JPEG], [$info[0], $info[1], $info[2]]);
    }

    public function formats(): array
    {
        return [['gif'], ['webp'], ['bmp']];
    }

    /** @dataProvider formats */
    public function test_acepta_el_formato_y_lo_guarda_como_jpeg_256($format)
    {
        $user = $this->user();
        $this->post('/api/me/avatar', ['avatar' => $this->imageFile($format)], $this->authHeaders($user))->assertOk();
        $this->assertStoredAsJpeg256($user);
    }

    public function test_aplica_la_orientacion_exif_de_las_fotos_de_celular()
    {
        // 300x150: tercio izquierdo rojo, resto azul. EXIF Orientation=6 (girar 90° horario):
        // al mostrarla, el rojo queda ARRIBA. Sin aplicar la orientación, arriba-centro sería azul.
        $img = imagecreatetruecolor(300, 150);
        imagefill($img, 0, 0, imagecolorallocate($img, 0, 0, 255));
        imagefilledrectangle($img, 0, 0, 99, 149, imagecolorallocate($img, 255, 0, 0));
        ob_start();
        imagejpeg($img, null, 95);
        $jpeg = ob_get_clean();
        imagedestroy($img);

        $tiff = "MM\x00\x2a\x00\x00\x00\x08" . "\x00\x01" . "\x01\x12\x00\x03\x00\x00\x00\x01\x00\x06\x00\x00" . "\x00\x00\x00\x00";
        $payload = "Exif\x00\x00" . $tiff;
        $app1 = "\xFF\xE1" . pack('n', strlen($payload) + 2) . $payload;
        $path = tempnam(sys_get_temp_dir(), 'av');
        file_put_contents($path, substr($jpeg, 0, 2) . $app1 . substr($jpeg, 2));
        $this->assertSame(6, @exif_read_data($path)['Orientation'] ?? null, 'el archivo de prueba debe tener Orientation=6');

        $user = $this->user();
        $this->post('/api/me/avatar', ['avatar' => new UploadedFile($path, 'celular.jpg', 'image/jpeg', null, true)], $this->authHeaders($user))
            ->assertOk();

        $stored = imagecreatefromstring(Storage::disk('local')->get($user->fresh()->avatar_path));
        $rgb = imagecolorat($stored, 128, 20);
        $red = ($rgb >> 16) & 0xFF;
        $blue = $rgb & 0xFF;
        $this->assertGreaterThan($blue, $red, 'arriba-centro debe ser rojo (orientación aplicada)');
    }

    public function test_sigue_rechazando_lo_que_no_es_imagen()
    {
        $path = tempnam(sys_get_temp_dir(), 'av');
        file_put_contents($path, "GIF89a<?php echo 'x'; ?>");
        $this->post('/api/me/avatar', ['avatar' => new UploadedFile($path, 'falso.gif', 'image/gif', null, true)], $this->authHeaders($this->user()))
            ->assertStatus(422);
    }
}
