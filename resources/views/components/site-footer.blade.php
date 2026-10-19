@php
    $social = config('jocky.social', []);
    $hasSocial = (filter_var($social['instagram'] ?? null, FILTER_VALIDATE_URL) && parse_url($social['instagram'], PHP_URL_SCHEME) === 'https')
        || (filter_var($social['x'] ?? null, FILTER_VALIDATE_URL) && parse_url($social['x'], PHP_URL_SCHEME) === 'https')
        || (filter_var($social['email'] ?? null, FILTER_VALIDATE_EMAIL) !== false);
@endphp

<footer @class(['site-footer', 'site-footer--without-social' => ! $hasSocial])>
    <div class="footer-brand">
        <a class="footer-brand-link" href="{{ route('home') }}" aria-label="JOCKY LIFE IN CIRCLE — หน้าแรก">
            <img class="footer-logo" src="{{ asset('images/Logo-Height-75-px.png') }}" width="115" height="75" alt="JOCKY LIFE IN CIRCLE">
        </a>
        <p>พื้นที่เล็ก ๆ สำหรับความคิด เรื่องราวระหว่างทาง<br class="desktop-break"> และถ้อยคำที่อยากเก็บไว้</p>
    </div>

    <nav class="footer-nav" aria-label="เมนูส่วนท้าย">
        <h2>เมนู</h2>
        <a href="{{ route('home') }}">หน้าแรก</a>
        <a href="{{ route('writing.index') }}">บทความ</a>
        <a href="{{ route('about') }}">เกี่ยวกับ</a>
    </nav>

    @if ($hasSocial)
        <nav class="footer-social" aria-label="ช่องทางติดตาม">
            <h2>ติดตาม</h2>
            <div class="social-links">
                @if (filter_var($social['instagram'] ?? null, FILTER_VALIDATE_URL) && parse_url($social['instagram'], PHP_URL_SCHEME) === 'https')<a href="{{ $social['instagram'] }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram">Instagram</a>@endif
                @if (filter_var($social['x'] ?? null, FILTER_VALIDATE_URL) && parse_url($social['x'], PHP_URL_SCHEME) === 'https')<a href="{{ $social['x'] }}" target="_blank" rel="noopener noreferrer" aria-label="X">X</a>@endif
                @if (filled($social['email'] ?? null) && filter_var($social['email'], FILTER_VALIDATE_EMAIL))<a href="mailto:{{ $social['email'] }}" aria-label="อีเมล">อีเมล</a>@endif
            </div>
        </nav>
    @endif

    <div class="footer-legal">
        <p>© {{ now()->year }} Jocky.<br>บันทึกส่วนตัวของผู้เขียน<br>สงวนลิขสิทธิ์</p>
    </div>
</footer>
