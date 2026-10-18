<?php

namespace App\Filament\RichEditor;

use App\Models\Article;
use App\Services\ArticleMediaStorage;
use App\Services\ArticleVideoDelivery;
use App\Services\ArticleVideoStorage;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor\RichContentCustomBlock;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ArticleVideoBlock extends RichContentCustomBlock
{
    public static function getId(): string
    {
        return 'article-video';
    }

    public static function getLabel(): string
    {
        return 'วิดีโอ';
    }

    public static function configureEditorAction(Action $action): Action
    {
        return $action->schema([
            FileUpload::make('video_path')
                ->label('ไฟล์วิดีโอ MP4')
                ->required()
                ->acceptedFileTypes(['video/mp4'])
                ->maxSize((int) (ArticleVideoStorage::MAX_BYTES / 1024))
                ->helperText('บันทึกบทความก่อนเพิ่มวิดีโอ เพื่อผูกไฟล์กับบทความนี้')
                ->disk('r2_media')
                ->visibility('private')
                ->previewable(false)
                ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file, ?Article $record = null): string => app(ArticleVideoStorage::class)->store($file, $record)),
            FileUpload::make('poster_path')
                ->label('ภาพโปสเตอร์ (ไม่บังคับ)')
                ->image()
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(5120)
                ->disk('r2_media')
                ->directory('article-body')
                ->visibility('private')
                ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => app(ArticleMediaStorage::class)->storeBodyAttachment($file)),
            Textarea::make('caption')
                ->label('คำบรรยาย (ไม่บังคับ)')
                ->rows(2)
                ->maxLength(500),
        ]);
    }

    public static function toPreviewHtml(array $config): ?string
    {
        $path = $config['video_path'] ?? null;

        if (! is_string($path) || ! preg_match('#\Aarticle-videos/[1-9][0-9]*/[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\.mp4\z#i', $path)) {
            return '<p>เลือกไฟล์วิดีโอ MP4 เพื่อแสดงตัวอย่าง</p>';
        }

        try {
            $disk = Storage::disk('r2_media');

            if (! $disk->exists($path)) {
                return '<p>ไม่พบไฟล์วิดีโอ</p>';
            }

            $url = $disk->temporaryUrl($path, now()->addMinutes(30));
        } catch (\Throwable) {
            return '<p>ไม่สามารถแสดงตัวอย่างวิดีโอได้</p>';
        }

        return '<video controls playsinline preload="metadata" width="1280" height="720" src="'.e($url).'" aria-label="ตัวอย่างวิดีโอ"></video>';
    }

    public static function toHtml(array $config, array $data): ?string
    {
        $article = $data['article'] ?? null;
        $path = $config['video_path'] ?? null;

        if (! $article instanceof Article || ! app(ArticleVideoDelivery::class)->isPublic($article) || ! is_string($path)) {
            return null;
        }

        // Only a harmless, allow-listed marker survives Filament's HTML sanitizer.
        // Article::safeBodyHtml() expands it into a video after sanitization.
        if (! preg_match('#\Aarticle-videos/'.preg_quote((string) $article->getKey(), '#').'/[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\.mp4\z#i', $path)) {
            return null;
        }

        $poster = $config['poster_path'] ?? null;
        $posterMarkup = is_string($poster) && preg_match('#\Aarticle-body/[A-Za-z0-9_-]+\.(?:jpe?g|png|webp)\z#i', $poster)
            ? '<img data-id="'.e($poster).'" alt="" />'
            : '';
        $caption = is_string($config['caption'] ?? null) ? mb_substr($config['caption'], 0, 500) : '';

        return '<figure class="article-video-placeholder" data-type="jocky-video-placeholder" data-id="'.e($path).'">'.$posterMarkup.($caption !== '' ? '<figcaption>'.e($caption).'</figcaption>' : '').'</figure>';
    }
}
