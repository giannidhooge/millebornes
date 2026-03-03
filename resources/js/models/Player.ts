import type { Card } from "./Card";

export interface Player {
    is_host: boolean;
    name: string;
    unique_identifier: string;
    distance: number;
    safeties: Card[];
    battle_pile: Card[];
    speed_pile: Card[];
    last_battle_pile_card: Card;
    last_speed_pile_card: Card;
    hand: Card[];
}