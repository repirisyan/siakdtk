<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class KontenImageService
{
    public function store(UploadedFile $file, string $directory, ?string $oldPath = null): string
    {
        $image = match ($file->getMimeType()) {
            'image/jpeg' => imagecreatefromjpeg($file->getPathname()),
            'image/png' => imagecreatefrompng($file->getPathname()),
            'image/webp' => imagecreatefromwebp($file->getPathname()),
            default => false,
        };

        if ($image === false) {
            throw new RuntimeException('Gambar konten tidak dapat diproses.');
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $path = trim($directory, '/').'/'.Str::uuid().'.webp';
        $temporaryPath = tempnam(sys_get_temp_dir(), 'konten-image-');

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
