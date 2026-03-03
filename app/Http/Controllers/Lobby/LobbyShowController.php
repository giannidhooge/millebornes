<?php

namespace App\Http\Controllers\Lobby;

use Auth;
use App\Http\Controllers\Controller;
use App\Models\Lobby;
use Inertia\Inertia;
use Inertia\Response;

class LobbyShowController extends Controller
{
    public function __invoke(Lobby $lobby): Response
    {
        return Inertia::render('game/Lobby', [
            'lobby' => $lobby,
            'player' => Auth::user()->toArray(),
        ]);
    }
}
