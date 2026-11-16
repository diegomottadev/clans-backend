<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Re-codificación segura de fotos de perfil (OWASP File Upload Cheat Sheet, "Image rewriting"):
 * decodifica con GD, recorta al centro en cuadrado y vuelve a codificar como JPEG 256x256.
 * El archivo resultante es un JPEG nuevo: no conserva EXIF/metadatos ni bytes anexados (polyglots).
 * Se guarda en el disco PRIVADO 'local' (storage/app/avatars), fuera del document root,
 * con nombre UUID: nunca se usa el nombre ni la extensión que mandó el cliente.
 */
class AvatarImage
{
    const DISK = 'local';
    const DIR = 'avatars';
    const SIZE = 256;
    const QUALITY = 85;

    /**
     * @return string|null ruta relativa guardada, o null si GD no pudo decodificar la imagen.
     */
    public function store(UploadedFile $file)
    {
        $info = @getimagesize($file->getPathname());
        if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
            return null;
        }

        $src = $info[2] === IMAGETYPE_JPEG
            ? @imagecreatefromjpeg($file->getPathname())
            : @imagecreatefrompng($file->getPathname());
        if (!$src) {
            return null;
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $side = min($w, $h);
        $dst = imagecreatetruecolor(self::SIZE, self::SIZE);
        // Fondo blanco para PNG con transparencia (JPEG no tiene canal alfa)
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

    public function delete($path)
    {
        if (!empty($path) && strpos($path, self::DIR . '/') === 0) {
            Storage::disk(self::DISK)->delete($path);
        }
    }
}
