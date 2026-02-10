<?php

use App\Models\Game;
use App\Models\Lobby;
use App\Models\Player;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('lobby.{code}', function (Player $player, $code) {
    $lobby = Lobby::where('code', $code)->firstOrFail();
    $player = $lobby->game->players()->where('id', $player->id)->firstOrFail();
    
    return $player !== null;
}, ['guards' => ['player']]);

Broadcast::channel('game.{unique_identifier}', function (Player $player, $uniqueIdentifier) {
    $game = Game::where('unique_identifier', $uniqueIdentifier)->firstOrFail();
    $player = $game->players()->where('id', $player->id)->firstOrFail();
    
    return $player !== null;
}, ['guards' => ['player']]);
