<x-layouts.app :title="config('jocky.home_title').' — JOCKY LIFE IN CIRCLE'" :description="config('jocky.home_intro')">
    <section class="home-hero" aria-labelledby="home-title">
        <div class="home-hero-inner page-width">
            <div class="hero-copy">
                <x-eyebrow><span lang="en">A PERSONAL WRITER'S JOURNAL</span></x-eyebrow>
                <h1 id="home-title">
                    {{ config('jocky.home_title') }}
                    <span>{{ config('jocky.home_secondary_title') }}</span>
                </h1>
                <p class="hero-description">{{ config('jocky.home_intro') }}</p>
                <a class="hero-button" href="{{ route('writing.index') }}">อ่านบทความ <span aria-hidden="true">→</span></a>
            </div>

            <div class="hero-visual" role="img" aria-label="ภาพประกอบชั่วคราว บรรยากาศริมหน้าต่างพร้อมหนังสือและกาแฟ รอภาพถ่ายจากเจ้าของ">
                <div class="hero-window" aria-hidden="true"></div>
                <div class="hero-leaf-shadow" aria-hidden="true"></div>
                <div class="hero-table" aria-hidden="true"></div>
                <div class="hero-book" aria-hidden="true"></div>
                <div class="hero-vase" aria-hidden="true"></div>
                <div class="hero-cup" aria-hidden="true"></div>
                <span class="hero-placeholder-label">ภาพประกอบชั่วคราว · รอภาพถ่ายจากเจ้าของ</span>
            </div>

            <aside class="hero-note" aria-label="ถ้อยคำประจำบันทึก">บางเรื่อง<br>อาจเล็กน้อย<br>แต่ก็มีความหมาย<br>เสมอ<span aria-hidden="true"></span></aside>
        </div>
    </section>

    <section class="latest-section page-width" aria-labelledby="latest-heading">
        <div class="section-heading">
            <h2 id="latest-heading">บทความล่าสุด</h2>
            <a class="text-link" href="{{ route('writing.index') }}">ดูบทความทั้งหมด <span aria-hidden="true">→</span></a>
        </div>

        @if ($entries->isNotEmpty())
            <div @class(['article-grid', 'article-grid--'.$entries->count()])>
                <x-editorial-card :entry="$entries->first()" featured />
                @foreach ($entries->skip(1) as $entry)
                    <x-editorial-card :entry="$entry" />
                @endforeach
            </div>
        @else
            <p class="empty-note">ยังไม่มีบทความ</p>
        @endif
    </section>
</x-layouts.app>
