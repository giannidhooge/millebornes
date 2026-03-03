<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { start, show } from '@/routes/games';
import { useEcho } from "@laravel/echo-vue";
import GameLayout from "@/layouts/GameLayout.vue";

const props = defineProps<{
    player: any,
    lobby: {
        code: string;
        game: {
            players: any[];
        };
    };
}>();

const players = ref(props.lobby.game.players || []);
const isHost = ref(false);

const form = useForm({ players: [] });

useEcho(`lobby.${props.lobby.code}`, "PlayerJoinsLobby", (e) => {
    players.value.push(e.player);
});

useEcho(`lobby.${props.lobby.code}`, "GameStart", (e) => {
    router.visit(show(e.game.unique_identifier));
});

onMounted(() => {
    isHost.value = props.player?.is_host;
});

const startGame = () => {
    form.players = players.value;
    form.post(start(props.lobby.code));
};
</script>

<template>
    <Head :title="`Lobby — ${lobby.code}`" />

    <GameLayout>
        <div class="flex items-center justify-center w-full h-full">
            <!-- Lobby card -->
            <div class="relative z-10 w-full max-w-md">

                <!-- Gold border frame -->
                <div class="border border-yellow-800/60 rounded-2xl overflow-hidden shadow-[0_8px_48px_rgba(0,0,0,0.7),0_0_32px_rgba(201,168,76,0.08)]">

                    <!-- Header -->
                    <div class="bg-green-950 border-b border-yellow-800/40 px-8 py-6 text-center">
                        <div class="flex items-baseline justify-center gap-2 mb-1">
                            <span class="font-display text-2xl font-black tracking-widest uppercase text-yellow-300">Mille Bornes</span>
                        </div>
                        <p class="text-yellow-800 text-[10px] tracking-[0.3em] uppercase font-serif mt-1">Game Lobby</p>
                    </div>

                    <!-- Body -->
                    <div class="bg-black/20 px-8 py-6">

                        <!-- Lobby code -->
                        <div class="text-center mb-6">
                            <p class="text-[10px] tracking-[0.3em] uppercase text-yellow-800 font-serif mb-2">Lobby Code</p>
                            <div class="inline-flex items-center gap-3 px-6 py-3 border-2 border-yellow-800/60 rounded-xl bg-black/30">
                                <span class="font-display text-3xl font-black tracking-[0.2em] text-yellow-300">{{ lobby.code }}</span>
                            </div>
                            <p class="text-yellow-800/60 text-[10px] tracking-wide mt-2">Share this code with your friends</p>
                        </div>

                        <!-- Divider -->
                        <div class="border-t border-yellow-800/20 mb-5"></div>

                        <!-- Players list -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-[10px] tracking-[0.3em] uppercase text-yellow-800 font-serif">Players</p>
                                <span class="text-[10px] tracking-wide text-yellow-700 font-semibold">{{ players.length }} joined</span>
                            </div>

                            <ul class="flex flex-col gap-2">
                                <li
                                    v-for="p in players"
                                    :key="p.unique_identifier"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-yellow-800/20 bg-black/25"
                                >
                                    <!-- Avatar dot -->
                                    <div class="w-7 h-7 rounded-full bg-yellow-800/30 border border-yellow-700/40 flex items-center justify-center text-xs font-bold text-yellow-400 flex-shrink-0">
                                        {{ p.name?.[0]?.toUpperCase() }}
                                    </div>
                                    <span class="text-sm font-semibold text-yellow-100 font-serif">{{ p.name }}</span>
                                    <span v-if="p.is_host"
                                          class="ml-auto text-[9px] uppercase tracking-wide px-1.5 py-0.5 rounded bg-yellow-800/40 text-yellow-400 font-bold border border-yellow-700/30">
                                        Host
                                    </span>
                                    <span v-else-if="p.unique_identifier === player.unique_identifier"
                                          class="ml-auto text-[9px] uppercase tracking-wide text-yellow-800">
                                        You
                                    </span>
                                </li>

                                <!-- Waiting slots -->
                                <li v-if="players.length < 2"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-dashed border-yellow-800/20 opacity-40">
                                    <div class="w-7 h-7 rounded-full border border-dashed border-yellow-800/40 flex-shrink-0"></div>
                                    <span class="text-xs text-yellow-800 tracking-wide">Waiting for player…</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Action area -->
                        <div v-if="isHost">
                            <button
                                @click="startGame"
                                :disabled="players.length < 2 || form.processing"
                                class="w-full py-3 rounded-xl font-bold text-sm uppercase tracking-widest transition-all duration-200 cursor-pointer disabled:opacity-35 disabled:cursor-not-allowed"
                                :class="players.length >= 2
                                    ? 'bg-gradient-to-br from-yellow-700 to-yellow-500 text-yellow-950 hover:opacity-85 shadow-[0_0_20px_rgba(201,168,76,0.2)]'
                                    : 'bg-yellow-900/30 text-yellow-800 border border-yellow-800/30'"
                            >
                                {{ form.processing ? 'Starting…' : 'Start Game' }}
                            </button>
                            <p v-if="players.length < 2" class="text-center text-[10px] text-yellow-800 tracking-wide mt-2 opacity-60">
                                Need at least 2 players to start
                            </p>
                        </div>

                        <div v-else class="flex items-center justify-center gap-2 py-3">
                            <span class="w-1.5 h-1.5 rounded-full bg-yellow-600 animate-pulse"></span>
                            <p class="text-sm text-yellow-700 tracking-wide">Waiting for the host to start…</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GameLayout>
</template>