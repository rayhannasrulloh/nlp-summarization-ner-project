<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NewsBotController;
use Illuminate\Support\Facades\Route;

// Landing Page (Pure HTML)
Route::get('/', function () {
    return view('welcome');
});

// Guest Access to Bot
Route::get('/news-bot', [NewsBotController::class, 'index'])->name('news.index');
Route::post('/news-bot/process', [NewsBotController::class, 'process'])->name('news.process');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard/{history?}', [NewsBotController::class, 'dashboard'])->name('dashboard');
    Route::delete('/history/{history}', [NewsBotController::class, 'destroy'])->name('history.destroy');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
