<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/news-bot', [App\Http\Controllers\NewsBotController::class, 'index'])->name('news.index');
Route::post('/news-bot/process', [App\Http\Controllers\NewsBotController::class, 'process'])->name('news.process');
