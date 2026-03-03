<?php

namespace App\Services;

use App\Exceptions\CannotPlayDistanceException;
use App\Exceptions\InvalidCardTypeException;
use App\Exceptions\InvalidHazardTargetException;
use App\Exceptions\InvalidRemedyException;
use App\Exceptions\InvalidTargetException;
use App\Exceptions\MileageExceededException;
use App\Exceptions\SafetyBlocksHazardException;
use App\Exceptions\SpeedLimitException;
use App\Exceptions\TwoHundredLimitException;
use App\Exceptions\UnresolvedHazardException;
use App\Models\Game;
use App\Models\Player;

class MoveValidationService
{
    private Game $game;

    public function validateMove(Game $game, Player $player, array $card, ?string $targetPlayerId): void
    {
        $this->game = $game;

        $cardType = $card['type'];
        $cardSubtype = $card['subtype'];

        switch ($cardType) {
            case 'distance':
                $this->validateDistanceCard($player, $card);
                break;

            case 'hazard':
                $this->validateHazardCard($player, $card, $targetPlayerId);
                break;

            case 'remedy':
                $this->validateRemedyCard($player, $card);
                break;

            case 'safety':
                // Safety cards can always be played to your own safety area at any time.
                // No restrictions — they are always legal to play.
                // (Coup Fourré timing is enforced at the game flow level, not here.)
                break;

            default:
                throw new InvalidCardTypeException("Unknown card type: {$cardType}");
        }
    }

    private function validateDistanceCard(Player $player, array $card): void
    {
        $value = (int) $card['value'];

        // Must have Roll card OR Right of Way safety on Battle Pile to play distance
        $hasRightOfWay = $player->hasSafety('right_of_way');
        $topBattleCard = $player->getTopBattleCard();

        $canRoll = ($topBattleCard !== null && $topBattleCard['subtype'] === 'roll') || $hasRightOfWay;

        if ($canRoll === false) {
            throw new CannotPlayDistanceException("You need a Roll card (or Right of Way) to play distance cards.");
        }

        // If Right of Way is active but a non-speed hazard is on top, only remedy fixes it
        // (Out of Gas, Flat Tire, Accident block even Right of Way)
        if ($hasRightOfWay && $topBattleCard !== null) {
            $blockingHazards = ['out_of_gas', 'flat_tire', 'accident'];
            if (in_array($topBattleCard['subtype'], $blockingHazards, true)) {
                throw new UnresolvedHazardException(
                    "You must remedy the {$topBattleCard['subtype']} before playing distance cards."
                );
            }
        }

        // Speed limit check: if opponent played Speed Limit and no End of Limit,
        // only 25 and 50 mile cards are allowed (Right of Way ignores speed limit too)
        if (!$hasRightOfWay && $player->hasActiveSpeedLimit()) {
            if ($value > 50) {
                throw new SpeedLimitException("Speed limit in effect: only 25 or 50 mile cards may be played.");
            }
        }

        // Cannot exceed 1000 miles total
        $currentMileage = $player->getTotalMileage();
        if ($currentMileage + $value > 1000) {
            throw new MileageExceededException(
                "Playing this card would exceed 1000 miles (current: {$currentMileage}, card: {$value})."
            );
        }

        // No more than two 200-mile cards
        if ($value === 200 && $player->getDistanceCardCount('200') >= 2) {
            throw new TwoHundredLimitException("You may not play more than two 200-mile cards.");
        }
    }

    private function validateHazardCard(Player $player, array $card, ?string $targetPlayerId): void
    {
        if ($targetPlayerId === null) {
            throw new InvalidTargetException("Hazard cards must target an opponent.");
        }

        $target = $this->getPlayer($targetPlayerId);
        if ($target === null) {
            throw new InvalidTargetException("Target player not found.");
        }

        // Cannot target yourself or your partner
        if ($player->unique_identifier === $target->unique_identifier) {
            throw new InvalidTargetException("You cannot play hazard cards against yourself or your partner.");
        }

        $subtype = $card['subtype'];

        if ($subtype === 'speed_limit') {
            // Speed Limit goes on the target's Speed Pile
            // Can be played even if target already has a Speed Limit or a hazard on Battle Pile (rules hint H)
            // But cannot play if target has Right of Way safety
            if ($target->hasSafety('right_of_way')) {
                throw new SafetyBlocksHazardException("Target's Right of Way prevents Speed Limit cards.");
            }

            return;
        }

        // stop, out_of_gas, flat_tire, accident — go on Battle Pile
        // Target must have a Roll card exposed OR Right of Way (with Right of Way, hazards other than stop/speed_limit can still be played)
        $topBattleCard = $target->getTopBattleCard();

        // Under normal circumstances, hazards go on top of Roll cards
        // With Right of Way, opponents can still play out_of_gas, flat_tire, accident (not stop)
        $targetHasRightOfWay = $target->hasSafety('right_of_way');

        if ($subtype === 'stop') {
            // Stop can only be played on Roll card; Right of Way blocks it entirely
            if ($targetHasRightOfWay) {
                throw new SafetyBlocksHazardException("Target's Right of Way prevents Stop cards.");
            }

            if ($topBattleCard === null || $topBattleCard['subtype'] !== 'roll') {
                throw new InvalidHazardTargetException("Stop can only be played on top of a Roll card.");
            }

            return;
        }

        // out_of_gas, flat_tire, accident
        // Check corresponding safety blocks
        $safetyMap = [
            'out_of_gas' => 'extra_tank',
            'flat_tire' => 'puncture_proof',
            'accident' => 'driving_ace',
        ];

        if (isset($safetyMap[$subtype]) && $target->hasSafety($safetyMap[$subtype])) {
            throw new SafetyBlocksHazardException(
                "Target's safety card prevents playing {$subtype}."
            );
        }

        // Must be played on top of a Roll card (or on Right of Way battle pile top = remedy card, which is also valid per rules)
        // "This is also the only time an opponent can play a Hazard Card directly on top of any Remedy Card other than a Roll Card"
        if ($targetHasRightOfWay) {
            // With Right of Way, these can be played on top of remedy cards too
            // Just verify the top is not already a pending unresolved hazard of these types
            $blockingHazards = ['out_of_gas', 'flat_tire', 'accident'];
            if ($topBattleCard !== null && in_array($topBattleCard['subtype'], $blockingHazards, true)) {
                throw new InvalidHazardTargetException(
                    "Cannot stack hazard on top of an unresolved hazard."
                );
            }

            return;
        }

        // Normal: must play on Roll card
        if ($topBattleCard === null || $topBattleCard['subtype'] !== 'roll') {
            throw new InvalidHazardTargetException(
                "{$subtype} must be played on top of a Roll card."
            );
        }
    }

    private function validateRemedyCard(Player $player, array $card): void
    {
        $subtype = $card['subtype'];
        $topBattleCard = $player->getTopBattleCard();
        $topSpeedCard = $player->getTopSpeedCard();

        if ($subtype === 'end_of_limit') {
            // End of Limit goes on own Speed Pile, on top of a Speed Limit card
            if ($topSpeedCard === null || $topSpeedCard['subtype'] !== 'speed_limit') {
                throw new InvalidRemedyException("End of Limit can only be played on top of a Speed Limit card.");
            }
        
            return;
        }
        
        if ($subtype === 'roll') {
            // Roll can be played to start the Battle Pile, or to recover from stop/out_of_gas (after gas)/flat_tire (after spare)/accident (after repair)
            $validTopCards = ['stop', 'out_of_gas', 'flat_tire', 'accident', 'spare_tire', 'gasoline', 'repairs'];
            // Roll can be played on null (starting) or on top of a remedy that fixed a hazard, or on a stop/hazard directly?
            // Actually: Roll starts the pile or is played after the remedy resolves the hazard.
            // Per rules: after Out of Gas -> play Gasoline THEN Roll. After Stop -> play Roll directly.
            // So Roll is valid when: pile is empty, OR top is stop, OR top is a remedy (gasoline/spare_tire/repairs) that resolved a hazard.
            $rollableOn = [null, 'stop', 'gasoline', 'spare_tire', 'repairs'];
            $topSubtype = $topBattleCard ? $topBattleCard['subtype'] : null;
            if (in_array($topSubtype, $rollableOn, true) === false) {
                throw new InvalidRemedyException(
                    "Roll cannot be played on top of '{$topSubtype}'."
                );
            }

            return;
        }

        // gasoline, spare_tire, repairs — must match the hazard on top
        $remedyToHazard = [
            'gasoline' => 'out_of_gas',
            'spare_tire' => 'flat_tire',
            'repairs' => 'accident',
        ];

        if (isset($remedyToHazard[$subtype]) === false) {
            throw new InvalidRemedyException("Unknown remedy subtype: {$subtype}");
        }

        $requiredHazard = $remedyToHazard[$subtype];
        $topSubtype = $topBattleCard ? $topBattleCard['subtype'] : null;

        if ($topSubtype !== $requiredHazard) {
            throw new InvalidRemedyException(
                "{$subtype} can only be played on top of a {$requiredHazard} card (top is: " . ($topSubtype ?? 'none') . ")."
            );
        }
    }

    private function getPlayer(string $playerUuid): Player
    {
        return $this->game->players->where('unique_identifier', $playerUuid)->firstOrFail();
    }

}
