<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ProfilePhotoService
{
    public function replace(UploadedFile $file, ?string $oldPath = null): string
    {
        $image = match ($file->getMimeType()) {
            'image/jpeg' => imagecreatefromjpeg($file->getPathname()),
            'image/png' => imagecreatefrompng($file->getPathname()),
            'image/webp' => imagecreatefromwebp($file->getPathname()),
            default => false,
        };

        if ($image === false) {
            throw new RuntimeException('Foto profil tidak dapat diproses.');
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $path = 'profile/'.Str::uuid().'.webp';
        $temporaryPath = tempnam(sys_get_temp_dir(), 'profile-photo-');

        imagewebp($image, $temporaryPath, 82);
        imagedestroy($image);

        Storage::disk('public')->put($path, file_get_contents($temporaryPath));
        unlink($temporaryPath);

        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
