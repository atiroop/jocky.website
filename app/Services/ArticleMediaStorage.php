<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ArticleMediaStorage
{
    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function storeCover(UploadedFile $file): string
    {
        return $this->store($file, 'article-covers', 5 * 1024 * 1024, 'data.cover_image');
    }

    public function storeBodyAttachment(UploadedFile $file): string
    {
        return $this->store($file, 'article-body', 10 * 1024 * 1024, 'data.body');
    }

    private function store(UploadedFile $file, string $directory, int $maxBytes, string $field): string
    {
        $mime = $file->getMimeType();
        $extension = self::MIME_EXTENSIONS[$mime] ?? null;

        if (! $file->isValid() || ! $extension || $file->getSize() > $maxBytes) {
            throw ValidationException::withMessages([$field => 'Upload a valid JPEG, PNG, or WebP image within the size limit.']);
        }

        $path = $directory.'/'.Str::uuid().'.'.$extension;

        try {
            if (! $this->writeFile($directory, $file, basename($path), $mime)) {
                throw new \RuntimeException('Storage did not confirm the upload.');
            }
        } catch (Throwable) {
            throw ValidationException::withMessages([$field => 'The image could not be stored. Please retry.']);
        }

        return $path;
    }

    protected function writeFile(string $directory, UploadedFile $file, string $filename, string $mime): bool
    {
        return (bool) Storage::disk('r2_media')->putFileAs($directory, $file, $filename, ['mimetype' => $mime]);
    }
}
