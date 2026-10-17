<x-layouts.app :title="config('jocky.home_title').' — jocky.'" :description="config('jocky.home_intro')">
    <section class="home-intro page-width">
        <x-eyebrow>บันทึกส่วนตัว</x-eyebrow>
        <h1>{{ config('jocky.home_title') }}</h1>
        <div class="intro-bottom">
            <p>{{ config('jocky.home_intro') }}</p>
            <a class="text-link" href="{{ route('writing.index') }}">อ่านบทความ <span aria-hidden="true">↗</span></a>
        </div>
    </section>
    <section class="latest-section page-width" aria-labelledby="latest-heading">
        <div class="section-heading"><x-eyebrow>บันทึกล่าสุด</x-eyebrow><a class="text-link" href="{{ route('writing.index') }}">บทความทั้งหมด <span aria-hidden="true">↗</span></a></div>
        <h2 id="latest-heading" class="visually-hidden">บทความล่าสุด</h2>
        <div class="writing-list">@forelse ($entries as $entry)<x-writing-row :entry="$entry" />@empty<p class="empty-note">บทความใหม่จะปรากฏที่นี่เมื่อพร้อม</p>@endforelse</div>
    </section>
</x-layouts.app>
