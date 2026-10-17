@props(['title' => 'jocky. — บันทึกส่วนตัว', 'description' => null])
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f5ef">
    <meta name="description" content="{{ $description ?: 'บันทึกเรื่องราวระหว่างทางของชีวิต ความคิด และสิ่งเล็ก ๆ ที่อยากเก็บไว้' }}">
    <meta property="og:locale" content="th_TH">
    <meta property="og:site_name" content="jocky.">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description ?: 'บันทึกเรื่องราวระหว่างทางของชีวิต ความคิด และสิ่งเล็ก ๆ ที่อยากเก็บไว้' }}">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@400;500;600&family=Newsreader:wght@400;500&family=Noto+Serif+Thai:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body><a class="skip-link" href="#main">ข้ามไปยังเนื้อหา</a><div class="site-shell"><x-site-header /><main id="main">{{ $slot }}</main><x-site-footer /></div></body>
</html>
