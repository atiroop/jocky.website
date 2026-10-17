<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_about_page_renders(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee('เกี่ยวกับ')
            ->assertSee(config('jocky.about_intro'));
    }

    public function test_admin_login_is_available_without_public_registration(): void
    {
        $this->get('/admin/login')->assertOk();
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('filament.admin.auth.register'));
    }
}
