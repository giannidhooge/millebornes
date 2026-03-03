<?php

namespace App\Services;

use App\Events\PlayerWon;
use App\Models\Game;
use App\Models\Player;
use App\Models\Enums\CardType;
use Exception;

class PlayCardService
{
    private Game $game;

    private Player $player;

    public function discardCard(Game $game, Player $player, int $cardIndex): void
    {
        $this->game = $game;
        $this->player = $player;

        $hand = $this->player->hand;
        if (!isset($hand[$cardIndex])) {
            throw new Exception('Card not in hand.');
        }
        $card = $hand[$cardIndex];

        array_splice($hand, $cardIndex, 1);
        $this->player->hand = $hand;
        $this->player->save();

        $this->nextTurn();
        
        $discardPile = $this->game->discard_pile;
        $discardPile[] = $card;
        $this->game->discard_pile = $discardPile;
        $this->game->save();
    }

    public function drawCard(Game $game): void
    {
        $this->game = $game;

        $deck = $this->game->deck;
        if (empty($deck)) {
            $deck = $this->game->discard_pile;
            shuffle($deck);
            $this->game->discard_pile = [];
        }

        $card = array_shift($deck);
        $this->game->deck = $deck;

        $currentPlayer = $this->game->currentPlayer;
        $hand = $currentPlayer->hand;
        $hand[] = $card;
        $currentPlayer->hand = $hand;
        $currentPlayer->save();

        $this->game->save();
    }

    public function coupFourre(Game $game, Player $player, int $cardIndex): void
    {
        $this->game = $game;
        $this->player = $player;

        $hand = $this->player->hand;
        $card = $hand[$cardIndex] ?? null;

        app(PlayCardValidationService::class)->validateCoupFourre($this->game, $this->player, $card);

        array_splice($hand, $cardIndex, 1);
        $this->player->hand = $hand;

        $this->player->addCardToPile($card, 'safeties');
        $this->player->addCardToPile([
            'type' => CardType::CARD_TYPE_REMEDY,
            'subtype' => CardType::REMEDY_ROLL,
            'value' => 0,
            'label' => 'Roll',
        ], 'battle_pile');
        $this->player->save();

        $this->game->last_targeted_player_id = null;
        $this->game->current_player_id = $this->player->id;
        $this->game->save();
    }

    public function playCard(Game $game, Player $player, int $cardIndex, ?string $targetPlayerId = null): void
    {
        $this->game = $game;
        $this->player = $player;

        $hand = $player->hand;
        $card = $hand[$cardIndex] ?? null;

        if ($card === null) {
            throw new Exception('Card not in hand.');
        }

        app(PlayCardValidationService::class)->validateMove($this->game, $this->player, $card, $targetPlayerId);

        array_splice($hand, $cardIndex, 1);
        $this->player->hand = $hand;

        match ($card['type']) {
            CardType::CARD_TYPE_DISTANCE => $this->playDistanceCard($card, $targetPlayerId),
            CardType::CARD_TYPE_HAZARD => $this->playHazardCard($card, $targetPlayerId),
            CardType::CARD_TYPE_REMEDY => $this->playRemedyCard($card),
            CardType::CARD_TYPE_SAFETY => $this->playSafetyCard($card),
        };
    
        $this->nextTurn();

        $this->player->save();
        $this->game->save();
    }

    private function playDistanceCard(array $card): void
    {
        $this->player->addCardToPile($card, 'distance_pile');
        $this->game->last_targeted_player_id = null;

        if ($this->player->getTotalMileage() === 1000) {
            $this->game->status = 'finished';
            $this->game->current_player_id = null;

            event(new PlayerWon($this->game, $this->player));
        }
    }

    private function playHazardCard(array $card, ?string $targetPlayerId): void
    {
        $targetPlayer = $this->game->players->where('unique_identifier', $targetPlayerId)->firstOrFail();

        $pileName = $card['subtype'] === CardType::HAZARD_SPEED_LIMIT
            ? 'speed_pile'
            : 'battle_pile';

        $targetPlayer->addCardToPile($card, $pileName);
        $targetPlayer->save();

        $this->game->last_targeted_player_id = $targetPlayer->id;
    }

    private function playRemedyCard(array $card): void
    {
        $pileName = $card['subtype'] === CardType::REMEDY_END_OF_LIMIT
            ? 'speed_pile'
            : 'battle_pile';

        $this->player->addCardToPile($card, $pileName);
        $this->game->last_targeted_player_id = null;
    }

    private function playSafetyCard(array $card): void
    {
        $this->player->addCardToPile($card, 'safeties');

        $topBattleCard = $this->player->getTopBattleCard();

        $safetyToHazard = [
            'right_of_way' => ['stop', 'speed_limit'],
            'extra_tank' => ['out_of_gas'],
            'puncture_proof' => ['flat_tire'],
            'driving_ace' => ['accident'],
        ];

        $countersHazards = $safetyToHazard[$card['subtype']];
        if ($topBattleCard && in_array($topBattleCard['subtype'], $countersHazards, true)) {
            $this->player->addCardToPile([
                'type' => CardType::CARD_TYPE_REMEDY,
                'subtype' => CardType::REMEDY_ROLL,
                'value' => 0,
                'label' => 'Roll',
            ], 'battle_pile');
        }

        $topSpeedCard = $this->player->getTopSpeedCard();
        if ($card['subtype'] === CardType::SAFETY_DRIVING_ACE && $topSpeedCard && $topSpeedCard['subtype'] === CardType::HAZARD_SPEED_LIMIT) {
            $this->player->addCardToPile([
                 'type' => CardType::CARD_TYPE_REMEDY,
                'subtype' => CardType::REMEDY_END_OF_LIMIT,
                'value' => 0,
                'label' => 'End of Limit',
            ], 'battle_pile');
        }

        $this->game->last_targeted_player_id = null;
    }

    private function nextTurn(): void
    {
        if ($this->game->status === 'finished') {
            return;
        }
                
        $players = $this->game->players()->get();
        $currentPlayerIndex = $players->search(fn ($p) => $p->id === $this->game->current_player_id);
        $nextPlayerIndex = ($currentPlayerIndex + 1) % $players->count();
        $this->game->current_player_id = $players[$nextPlayerIndex]->id;
    }
}
