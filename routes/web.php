<?php

use Illuminate\Support\Facades\Route;

Route::pattern('locale', implode('|', array_keys(config('portfolio.locales'))));

Route::redirect('/', '/'.config('portfolio.default_locale'));

Route::get('{locale}', fn () => view('pages.home'))->name('home');
Route::get('{locale}/projects', fn () => view('pages.projects'))->name('projects');
Route::get('{locale}/projects/{slug}', function (string $locale, string $slug) {
    abort_unless(config("content.projects.$slug"), 404);

    return view('pages.project', ['slug' => $slug]);
})->name('project');
Route::get('{locale}/lab', fn () => view('pages.lab'))->name('lab');
Route::get('{locale}/about', fn () => view('pages.about'))->name('about');
Route::get('{locale}/contact', fn () => view('pages.contact'))->name('contact');
