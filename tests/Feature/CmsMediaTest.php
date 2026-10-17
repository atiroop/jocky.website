<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\ArticleBodyAttachmentProvider;
use App\Services\ArticleMediaStorage;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CmsMediaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cover_upload_uses_unique_private_media_path_and_survives_article_edits(): void
    {
        Storage::fake('r2_media');
        Storage::fake('local');

        $path = app(ArticleMediaStorage::class)->storeCover(UploadedFile::fake()->create('cover.png', 100, 'image/png'));
        $this->assertMatchesRegularExpression('#\\Aarticle-covers/[0-9a-f-]{36}\\.png\\z#', $path);
        Storage::disk('r2_media')->assertExists($path);
        Storage::disk('local')->assertMissing($path);

        $article = Article::create([
            'title' => 'Media article',
            'slug' => 'media-article',
            'excerpt' => 'Cover test',
            'body' => '<p>Body</p>',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'cover_image' => $path,
            'cover_image_alt' => 'A quiet page',
            'cover_image_caption' => 'A caption',
        ]);

        $article->update(['title' => 'Edited media article']);
        $this->assertSame($path, $article->fresh()->cover_image);
        $this->get(route('writing.show', $article))->assertOk()->assertSee('A quiet page')->assertSee('A caption');
        $this->get(route('writing.cover', $article))->assertOk();

        $article->update(['status' => 'draft']);
        $this->get(route('writing.cover', $article))->assertNotFound();
        $article->update(['status' => 'published', 'published_at' => now()->addDay()]);
        $this->get(route('writing.cover', $article))->assertNotFound();
    }

    public function test_rich_editor_attachments_use_a_scoped_path_and_reject_path_traversal(): void
    {
        Storage::fake('r2_media');
        $path = app(ArticleMediaStorage::class)->storeBodyAttachment(UploadedFile::fake()->create('inline.webp', 50, 'image/webp'));
        Storage::disk('r2_media')->assertExists($path);

        $safe = new Article(['body' => '<p>Text</p><img data-id="'.$path.'" alt="Inline art">']);
        $html = $safe->safeBodyHtml();
        $this->assertStringContainsString('Inline art', $html);

        $unsafe = new Article(['body' => '<img data-id="../private/secret.png" alt="Unsafe">']);
        $this->assertStringNotContainsString('https://', $unsafe->safeBodyHtml());
    }

    public function test_signed_inline_media_urls_require_published_non_future_articles(): void
    {
        Storage::fake('r2_media');
        $path = 'article-body/123e4567-e89b-12d3-a456-426614174000.png';
        Storage::disk('r2_media')->put($path, 'image');
        Storage::disk('r2_media')->buildTemporaryUrlsUsing(fn ($file): string => 'https://signed.test/'.rawurlencode($file));

        $provider = new ArticleBodyAttachmentProvider(new Article(['status' => 'published', 'published_at' => now()->subMinute()]));
        $this->assertSame('https://signed.test/'.rawurlencode($path), $provider->getFileAttachmentUrl($path));
        $this->assertNull($provider->getFileAttachmentUrl('../private/secret.png'));

        $draft = new Article(['status' => 'draft', 'published_at' => now()->subMinute()]);
        $this->assertNull((new ArticleBodyAttachmentProvider($draft))->getFileAttachmentUrl($path));

        $future = new Article(['status' => 'published', 'published_at' => now()->addDay()]);
        $this->assertNull((new ArticleBodyAttachmentProvider($future))->getFileAttachmentUrl($path));
    }

    public function test_uploads_reject_unsupported_mime_types_and_oversized_images(): void
    {
        $storage = app(ArticleMediaStorage::class);

        try {
            $storage->storeCover(UploadedFile::fake()->create('payload.svg', 10, 'image/svg+xml'));
            $this->fail('Unsupported MIME type was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('data.cover_image', $exception->errors());
        }

        try {
            $storage->storeCover(UploadedFile::fake()->create('large.jpg', 6000, 'image/jpeg'));
            $this->fail('Oversized cover was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('data.cover_image', $exception->errors());
        }
    }

    public function test_storage_failures_return_a_safe_validation_error(): void
    {
        $storage = new class extends ArticleMediaStorage
        {
            protected function writeFile(string $directory, UploadedFile $file, string $filename, string $mime): bool
            {
                return false;
            }
        };

        try {
            $storage->storeCover(UploadedFile::fake()->create('cover.jpg', 100, 'image/jpeg'));
            $this->fail('Failed storage was reported as a successful upload.');
        } catch (ValidationException $exception) {
            $this->assertSame('The image could not be stored. Please retry.', $exception->errors()['data.cover_image'][0]);
        }
    }

    public function test_r2_media_and_temporary_storage_are_separate_and_public_url_is_not_implicitly_used(): void
    {
        $this->assertStringEndsWith('article-media', config('filesystems.disks.r2_media.root'));
        $this->assertStringEndsWith('private', config('filesystems.disks.r2_private.root'));
        $this->assertSame('jocky-cdn-storage', config('filesystems.disks.r2_media.bucket'));
        $this->assertNull(config('filesystems.disks.r2_media.url'));
        $this->assertStringEndsWith('private', config('filesystems.disks.local.root'));
    }
}
