<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Filament\Panel;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class CmsDatabaseTest extends TestCase
{
    use DatabaseTransactions;

    private function article(array $attributes = []): Article
    {
        return Article::create(array_merge([
            'title' => 'A test article',
            'slug' => 'a-test-article',
            'excerpt' => 'A test excerpt',
            'body' => '<p>Article body</p>',
            'status' => 'draft',
        ], $attributes));
    }

    public function test_article_crud_and_category_tag_relationships_are_database_backed(): void
    {
        $category = Category::create(['name' => 'Essays', 'slug' => 'essays']);
        $tag = Tag::create(['name' => 'Reading', 'slug' => 'reading']);
        $article = $this->article(['category_id' => $category->id]);
        $article->tags()->sync([$tag->id]);

        $this->assertDatabaseHas('articles', ['id' => $article->id, 'title' => 'A test article']);
        $this->assertTrue($article->fresh()->category->is($category));
        $this->assertTrue($article->fresh()->tags->contains($tag));

        $article->update(['title' => 'Edited article', 'excerpt' => 'Edited excerpt']);
        $this->assertDatabaseHas('articles', ['id' => $article->id, 'title' => 'Edited article']);

        $article->delete();
        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
        $this->assertDatabaseMissing('article_tag', ['article_id' => $article->id, 'tag_id' => $tag->id]);

        $categorized = $this->article(['slug' => 'category-delete-case', 'category_id' => $category->id]);
        $category->delete();
        $this->assertNull($categorized->fresh()->category_id);
    }

    public function test_drafts_are_hidden_and_published_articles_appear_in_archive_search_and_detail(): void
    {
        $draft = $this->article(['title' => 'Secret draft', 'slug' => 'secret-draft']);
        $published = $this->article([
            'title' => 'Published finding',
            'slug' => 'published-finding',
            'excerpt' => 'Searchable phrase',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        $this->get('/writing')->assertOk()->assertSee('Published finding')->assertDontSee('Secret draft');
        $this->get('/writing?q=Searchable')->assertOk()->assertSee('Published finding')->assertDontSee('Secret draft');
        $this->get(route('writing.show', $published))->assertOk()->assertSee('Published finding');
        $this->get(route('writing.show', $draft))->assertNotFound();
    }

    public function test_future_publication_is_not_publicly_visible(): void
    {
        $future = $this->article([
            'title' => 'Future article',
            'slug' => 'future-article',
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);

        $this->assertSame(0, Article::published()->whereKey($future->id)->count());
        $this->get('/writing')->assertDontSee('Future article');
        $this->get(route('writing.show', $future))->assertNotFound();
    }

    public function test_database_enforces_unique_article_slugs(): void
    {
        $this->article();

        $this->expectException(QueryException::class);
        $this->article(['title' => 'Duplicate', 'slug' => 'a-test-article']);
    }

    public function test_rich_text_is_sanitized_when_saved_and_rendered(): void
    {
        $article = $this->article([
            'title' => 'Safe article',
            'slug' => 'safe-article',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'body' => '<p>Safe text</p><script>alert(1)</script><img src=x onerror=alert(1)>',
        ]);

        $this->assertStringContainsString('Safe text', $article->fresh()->body);
        $this->assertStringNotContainsString('<script', strtolower($article->fresh()->body));
        $this->assertStringNotContainsString('onerror', strtolower($article->fresh()->body));
        $this->get(route('writing.show', $article))->assertOk()->assertSee('Safe text')->assertDontSee('onerror');
    }

    public function test_only_configured_admin_can_access_filament_and_registration_is_disabled(): void
    {
        config(['cms.admin_email' => 'admin@jocky.website']);
        $panel = Mockery::mock(Panel::class);
        $panel->shouldReceive('getId')->andReturn('admin');

        $admin = User::create(['name' => 'Test Admin', 'email' => 'admin@jocky.website', 'password' => bin2hex(random_bytes(32))]);
        $admin->forceFill(['is_admin' => true])->save();
        $ordinary = User::create(['name' => 'Ordinary User', 'email' => 'reader@example.test', 'password' => bin2hex(random_bytes(32))]);

        $this->assertTrue($admin->canAccessPanel($panel));
        $this->assertFalse($ordinary->canAccessPanel($panel));
        $this->actingAs($ordinary)->get('/admin')->assertForbidden();
        $this->get('/admin/register')->assertNotFound();
    }
}
