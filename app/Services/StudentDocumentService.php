<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class StudentDocumentService
{
    public function store(UploadedFile $file, string $directory = 'student-documents'): string
    {
        if ($file->getMimeType() === 'application/pdf') {
            return $file->store($directory, 'public');
        }

        $image = match ($file->getMimeType()) {
            'image/jpeg' => imagecreatefromjpeg($file->getPathname()),
            'image/png' => imagecreatefrompng($file->getPathname()),
            'image/webp' => imagecreatefromwebp($file->getPathname()),
            default => false,
        };

        if ($image === false) {
            throw new RuntimeException('Dokumen gambar tidak dapat diproses.');
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $path = $directory.'/'.Str::uuid().'.webp';
        $temporaryPath = tempnam(sys_get_temp_dir(), 'student-document-');
        imagewebp($image, $temporaryPath, 82);
        imagedestroy($image);

        Storage::disk('public')->put($path, file_get_contents($temporaryPath));
        unlink($temporaryPath);

        return $path;
    }
}
