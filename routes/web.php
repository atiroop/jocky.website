<?php

use Illuminate\Support\Facades\Route;

$entries = [
    ['slug' => 'the-shape-of-a-morning', 'title' => 'The shape of a morning', 'date' => '17 October 2026', 'category' => 'Notes', 'excerpt' => 'On the small rituals that make a day feel like it belongs to you.'],
    ['slug' => 'a-language-for-rain', 'title' => 'A language for rain', 'date' => '04 October 2026', 'category' => 'Observations', 'excerpt' => 'Listening to a familiar city change its voice when the clouds gather.'],
    ['slug' => 'the-things-we-keep', 'title' => 'The things we keep', 'date' => '21 September 2026', 'category' => 'Reflections', 'excerpt' => 'A few thoughts on memory, ordinary objects, and what they hold.'],
];

Route::get('/', fn () => view('home', ['entries' => $entries]))->name('home');
Route::get('/writing', fn () => view('writing.index', ['entries' => $entries]))->name('writing.index');
Route::get('/writing/{slug}', function (string $slug) use ($entries) {
    $entry = collect($entries)->firstWhere('slug', $slug);
    abort_if($entry === null, 404);
    return view('writing.show', ['entry' => $entry]);
})->name('writing.show');
Route::view('/about', 'about')->name('about');
