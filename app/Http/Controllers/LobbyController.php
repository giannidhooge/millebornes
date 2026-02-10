<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Game;
use App\Models\Lobby;
use App\Models\Player;
use App\Services\MilleBornesService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Events\PlayerJoinsLobby;


class LobbyController extends Controller
{
    public function __construct(MilleBornesService $gameService)
    {
        $this->gameService = $gameService;
    }

    public function create()
    {
        return Inertia::render('Game/CreateLobby');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $lobby = Lobby::create([
            'code' => strtoupper(Str::random(4)),
        ]);

        $game = $this->gameService->createGame($lobby);

        $player = Player::create([
            'name' => $request->name,
            'game_id' => $game->id,
            'unique_identifier' => Str::uuid()->toString(),
            'hand' => [],
            'active_hazards' => [],
            'permanents' => [],
            'is_host' => true,
        ]);
        
        session(['player_id' => $player->id]);

        return redirect()->route('lobbies.show', $lobby->code);
    }

    public function show(string $code)
    {
        $lobby = Lobby::where('code', $code)->with('game.players')->firstOrFail();

        return Inertia::render('game/Lobby', [
            'lobby' => $lobby,
            'player' => Auth::user()->toArray(),
        ]);
    }

    public function join(Request $request)
    {
        $request->validate([
            'code' => 'required|string|exists:lobbies,code',
            'name' => 'required|string|max:255',
        ]);

        $lobby = Lobby::where('code', $request->code)->firstOrFail();

        $player = Player::create([
            'name' => $request->name,
            'game_id' => $lobby->game->id,
            'unique_identifier' => Str::uuid()->toString(),
            'hand' => [],
            'active_hazards' => [],
            'permanents' => [],
            'is_host' => false,
        ]);

        session(['player_id' => $player->id]);

        event(new PlayerJoinsLobby($lobby, $player));

        return redirect()->route('lobbies.show', $lobby->code);
    }
}
