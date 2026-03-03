<?php

namespace App\Services;

use App\Models\Enums\CardType;
use App\Models\Game;
use App\Models\Lobby;
use App\Models\Player;
use Illuminate\Support\Str;

class MilleBornesService
{
    public function createGame(Lobby $lobby): Game
    {
        $deck = $this->getDeck();
        shuffle($deck);

        $game = Game::create([
            'unique_identifier' => Str::uuid()->toString(),
            'lobby_id' => $lobby->id,
            'deck' => $deck,
            'discard_pile' => [],
            'status' => 'pending',
            'current_player_id' => null,
        ]);

        return $game;
    }

    public function startGame(Game $game): Game
    {
        $deck = $game->deck;

        $game->players->each(function(Player $player) use (&$deck) {
            $hand = array_splice($deck, 0, 6);
            $player->hand = $hand;
            $player->save();
        });

        $game->deck = $deck;
        $game->status = 'active';
        $game->current_player_id = $game->players()->first()->id;
        $game->save();

        return $game;
    }

    private function getDeck(): array
    {
        $cards = [];
        $add = function (int $count, string $type, string $subtype, int $value, string $label) use (&$cards) {
            for ($i = 0; $i < $count; $i++) {
                $cards[] = compact('type', 'subtype', 'value', 'label');
            }
        };

        // Distance Cards
        $add(10, CardType::CARD_TYPE_DISTANCE, '25', 25, '25km');
        $add(10, CardType::CARD_TYPE_DISTANCE, '50', 50, '50km');
        $add(10, CardType::CARD_TYPE_DISTANCE, '75', 75, '75km');
        $add(12, CardType::CARD_TYPE_DISTANCE, '100', 100, '100km');
        $add(4, CardType::CARD_TYPE_DISTANCE, '200', 200, '200km');

        // Hazards
        $add(3, CardType::CARD_TYPE_HAZARD, CardType::HAZARD_ACCIDENT, 0, 'Accident');
        $add(3, CardType::CARD_TYPE_HAZARD, CardType::HAZARD_OUT_OF_GAS, 0, 'Out of Gas');
        $add(3, CardType::CARD_TYPE_HAZARD, CardType::HAZARD_FLAT_TIRE, 0, 'Flat Tire');
        $add(4, CardType::CARD_TYPE_HAZARD, CardType::HAZARD_SPEED_LIMIT, 0, 'Speed Limit');
        $add(5, CardType::CARD_TYPE_HAZARD, CardType::HAZARD_STOP, 0, 'Stop');

        // Remedies
        $add(6, CardType::CARD_TYPE_REMEDY, CardType::REMEDY_REPAIRS, 0, 'Repairs');
        $add(6, CardType::CARD_TYPE_REMEDY, CardType::REMEDY_GASOLINE, 0, 'Gasoline');
        $add(6, CardType::CARD_TYPE_REMEDY, CardType::REMEDY_SPARE_TIRE, 0, 'Spare Tire');
        $add(6, CardType::CARD_TYPE_REMEDY, CardType::REMEDY_END_OF_LIMIT, 0, 'End of Limit');
        $add(14, CardType::CARD_TYPE_REMEDY, CardType::REMEDY_ROLL, 0, 'Roll');

        // Safeties
        $add(1, CardType::CARD_TYPE_SAFETY, CardType::SAFETY_DRIVING_ACE, 0, 'Driving Ace');
        $add(1, CardType::CARD_TYPE_SAFETY, CardType::SAFETY_EXTRA_TANK, 0, 'Extra Tank');
        $add(1, CardType::CARD_TYPE_SAFETY, CardType::SAFETY_PUNCTURE_PROOF, 0, 'Puncture Proof');
        $add(1, CardType::CARD_TYPE_SAFETY, CardType::SAFETY_RIGHT_OF_WAY, 0, 'Right of Way');

        return $cards;
    }
}