<?php

namespace App\Http\Controllers\Lobby;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lobby\LobbyCreateRequest;
use App\Models\Lobby;
use App\Models\Player;
use App\Services\MilleBornesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class LobbyCreateController extends Controller
{
    private MilleBornesService $gameService;

    public function __construct(MilleBornesService $gameService)
    {
        $this->gameService = $gameService;
    }

    public function __invoke(LobbyCreateRequest $request): RedirectResponse
    {
        $name = $request->input(LobbyCreateRequest::NAME);

        $lobby = Lobby::create([
            'code' => strtoupper(Str::random(4)),
        ]);

        $game = $this->gameService->createGame($lobby);

        $player = Player::create([
            'name' => $name,
            'game_id' => $game->id,
            'unique_identifier' => Str::uuid()->toString(),
            'hand' => [],
            'battle_pile' => [],
            'distance_pile' => [],
            'safeties' => [],
            'speed_pile' => [],
            'is_host' => true,
        ]);

        session(['player_id' => $player->id]);

        return redirect()->route('lobbies.show', $lobby->code);
    }
}
