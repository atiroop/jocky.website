<?php

use App\Models\Article;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    $entries = Article::published()->with('category')->latest('published_at')->take(3)->get();

    return view('home', compact('entries'));
})->name('home');

Route::get('/writing', function () {
    $query = Article::published()->with('category')->latest('published_at');
    $term = trim((string) request('q', ''));

    if ($term !== '') {
        $query->where(fn ($articles) => $articles->where('title', 'like', "%{$term}%")
            ->orWhere('excerpt', 'like', "%{$term}%"));
    }

    return view('writing.index', ['entries' => $query->paginate(12)->withQueryString()]);
})->name('writing.index');

Route::get('/writing/{article:slug}', function (Article $article) {
    abort_unless($article->status === 'published' && $article->published_at?->isPast(), 404);

    return view('writing.show', ['entry' => $article->load(['category', 'tags'])]);
})->name('writing.show');

Route::get('/writing/{article:slug}/cover', function (Article $article) {
    abort_unless($article->status === 'published' && $article->published_at?->isPast(), 404);
    abort_unless(is_string($article->cover_image) && preg_match('#\\Aarticle-covers/[A-Za-z0-9-]+\\.(?:jpe?g|png|webp)\\z#i', $article->cover_image), 404);

    try {
        abort_unless(Storage::disk('r2_media')->exists($article->cover_image), 404);

        return Storage::disk('r2_media')->response($article->cover_image, headers: ['Cache-Control' => 'public, max-age=300']);
    } catch (\Throwable) {
        abort(404);
    }
})->name('writing.cover');

Route::view('/about', 'about')->name('about');
