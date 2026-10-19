<?php

namespace Tests\Feature;

use Tests\TestCase;

class ThemeRenderingTest extends TestCase
{
    public function test_homepage_renders_the_thai_literary_theme_and_identifies_its_temporary_hero_art(): void
    {
        $html = view('home', ['entries' => collect()])->render();

        $this->assertStringContainsString('<html lang="th">', $html);
        $this->assertStringContainsString('บันทึกเล็ก ๆ ของการใช้ชีวิต', $html);
        $this->assertStringContainsString('ในวันที่ยังมีเรื่องให้ใส่ใจ', $html);
        $this->assertStringContainsString('บทความล่าสุด', $html);
        $this->assertStringContainsString('ภาพประกอบชั่วคราว', $html);
        $this->assertStringContainsString(asset('images/Logo-Height-75-px.png'), $html);
        $this->assertStringContainsString('ยังไม่มีบทความ', $html);
    }

    public function test_footer_hides_unconfigured_social_links(): void
    {
        config(['jocky.social' => ['instagram' => null, 'x' => null, 'email' => null]]);

        $footer = view('components.site-footer')->render();

        $this->assertStringNotContainsString('Instagram', $footer);
        $this->assertStringNotContainsString('>X<', $footer);
        $this->assertStringNotContainsString('mailto:', $footer);
        $this->assertStringContainsString('บทความ', $footer);
    }
}
