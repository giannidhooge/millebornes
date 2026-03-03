<?php

use App\Http\Controllers\Lobby\LobbyCreateController;
use App\Http\Controllers\Lobby\LobbyJoinController;
use App\Http\Controllers\Lobby\LobbyShowController;
use App\Models\Lobby;
use Illuminate\Support\Facades\Route;

Route::post('/lobbies', LobbyCreateController::class)->name('lobbies.store');
Route::get('/lobbies/{lobby:code}', LobbyShowController::class)->name('lobbies.show')->can('view,lobby', Lobby::class);
Route::post('/lobbies/join', LobbyJoinController::class)->name('lobbies.join');
