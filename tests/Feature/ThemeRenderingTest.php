<?php

namespace Tests\Feature;

use Tests\TestCase;

class ThemeRenderingTest extends TestCase
{
    public function test_homepage_renders_the_thai_literary_theme_and_identifies_its_temporary_hero_art(): void
    {
        config([
            'jocky.home_hero_image' => null,
            'jocky.home_hero_image_small' => null,
            'jocky.home_hero_fallback' => null,
            'jocky.home_hero_alt' => null,
        ]);

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

    public function test_owner_hero_image_can_replace_the_placeholder_through_configuration(): void
    {
        config([
            'jocky.home_hero_image' => 'images/owner-hero.webp',
            'jocky.home_hero_image_small' => 'images/owner-hero-800.webp',
            'jocky.home_hero_fallback' => 'images/owner-hero.jpg',
            'jocky.home_hero_alt' => 'แสงเช้าบนโต๊ะไม้พร้อมสมุดบันทึก',
        ]);

        $html = view('home', ['entries' => collect()])->render();

        $this->assertStringContainsString(asset('images/owner-hero.webp').' 1600w', $html);
        $this->assertStringContainsString(asset('images/owner-hero-800.webp').' 800w', $html);
        $this->assertStringContainsString(asset('images/owner-hero.jpg'), $html);
        $this->assertStringContainsString('alt="แสงเช้าบนโต๊ะไม้พร้อมสมุดบันทึก"', $html);
        $this->assertStringContainsString('fetchpriority="high"', $html);
        $this->assertStringNotContainsString('ภาพประกอบชั่วคราว · รอภาพถ่ายจากเจ้าของ', $html);
    }
}
