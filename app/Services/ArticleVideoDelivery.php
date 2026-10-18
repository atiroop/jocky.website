<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ArticleVideoDelivery
{
    public function signedUrl(Article $article, mixed $path): ?string
    {
        if (! $this->isPublic($article) || ! is_string($path) || ! $this->belongsToArticle($path, $article)) {
            return null;
        }

        try {
            $disk = Storage::disk('r2_media');

            return $disk->exists($path) ? $disk->temporaryUrl($path, now()->addHours(6)) : null;
        } catch (Throwable) {
            return null;
        }
    }

    public function posterUrl(Article $article, mixed $path): ?string
    {
        if (! $this->isPublic($article)) {
            return null;
        }

        return app(ArticleBodyAttachmentProvider::class, ['article' => $article])->getFileAttachmentUrl($path);
    }

    public function isPublic(Article $article): bool
    {
        return $article->status === 'published'
            && $article->published_at !== null
            && $article->published_at->lessThanOrEqualTo(now());
    }

    private function belongsToArticle(string $path, Article $article): bool
    {
        $id = preg_quote((string) $article->getKey(), '#');

        return preg_match('#\Aarticle-videos/'.$id.'/[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\.mp4\z#i', $path) === 1;
    }
}
