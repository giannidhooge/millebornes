<?php

namespace App\Http\Controllers\Lobby;

use App\Events\PlayerJoinsLobby;
use App\Http\Controllers\Controller;
use App\Http\Requests\Lobby\LobbyJoinRequest;
use App\Models\Lobby;
use App\Models\Player;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class LobbyJoinController extends Controller
{
    public function __invoke(LobbyJoinRequest $request): RedirectResponse
    {
        $code = $request->input(LobbyJoinRequest::CODE);
        $name = $request->input(LobbyJoinRequest::NAME);

        $lobby = Lobby::where('code', $code)->firstOrFail();

        $player = Player::create([
            'name' => $name,
            'game_id' => $lobby->game->id,
            'unique_identifier' => Str::uuid()->toString(),
            'hand' => [],
            'battle_pile' => [],
            'distance_pile' => [],
            'safeties' => [],
            'speed_pile' => [],
            'is_host' => false,
        ]);

        session(['player_id' => $player->id]);

        event(new PlayerJoinsLobby($lobby, $player));

        return redirect()->route('lobbies.show', $lobby->code);
    }
}
