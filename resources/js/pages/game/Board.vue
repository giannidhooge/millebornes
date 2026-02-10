<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { action } from '@/routes/games';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useEcho } from "@laravel/echo-vue";

const props = defineProps<{
    player: any;
    game: {
        unique_identifier: number;
        current_player: any;
        players: any[];
        discard_pile: any[];
    };
}>();

const form = useForm({
    action: '',
    card_index: null,
    target_player_id: null,
});


const isMyTurn = ref(false);

const isCurrentPlayer = (player) => {
    return player.id === props.player.id;
};

const checkIsMyTurn = () => {
    return props.player.unique_identifier === props.game.current_player.unique_identifier;
};

const playCard = (cardIndex: number, targetPlayerId: number | null = null) => {

    const card = props.player.hand[cardIndex];
    if (card.type === 'hazard') {
        // TODO: show model and select player (not myself)
        return;
    }

    form.action = 'play';
    form.card_index = cardIndex;
    form.target_player_id = targetPlayerId;
    form.post(action(props.game.unique_identifier));
};

const drawCard = () => {
    form.action = 'draw';
    form.post(action(props.game.unique_identifier));
};

const discardCard = (cardIndex: number) => {
    form.action = 'discard';
    form.card_index = cardIndex;
    form.post(action(props.game.unique_identifier));
};

onMounted(() => {
    isMyTurn.value = checkIsMyTurn();
});

useEcho(
    `game.${props.game.unique_identifier}`,
    "SwitchTurn",
    (e) => {
        console.log(e);
        props.game = e.game;
        isMyTurn.value = checkIsMyTurn();
    },
);

</script>

<template>
    <Head :title="`Mille Bornes - Game ${game.id}`" />
    <div v-if="game && game.current_player" class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Mille Bornes - {{ game.current_player.name }}</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Current Player Info -->
            <div class="md:col-span-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Game Board</CardTitle>
                        <CardDescription>It's {{ game.current_player.name }}'s turn.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="flex justify-around">
                            <div>
                                <h3 class="font-bold">Deck</h3>
                                <div class="w-24 h-36 bg-blue-500 rounded-lg text-white flex items-center justify-center">
                                    {{ game.deck_size }} cards
                                </div>
                            </div>
                            <div>
                                <h3 class="font-bold">Discard Pile</h3>
                                <div v-if="game.discard_pile.length > 0" class="w-24 h-36 border-2 border-gray-400 rounded-lg p-2">
                                   {{ game.discard_pile[game.discard_pile.length - 1].label }}
                                </div>
                                <div v-else class="w-24 h-36 border-2 border-dashed border-gray-400 rounded-lg flex items-center justify-center text-gray-400">
                                    Empty
                                </div>
                            </div>
                        </div>

                         <div v-if="isMyTurn" class="mt-4 text-center">
                            <Button @click="drawCard" :disabled="props.player.hand.length > 6">Draw Card</Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Players -->
            <div>
                <Card>
                    <CardHeader>
                        <CardTitle>Players</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-for="player in game.players" :key="player.id" class="mb-4 p-2 rounded-lg" :class="{'bg-blue-100': isCurrentPlayer(player) }">
                            <h3 class="font-bold">{{ player.name }} {{ isCurrentPlayer(player) ? '(You)' : '' }}</h3>
                            <p>Distance: {{ player.distance }} km</p>
                            <p>Speed Limit: {{ player.speed_limit ? 'Yes' : 'No' }}</p>
                            <div>
                                Hazards: 
                                <span v-for="hazard in player.active_hazards" :key="hazard" class="mr-2 inline-block bg-red-500 text-white rounded px-2 py-1 text-xs">{{ hazard }}</span>
                            </div>
                             <div>
                                Permanents:
                                <span v-for="permanent in player.permanents" :key="permanent" class="mr-2 inline-block bg-green-500 text-white rounded px-2 py-1 text-xs">{{ permanent }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Player Hand -->
        <div class="mt-4">
             <Card>
                <CardHeader>
                    <CardTitle>Your Hand</CardTitle>
                </CardHeader>

                <CardContent class="flex flex-wrap gap-2">
                    <div v-for="(card, index) in player?.hand" :key="index" class="w-24 h-36 border rounded-lg p-2 flex flex-col justify-between">
                       <div>{{ card.label }}</div>
                       <div v-if="isMyTurn && props.player.hand.length >= 7">
                           <Button size="sm" @click="playCard(index)">Play</Button>
                           <Button size="sm" variant="destructive" @click="discardCard(index)">Discard</Button>
                       </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
