<?php

namespace App\Policies;

use App\Models\Lobby;
use App\Models\Player;

class LobbyPolicy
{
    public function view(Player $player, Lobby $lobby): bool
    {
        $lobby = $lobby->load('game.players');

        return $lobby->game->players->contains('unique_identifier', $player?->unique_identifier);
    }    
}
