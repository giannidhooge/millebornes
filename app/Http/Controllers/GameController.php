<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use App\Models\Lobby;
use App\Services\MilleBornesService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Auth;
use App\Events\GameStart;
use App\Events\SwitchTurn;
use App\Http\Resources\GameResource;

class GameController extends Controller
{
    protected $gameService;

    public function __construct(MilleBornesService $gameService)
    {
        $this->gameService = $gameService;
    }

    public function start(Request $request, string $lobbyCode)
    {
        $lobby = Lobby::where('code', $lobbyCode)->firstOrFail();
        
        $game = $this->gameService->startGame($lobby->game);

        event(new GameStart($game));

        return redirect()->route('games.show', $game->unique_identifier);
    }

    public function show(Game $game)
    {
        $game->load('players', 'currentPlayer');

        return Inertia::render('game/Board', [
            'player' => Auth::user()->toArray(),
            'game' => GameResource::make($game),
        ]);
    }

    public function action(Request $request, Game $game)
    {
        $player = $game->players()->where('id', Auth::id())->firstOrFail();

        $action = $request->input('action');
        $cardIndex = $request->input('card_index');
        $targetPlayerId = $request->input('target_player_id');

        if ($action === 'draw') {
            $this->gameService->drawCard($game);

            return back();
        }

        match ($action) {
            'play' => $this->gameService->playCard($player, $cardIndex, $targetPlayerId),
            'discard' => $this->gameService->discardCard($player, $cardIndex),
            default => throw new Exception('Invalid action.'),
        };
    
        event(new SwitchTurn($game));

        return back();
    }
}