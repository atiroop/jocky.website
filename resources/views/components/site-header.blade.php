<header class="site-header">
    <a class="brand-link" href="{{ route('home') }}" aria-label="JOCKY LIFE IN CIRCLE — หน้าแรก">
        <img class="brand-logo" src="{{ asset('images/Logo-Height-75-px.png') }}" width="115" height="75" alt="JOCKY LIFE IN CIRCLE">
    </a>

    <nav class="primary-nav" aria-label="เมนูหลัก">
        <a @class(['active' => request()->routeIs('home')]) @if (request()->routeIs('home')) aria-current="page" @endif href="{{ route('home') }}">หน้าแรก</a>
        <a @class(['active' => request()->routeIs('writing*')]) @if (request()->routeIs('writing*')) aria-current="page" @endif href="{{ route('writing.index') }}">บทความ</a>
        <a @class(['active' => request()->routeIs('about')]) @if (request()->routeIs('about')) aria-current="page" @endif href="{{ route('about') }}">เกี่ยวกับ</a>
        <a class="search-link" href="{{ route('writing.index') }}#article-search" aria-label="ค้นหาบทความ" title="ค้นหาบทความ">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="10.8" cy="10.8" r="6.8"></circle><path d="m16 16 5 5"></path></svg>
        </a>
    </nav>
</header>
