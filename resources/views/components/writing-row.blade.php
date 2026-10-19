@props(['entry'])
<article @class(['writing-row', 'writing-row--with-image' => $entry->cover_image])>
    @if ($entry->cover_image)
        <a class="writing-row-image" href="{{ route('writing.show', $entry) }}" tabindex="-1" aria-hidden="true"><img src="{{ route('writing.cover', $entry) }}" alt="" loading="lazy" decoding="async"></a>
    @endif
    <div class="writing-meta"><span>{{ $entry->category?->name ?? 'บทความ' }}</span><x-thai-date :date="$entry->published_at" /></div>
    <div class="writing-copy"><h2><a href="{{ route('writing.show', $entry) }}">{{ $entry->title }}</a></h2><p>{{ $entry->excerpt }}</p></div>
    <a class="row-arrow" href="{{ route('writing.show', $entry) }}" aria-label="อ่านบทความ: {{ $entry->title }}">↗</a>
</article>
