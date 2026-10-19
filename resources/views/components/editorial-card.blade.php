@props(['entry', 'featured' => false])

<article @class(['article-card', 'article-card--featured' => $featured])>
    @if ($entry->cover_image)
        <a class="article-card-image-link" href="{{ route('writing.show', $entry) }}" tabindex="-1" aria-hidden="true">
            <img class="article-card-image" src="{{ route('writing.cover', $entry) }}" alt="" loading="lazy" decoding="async">
        </a>
    @endif
    <div class="article-card-meta">
        <span>{{ $entry->category?->name ?? 'บทความ' }}</span>
        <span class="meta-divider" aria-hidden="true"></span>
        <x-thai-date :date="$entry->published_at" />
    </div>
    <h3><a href="{{ route('writing.show', $entry) }}">{{ $entry->title }}</a></h3>
    <p class="article-card-excerpt">{{ $entry->excerpt }}</p>
    <a class="article-card-read-more" href="{{ route('writing.show', $entry) }}">อ่านต่อ <span aria-hidden="true">→</span><span class="visually-hidden">: {{ $entry->title }}</span></a>
</article>
