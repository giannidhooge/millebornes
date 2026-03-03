<?php

use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::post('/games/{roomCode}/start', [GameController::class, 'start'])->name('games.start');
Route::get('/games/{game}', [GameController::class, 'show'])->name('games.show');
Route::post('/games/{game}/action', [GameController::class, 'action'])->name('games.action');

