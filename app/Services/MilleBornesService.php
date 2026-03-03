<?php

namespace App\Services;

use App\Models\Enums\CardType;
use App\Models\Game;
use App\Models\Lobby;
use App\Models\Player;
use Illuminate\Support\Str;
use Exception;

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

        $players = $game->players->each(function(Player $player) use (&$deck) {
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

    public function drawCard(Game $game): void
    {
        $deck = $game->deck;
        if (empty($deck)) {
            $deck = $game->discard_pile;
            shuffle($deck);
            $game->discard_pile = [];
        }

        $card = array_shift($deck);
        $game->deck = $deck;

        $currentPlayer = $game->currentPlayer;
        $hand = $currentPlayer->hand;
        $hand[] = $card;
        $currentPlayer->hand = $hand;
        $currentPlayer->save();

        $game->save();
    }

    public function playCard(Player $player, int $cardIndex, ?int $targetPlayerId = null): void
    {
        $hand = $player->hand;
        $card = $hand[$cardIndex] ?? null;

        if ($card === null) {
            throw new Exception('Card not in hand.');
        }

        // Validation logic here...
        $this->validateMove($player, $card, $targetPlayerId);

        array_splice($hand, $cardIndex, 1);
        $player->hand = $hand;

        switch ($card['type']) {
            case self::CARD_TYPE_DISTANCE:
                $player->distance += $card['value'];
                break;
            case self::CARD_TYPE_HAZARD:
                $targetPlayer = Player::find($targetPlayerId);
                $hazards = $targetPlayer->active_hazards;
                $hazards[] = $card['subtype'];
                $targetPlayer->active_hazards = $hazards;
                $targetPlayer->save();
                break;
            case self::CARD_TYPE_REMEDY:
                $hazards = $player->active_hazards;
                // Logic to remove the corresponding hazard
                $player->active_hazards = $hazards;
                break;
            case self::CARD_TYPE_SAFETY:
                $permanents = $player->permanents;
                $permanents[] = $card['subtype'];
                $player->permanents = $permanents;
                // Logic for coup fourre
                break;
        }

        $player->save();

        $game = $player->game;
        $discardPile = $game->discard_pile;
        $discardPile[] = $card;
        $game->discard_pile = $discardPile;
        $game->save();

        $this->nextTurn($game);
    }

    public function discardCard(Player $player, int $cardIndex): void
    {
        $hand = $player->hand;
        if (!isset($hand[$cardIndex])) {
            throw new Exception('Card not in hand.');
        }
        $card = $hand[$cardIndex];

        array_splice($hand, $cardIndex, 1);
        $player->hand = $hand;
        $player->save();

        $game = $player->game;
        $discardPile = $game->discard_pile;
        $discardPile[] = $card;
        $game->discard_pile = $discardPile;
        $game->save();

        $this->nextTurn($game);
    }

    private function validateMove(Player $player, array $card, ?int $targetPlayerId): void
    {
        // Extensive validation logic will be implemented here
    }

    private function nextTurn(Game $game): void
    {
        $players = $game->players()->get();
        $currentPlayerIndex = $players->search(fn ($p) => $p->id === $game->current_player_id);
        $nextPlayerIndex = ($currentPlayerIndex + 1) % $players->count();
        $game->current_player_id = $players[$nextPlayerIndex]->id;
        $game->save();
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