<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use App\Services\ArticleBodyAttachmentProvider;

class Article extends Model
{
    protected $fillable = [
        'category_id', 'title', 'slug', 'subtitle', 'excerpt', 'body', 'status',
        'published_at', 'cover_image', 'cover_image_alt', 'cover_image_caption', 'seo_title', 'seo_description',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::saving(function (Article $article): void {
            $article->body = Str::of($article->body ?? '')->sanitizeHtml()->toString();

            if ($article->status === 'published' && $article->published_at === null) {
                $article->published_at = now();
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function safeBodyHtml(): string
    {
        return RichContentRenderer::make($this->body ?? '')
            ->fileAttachmentsDisk('r2_media')
            ->fileAttachmentsVisibility('private')
            ->fileAttachmentProvider(app(ArticleBodyAttachmentProvider::class, ['article' => $this]))
            ->toHtml();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
