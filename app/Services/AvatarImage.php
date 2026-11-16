<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Re-codificación segura de fotos de perfil (OWASP File Upload Cheat Sheet, "Image rewriting"):
 * decodifica con GD cualquier formato habitual (JPEG, PNG, GIF, WebP, BMP), aplica la
 * orientación EXIF de las fotos de celular, recorta al centro en cuadrado y vuelve a codificar
 * como JPEG 256x256. El archivo resultante es un JPEG nuevo: no conserva EXIF/metadatos ni
 * bytes anexados (polyglots).
 * Se guarda en el disco PRIVADO 'local' (storage/app/avatars), fuera del document root,
 * con nombre UUID: nunca se usa el nombre ni la extensión que mandó el cliente.
 */
class AvatarImage
{
    const DISK = 'local';
    const DIR = 'avatars';
    const SIZE = 256;
    const QUALITY = 85;

    /** Tipo detectado por contenido => función de GD que lo decodifica. */
    const DECODERS = [
        IMAGETYPE_JPEG => 'imagecreatefromjpeg',
        IMAGETYPE_PNG => 'imagecreatefrompng',
        IMAGETYPE_GIF => 'imagecreatefromgif',
        IMAGETYPE_WEBP => 'imagecreatefromwebp',
        IMAGETYPE_BMP => 'imagecreatefrombmp',
    ];

    /**
     * @return string|null ruta relativa guardada, o null si GD no pudo decodificar la imagen.
     */
    public function store(UploadedFile $file)
    {
        $pathname = $file->getPathname();
        $info = @getimagesize($pathname);
        if (!$info || !isset(self::DECODERS[$info[2]]) || !function_exists(self::DECODERS[$info[2]])) {
            return null;
        }

        $src = @call_user_func(self::DECODERS[$info[2]], $pathname);
        if (!$src) {
            return null;
        }
        if ($info[2] === IMAGETYPE_JPEG) {
            $src = $this->applyExifOrientation($src, $pathname);
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $side = min($w, $h);
        $dst = imagecreatetruecolor(self::SIZE, self::SIZE);
        // Fondo blanco para imágenes con transparencia (JPEG no tiene canal alfa)
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopyresampled($dst, $src, 0, 0, (int) (($w - $side) / 2), (int) (($h - $side) / 2), self::SIZE, self::SIZE, $side, $side);
        imagedestroy($src);

        ob_start();
        imagejpeg($dst, null, self::QUALITY);
        $jpeg = ob_get_clean();
        imagedestroy($dst);

        $path = self::DIR . '/' . Str::uuid()->toString() . '.jpg';
        Storage::disk(self::DISK)->put($path, $jpeg);

        return $path;
    }

    /**
     * Las cámaras guardan la foto "acostada" y anotan el giro en EXIF (Orientation 1-8).
     * GD ignora ese dato, así que se aplica acá antes de recortar.
     */
    private function applyExifOrientation($img, string $pathname)
    {
        if (!function_exists('exif_read_data')) {
            return $img;
        }
        $exif = @exif_read_data($pathname);
        $orientation = (int) ($exif['Orientation'] ?? 1);

        // imagerotate gira en sentido antihorario
        $transforms = [
            2 => [0, IMG_FLIP_HORIZONTAL],
            3 => [180, null],
            4 => [0, IMG_FLIP_VERTICAL],
            5 => [-90, IMG_FLIP_HORIZONTAL],
            6 => [-90, null],
            7 => [90, IMG_FLIP_HORIZONTAL],
            8 => [90, null],
        ];
        if (!isset($transforms[$orientation])) {
            return $img;
        }
        [$angle, $flip] = $transforms[$orientation];
        if ($angle !== 0) {
            $rotated = imagerotate($img, $angle, 0);
            if ($rotated) {
                imagedestroy($img);
                $img = $rotated;
            }
        }
        if ($flip !== null) {
            imageflip($img, $flip);
        }
        return $img;
    }

    public function delete($path)
    {
        if (!empty($path) && strpos($path, self::DIR . '/') === 0) {
            Storage::disk(self::DISK)->delete($path);
        }
    }
}
