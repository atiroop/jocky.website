<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ArticleVideoStorage
{
    public const MAX_BYTES = 100 * 1024 * 1024;

    public function store(UploadedFile $file, ?Article $article): string
    {
        if (! $article?->exists || ! is_numeric($article->getKey()) || (int) $article->getKey() < 1) {
            throw ValidationException::withMessages(['data.video_path' => 'กรุณาบันทึกบทความก่อนเพิ่มวิดีโอ']);
        }

        if (! $file->isValid() || $file->getSize() > self::MAX_BYTES || ! $this->isValidMp4($file)) {
            throw ValidationException::withMessages(['data.video_path' => 'กรุณาเลือกไฟล์วิดีโอ MP4 ที่ถูกต้อง ขนาดไม่เกิน 100 MB']);
        }

        $directory = 'article-videos/'.(int) $article->getKey();
        $filename = Str::uuid().'.mp4';

        try {
            if (! $this->writeFile($directory, $file, $filename)) {
                throw new \RuntimeException('Storage did not confirm the upload.');
            }
        } catch (Throwable) {
            throw ValidationException::withMessages(['data.video_path' => 'ไม่สามารถจัดเก็บวิดีโอได้ โปรดลองอีกครั้ง']);
        }

        return $directory.'/'.$filename;
    }

    protected function isValidMp4(UploadedFile $file): bool
    {
        if (! $file->isValid() || strtolower($file->getClientOriginalExtension()) !== 'mp4' || $file->getMimeType() !== 'video/mp4') {
            return false;
        }

        $handle = @fopen($file->getRealPath(), 'rb');

        if ($handle === false) {
            return false;
        }

        try {
            $header = fread($handle, 12);
        } finally {
            fclose($handle);
        }

        return is_string($header) && strlen($header) === 12 && substr($header, 4, 4) === 'ftyp';
    }

    protected function writeFile(string $directory, UploadedFile $file, string $filename): bool
    {
        return (bool) Storage::disk('r2_media')->putFileAs(
            $directory,
            $file,
            $filename,
            ['mimetype' => 'video/mp4'],
        );
    }
}
