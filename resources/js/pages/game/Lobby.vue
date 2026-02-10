<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { start, show } from '@/routes/games';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useEcho } from "@laravel/echo-vue";

const props = defineProps<{
    player: any,
    lobby: {
        code: string;
        game: {
            players: any[];
        };
    };
}>();

const page = usePage();
const players = ref(props.lobby.game.players || []);
const isHost = ref(false);

const form = useForm({
    players: [],
});

useEcho(
    `lobby.${props.lobby.code}`,
    "PlayerJoinsLobby",
    (e) => {
        console.log('message received')
        players.value.push(e.player);
    },
);

useEcho(
    `lobby.${props.lobby.code}`,
    "GameStart",
    (e) => {
        console.log(e);
        router.visit(show(e.game.unique_identifier));
    },
);

onMounted(() => {
    isHost.value = props.player?.is_host;
});

const startGame = () => {
    form.players = players.value;
    form.post(start(props.lobby.code));
};

</script>

<template>
    <Head :title="`Lobby - ${lobby.code}`" />
    <div v-if="player" class="flex min-h-screen flex-col items-center justify-center bg-gray-100 p-6 dark:bg-gray-900">
        <Card>
            <CardHeader>
                <CardTitle>Hello {{player.name}} - Lobby Code: {{ lobby.code }}</CardTitle>
                <CardDescription>Share this code with your friends to join.</CardDescription>
            </CardHeader>
            <CardContent>
                <h2 class="mb-4 text-xl font-bold">Players</h2>
                <ul>
                    <li v-for="player in players" :key="player.unique_identifier">{{ player.name }}</li>
                </ul>

                <div v-if="isHost" class="mt-8">
                    <Button @click="startGame" :disabled="players.length < 2">Start Game</Button>
                </div>
                <div v-else class="mt-8">
                    <p>Waiting for the host to start the game...</p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
