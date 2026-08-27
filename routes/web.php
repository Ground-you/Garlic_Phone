<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\DiscordController;
use App\Http\Controllers\LobbyController;
use App\Http\Controllers\GameController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

//홈 인덱스
Route::get('/', [HomeController::class, 'index'])->name('home');

//디스코드 연동
Route::prefix('auth/discord')->group(function () {
    // 1. 연동하기 버튼 누르면 디스코드 창으로 보내는 주소
    Route::get('/redirect', [DiscordController::class, 'redirectToDiscord'])->name('auth.discord.redirect');
    // 2. 디스코드에서 인증 후 돌아오는 콜백 주소
    Route::get('/callback', [DiscordController::class, 'handleDiscordCallback'])->name('auth.discord.callback');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->to('http://127.0.0.1:8000');
})->name('logout');


Route::post('/upload-avatar', [LobbyController::class, 'uploadAvatar']);
Route::post('/lobby', [LobbyController::class, 'store'])->name('lobby.store');
Route::get('/lobby/{id}', [LobbyController::class, 'show'])->name('lobby.show');
Route::post('/lobby/{code}/chat', [LobbyController::class, 'chat'])->name('lobby.chat');
Route::patch('/lobby/{code}/toggle-chat', [LobbyController::class, 'toggleChat'])->name('lobby.toggle-chat');
Route::delete('/lobby/{code}/leave', [LobbyController::class, 'leave'])->name('lobby.leave');
Route::patch('/lobby/{code}/ready', [LobbyController::class, 'ready'])->name('lobby.ready');
Route::post('/lobby/{code}/start', [LobbyController::class, 'start'])->name('lobby.start');
Route::get('/game/{code}', [GameController::class, 'show'])->name('game.show');
Route::post('/game/{code}/submit-topic', [GameController::class, 'submitTopic'])->name('game.submit-topic');

require __DIR__.'/auth.php';
