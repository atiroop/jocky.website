<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f5ef"><meta name="description" content="Jocky — a personal journal of thoughts, places, and the things that stay with us.">
    <title>{{ $title ?? 'Jocky — a writer’s journal' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=IBM+Plex+Sans+Thai:wght@400;500&family=Newsreader:opsz,wght@6..72,400;6..72,500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body><a class="skip-link" href="#main">Skip to content</a><div class="site-shell"><x-site-header /><main id="main">{{ $slot }}</main><x-site-footer /></div></body>
</html>
