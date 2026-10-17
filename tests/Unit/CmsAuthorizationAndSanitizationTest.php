<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Models\User;
use Filament\Panel;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Mockery;
use Tests\TestCase;

class CmsAuthorizationAndSanitizationTest extends TestCase
{
    public function test_only_an_explicit_admin_can_access_the_admin_panel(): void
    {
        $panel = Mockery::mock(Panel::class);
        $panel->shouldReceive('getId')->andReturn('admin');
        config(['cms.admin_email' => 'owner@example.test']);

        $authorized = new User;
        $authorized->forceFill(['is_admin' => true, 'email' => 'OWNER@example.test']);
        $this->assertTrue($authorized->canAccessPanel($panel));

        $ordinary = new User;
        $ordinary->forceFill(['is_admin' => true, 'email' => 'other@example.test']);
        $this->assertFalse($ordinary->canAccessPanel($panel));
    }

    public function test_article_rich_text_is_sanitized_before_public_rendering(): void
    {
        $article = new Article(['body' => '<p>Safe text</p><script>alert(1)</script><img src=x onerror=alert(1)>']);
        $html = $article->safeBodyHtml();

        $this->assertStringContainsString('Safe text', $html);
        $this->assertStringNotContainsString('<script', strtolower($html));
        $this->assertStringNotContainsString('onerror', strtolower($html));
    }

    public function test_article_urls_use_the_unique_slug(): void
    {
        $this->assertSame('slug', (new Article)->getRouteKeyName());
    }

    public function test_public_query_scope_excludes_drafts_and_future_publications(): void
    {
        config(['database.connections.cms_sql' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'database' => 'unused',
            'username' => 'unused',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ]]);

        $article = (new Article)->setConnection('cms_sql');
        $query = $article->newQuery()->published();
        $sql = $query->toSql();

        $this->assertStringContainsString('`status` = ?', $sql);
        $this->assertStringContainsString('`published_at` is not null', $sql);
        $this->assertStringContainsString('`published_at` <= ?', $sql);
        $this->assertSame('published', $query->getBindings()[0]);
    }

    public function test_category_and_tag_relationships_are_defined(): void
    {
        config(['database.connections.cms_sql' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'database' => 'unused',
            'username' => 'unused',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ]]);

        $article = (new Article)->setConnection('cms_sql');

        $this->assertInstanceOf(BelongsTo::class, $article->category());
        $this->assertInstanceOf(BelongsToMany::class, $article->tags());
    }

    public function test_article_form_schema_builds_with_the_installed_filament_api(): void
    {
        $schema = \App\Filament\Resources\Articles\ArticleResource::form(Schema::make());

        $this->assertInstanceOf(Schema::class, $schema);
    }

}
