<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertOk()->assertSee('Notes on living');
    }

    public function test_the_writing_archive_renders(): void
    {
        $this->get('/writing')
            ->assertOk()
            ->assertSee('The archive')
            ->assertSee('The shape of a morning');
    }

    public function test_an_article_renders_from_route_fixture_data(): void
    {
        $this->get('/writing/the-shape-of-a-morning')
            ->assertOk()
            ->assertSee('Temporary sample article');
    }

    public function test_an_unknown_article_returns_not_found(): void
    {
        $this->get('/writing/not-an-entry')->assertNotFound();
    }

    public function test_the_about_page_renders(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee('A small introduction')
            ->assertSee('No biography has been added yet');
    }
}
