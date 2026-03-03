import type { Player } from '@/models/Player';
import type { Card } from '@/models/Card';

export interface Game {
    unique_identifier: number;
    current_player: Player;
    players: Player[];
    discard_pile: Card[];
    deck_size: number;
    last_discard_card: Card;
    last_targeted_player: Player;
}