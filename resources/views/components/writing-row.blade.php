@props(['entry'])
<article class="writing-row">
    <div class="writing-meta"><x-thai-date :date="$entry->published_at" /><span>{{ $entry->category?->name ?? 'บทความ' }}</span></div>
    <div class="writing-copy"><h2><a href="{{ route('writing.show', $entry) }}">{{ $entry->title }}</a></h2><p>{{ $entry->excerpt }}</p></div>
    <a class="row-arrow" href="{{ route('writing.show', $entry) }}" aria-label="อ่านบทความ: {{ $entry->title }}">↗</a>
</article>
