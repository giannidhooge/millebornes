<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\LobbyController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
        // dd(auth()->user());

    return Inertia::render('Welcome');
})->name('welcome');

Route::get('/lobbies/create', [LobbyController::class, 'create'])->name('lobbies.create');
Route::post('/lobbies', [LobbyController::class, 'store'])->name('lobbies.store');
Route::get('/lobbies/{code}', [LobbyController::class, 'show'])->name('lobbies.show');
Route::post('/join', [LobbyController::class, 'join'])->name('lobbies.join');

Route::post('/games/{roomCode}/start', [GameController::class, 'start'])->name('games.start');
Route::get('/games/{game}', [GameController::class, 'show'])->name('games.show');
Route::post('/games/{game}/action', [GameController::class, 'action'])->name('games.action');


require __DIR__.'/settings.php';
