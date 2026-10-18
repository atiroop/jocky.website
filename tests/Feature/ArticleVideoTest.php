<?php

namespace Tests\Feature;

use App\Filament\RichEditor\ArticleVideoBlock;
use App\Models\Article;
use App\Services\ArticleVideoStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ArticleVideoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('r2_media');
        Storage::disk('r2_media')->buildTemporaryUrlsUsing(
            fn (string $path): string => 'https://signed.test/'.rawurlencode($path),
        );
    }

    public function test_valid_mp4_upload_uses_article_scoped_uuid_path(): void
    {
        $article = $this->article(42);
        $path = app(ArticleVideoStorage::class)->store($this->mp4Upload(), $article);

        $this->assertMatchesRegularExpression('#\Aarticle-videos/42/[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\.mp4\z#i', $path);
        Storage::disk('r2_media')->assertExists($path);
    }

    public function test_r2_upload_sets_video_content_type(): void
    {
        $file = $this->mp4Upload();
        $disk = \Mockery::mock();
        $disk->shouldReceive('putFileAs')
            ->once()
            ->withArgs(fn (string $directory, UploadedFile $uploaded, string $filename, array $options): bool =>
                $directory === 'article-videos/42'
                && $uploaded === $file
                && preg_match('#\A[0-9a-f-]{36}\.mp4\z#i', $filename) === 1
                && $options === ['mimetype' => 'video/mp4'])
            ->andReturn('article-videos/42/123e4567-e89b-42d3-a456-426614174000.mp4');
        Storage::shouldReceive('disk')->once()->with('r2_media')->andReturn($disk);

        $path = app(ArticleVideoStorage::class)->store($file, $this->article(42));

        $this->assertStringStartsWith('article-videos/42/', $path);
    }

    public function test_upload_rejects_non_mp4_content_oversized_files_and_unsaved_articles(): void
    {
        $storage = app(ArticleVideoStorage::class);

        foreach ([
            [UploadedFile::fake()->create('payload.mp4', 10, 'video/mp4'), $this->article(42)],
            [UploadedFile::fake()->create('large.mp4', 102401, 'video/mp4'), $this->article(42)],
            [$this->mp4Upload(), new Article],
        ] as [$file, $article]) {
            try {
                $storage->store($file, $article);
                $this->fail('Invalid video upload was accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('data.video_path', $exception->errors());
            }
        }
    }

    public function test_published_article_serializes_video_block_with_signed_responsive_player_and_caption(): void
    {
        $article = $this->article(42, 'published');
        $video = 'article-videos/42/123e4567-e89b-42d3-a456-426614174000.mp4';
        $poster = 'article-body/123e4567-e89b-42d3-a456-426614174001.png';
        Storage::disk('r2_media')->put($video, 'fake mp4');
        Storage::disk('r2_media')->put($poster, 'fake image');
        $source = $this->serializedBlock([
            'video_path' => $video,
            'poster_path' => $poster,
            'caption' => 'บันทึกระหว่างทาง',
        ]);
        $sanitized = Str::of(str_replace('<div ', '<div onclick="alert(1)" ', $source))->sanitizeHtml()->toString();
        $this->assertStringContainsString('data-config=', $sanitized);
        $this->assertStringNotContainsString('onclick', $sanitized);
        $article->body = $sanitized;

        $html = $article->safeBodyHtml();

        $this->assertStringContainsString('<video', $html);
        $this->assertStringContainsString('controls', $html);
        $this->assertStringContainsString('playsinline', $html);
        $this->assertStringContainsString('preload="metadata"', $html);
        $this->assertStringContainsString('width="1280" height="720"', $html);
        $this->assertStringContainsString('.article-video video { display: block; width: 100%; max-width: 100%; height: auto;', file_get_contents(resource_path('css/app.css')));
        $this->assertStringContainsString('https://signed.test/', $html);
        $this->assertStringContainsString('poster="https://signed.test/', $html);
        $this->assertStringContainsString('บันทึกระหว่างทาง', $html);
        $this->assertStringNotContainsString('data-config', $html);
    }

    public function test_storage_failure_is_reported_without_exposing_storage_details(): void
    {
        $storage = new class extends ArticleVideoStorage
        {
            protected function writeFile(string $directory, UploadedFile $file, string $filename): bool
            {
                return false;
            }
        };

        try {
            $storage->store($this->mp4Upload(), $this->article(42));
            $this->fail('A failed video upload was reported as successful.');
        } catch (ValidationException $exception) {
            $this->assertSame('ไม่สามารถจัดเก็บวิดีโอได้ โปรดลองอีกครั้ง', $exception->errors()['data.video_path'][0]);
        }
    }

    public function test_editor_preview_uses_the_native_video_player(): void
    {
        $path = 'article-videos/42/123e4567-e89b-42d3-a456-426614174000.mp4';
        Storage::disk('r2_media')->put($path, 'fake mp4');

        $preview = ArticleVideoBlock::toPreviewHtml(['video_path' => $path]);

        $this->assertStringContainsString('<video', $preview);
        $this->assertStringContainsString('controls', $preview);
        $this->assertStringContainsString('playsinline', $preview);
        $this->assertStringContainsString('https://signed.test/', $preview);
    }

    public function test_draft_future_and_traversal_video_blocks_never_receive_signed_urls(): void
    {
        $video = 'article-videos/42/123e4567-e89b-42d3-a456-426614174000.mp4';
        Storage::disk('r2_media')->put($video, 'fake mp4');

        foreach ([$this->article(42, 'draft'), $this->article(42, 'published', now()->addDay())] as $article) {
            $article->body = $this->serializedBlock(['video_path' => $video]);
            $html = $article->safeBodyHtml();
            $this->assertStringNotContainsString('https://signed.test/', $html);
            $this->assertStringNotContainsString('<video', $html);
        }

        $published = $this->article(42, 'published');
        $published->body = $this->serializedBlock(['video_path' => '../article-videos/42/secret.mp4']);
        $this->assertStringNotContainsString('https://signed.test/', $published->safeBodyHtml());

        $this->assertSame('article-video', ArticleVideoBlock::getId());
    }

    private function article(int $id, string $status = 'published', mixed $publishedAt = null): Article
    {
        $article = new Article;
        $article->setRawAttributes([
            'id' => $id,
            'status' => $status,
            'published_at' => $publishedAt ?? now()->subMinute(),
        ], sync: true);
        $article->exists = true;

        return $article;
    }

    private function serializedBlock(array $config): string
    {
        return '<div data-type="customBlock" data-id="article-video" data-config="'.e(json_encode($config, JSON_THROW_ON_ERROR)).'"></div>';
    }

    private function mp4Upload(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'jocky-video-');
        file_put_contents($path, pack('N', 24).'ftypisom'.pack('N', 0).'isomiso2');

        return new UploadedFile($path, 'sample.mp4', 'video/mp4', null, true);
    }
}
