<x-layouts.app :title="($entry->seo_title ?: $entry->title).' — jocky.'" :description="$entry->seo_description ?: $entry->excerpt">
    <article class="article page-width">
        <a class="back-link" href="{{ route('writing.index') }}">← กลับไปหน้าบทความ</a>
        <header class="article-header">
            <div class="article-meta"><span>{{ $entry->category?->name ?? 'บทความ' }}</span><span aria-hidden="true">·</span><x-thai-date :date="$entry->published_at" /><span aria-hidden="true">·</span><span>อ่านประมาณ {{ $entry->readingTimeMinutes() }} นาที</span></div>
            <h1>{{ $entry->title }}</h1>
            @if ($entry->subtitle)<p class="article-deck">{{ $entry->subtitle }}</p>@endif
            <p class="article-deck">{{ $entry->excerpt }}</p>
        </header>
        @if ($entry->cover_image)
            <figure class="article-cover-figure"><img class="article-cover" src="{{ route('writing.cover', $entry) }}" alt="{{ $entry->cover_image_alt ?: $entry->title }}">@if ($entry->cover_image_caption)<figcaption>{{ $entry->cover_image_caption }}</figcaption>@endif</figure>
        @endif
        <div class="article-body">{!! $entry->safeBodyHtml() !!}</div>
        @if ($previous || $next)
            <nav class="article-navigation" aria-label="บทความที่เกี่ยวข้อง">
                @if ($previous)<a href="{{ route('writing.show', $previous) }}"><span>บทความก่อนหน้า</span><strong>{{ $previous->title }}</strong></a>@else<div></div>@endif
                @if ($next)<a class="article-navigation-next" href="{{ route('writing.show', $next) }}"><span>บทความถัดไป</span><strong>{{ $next->title }}</strong></a>@endif
            </nav>
        @endif
    </article>
</x-layouts.app>
