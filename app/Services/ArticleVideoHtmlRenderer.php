<?php

namespace App\Services;

use App\Models\Article;

class ArticleVideoHtmlRenderer
{
    public function render(string $html, Article $article): string
    {
        return preg_replace_callback(
            '~<figure\b([^>]*\bdata-type="jocky-video-placeholder"[^>]*)>(.*?)</figure>~is',
            function (array $match) use ($article): string {
                if (! preg_match('/\bdata-id="([^"]+)"/i', $match[1], $videoMatch)) {
                    return '';
                }

                $videoPath = html_entity_decode($videoMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $delivery = app(ArticleVideoDelivery::class);
                $videoUrl = $delivery->signedUrl($article, $videoPath);

                if ($videoUrl === null) {
                    return '';
                }

                $posterAttribute = '';

                if (preg_match('/<img\b[^>]*\bdata-id="([^"]+)"[^>]*>/i', $match[2], $posterMatch)) {
                    $posterPath = html_entity_decode($posterMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $posterUrl = $delivery->posterUrl($article, $posterPath);

                    if ($posterUrl !== null) {
                        $posterAttribute = ' poster="'.e($posterUrl).'"';
                    }
                }

                $caption = '';

                if (preg_match('~<figcaption\b[^>]*>(.*?)</figcaption>~is', $match[2], $captionMatch)) {
                    $captionText = trim(html_entity_decode(strip_tags($captionMatch[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

                    if ($captionText !== '') {
                        $caption = '<figcaption>'.e($captionText).'</figcaption>';
                    }
                }

                return '<figure class="article-video"><video controls playsinline preload="metadata" width="1280" height="720" src="'.e($videoUrl).'"'.$posterAttribute.' aria-label="วิดีโอประกอบบทความ"><p>เบราว์เซอร์ของคุณไม่รองรับการเล่นวิดีโอ</p></video>'.$caption.'</figure>';
            },
            $html,
        ) ?? $html;
    }
}
