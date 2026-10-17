<x-layouts.app title="บทความ — jocky." description="รวมเรื่องราว ความคิด และบันทึกจาก jocky.">
    <section class="page-heading page-width"><x-eyebrow>คลังเรื่องราว</x-eyebrow><h1>บทความ<span class="bronze-dot">.</span></h1><p>บันทึกเรื่องราว ความคิด และสิ่งที่อยากเก็บไว้</p></section>
    <section class="archive-section page-width" aria-label="คลังบทความ">
        <form class="archive-search" method="GET" action="{{ route('writing.index') }}" role="search">
            <label class="visually-hidden" for="article-search">ค้นหาบทความ</label>
            <input id="article-search" name="q" type="search" value="{{ request('q') }}" placeholder="ค้นหาบทความ" maxlength="120">
            <button type="submit">ค้นหา</button>
        </form>
        @error('q')<p class="form-error" role="alert">{{ $message }}</p>@enderror
        <div class="writing-list">@forelse ($entries as $entry)<x-writing-row :entry="$entry" />@empty<p class="empty-note">ยังไม่มีบทความ</p>@endforelse</div>
        <div class="pagination-wrap">{{ $entries->links() }}</div>
    </section>
</x-layouts.app>
