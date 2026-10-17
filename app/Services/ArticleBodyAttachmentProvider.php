<?php

namespace App\Services;

use App\Models\Article;
use Filament\Forms\Components\RichEditor\FileAttachmentProviders\Contracts\FileAttachmentProvider;
use Filament\Forms\Components\RichEditor\RichContentAttribute;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

class ArticleBodyAttachmentProvider implements FileAttachmentProvider
{
    private ?RichContentAttribute $attribute = null;

    public function __construct(private ?Article $article = null) {}

    public function attribute(RichContentAttribute $attribute): static
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getFileAttachmentUrl(mixed $file): ?string
    {
        $article = $this->article ?? $this->attribute?->getModel();

        if (! $article instanceof Article || $article->status !== 'published' || ! $article->published_at?->isPast()) {
            return null;
        }

        if (! is_string($file) || ! preg_match('#\\Aarticle-body/[A-Za-z0-9_-]+\\.(?:jpe?g|png|webp)\\z#i', $file)) {
            return null;
        }

        try {
            $disk = Storage::disk('r2_media');

            return $disk->exists($file) ? $disk->temporaryUrl($file, now()->addMinutes(30)) : null;
        } catch (Throwable) {
            return null;
        }
    }

    public function saveUploadedFileAttachment(TemporaryUploadedFile $file): string
    {
        return app(ArticleMediaStorage::class)->storeBodyAttachment($file);
    }

    public function getDefaultFileAttachmentVisibility(): ?string
    {
        return 'private';
    }

    public function isExistingRecordRequiredToSaveNewFileAttachments(): bool
    {
        return false;
    }

    public function cleanUpFileAttachments(array $exceptIds): void
    {
        // Media cleanup is deliberately deferred to a future retention workflow.
    }
}
