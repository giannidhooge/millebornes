<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { store, join } from '@/routes/lobbies';
import GameLayout from "@/layouts/GameLayout.vue";

const createForm = useForm({ name: '' });
const joinForm = useForm({ code: '', name: '' });

const submitCreate = () => createForm.post(store());
const submitJoin = () => joinForm.post(join());
</script>

<template>
    <Head title="Welcome to Mille Bornes" />
    <GameLayout>
        <div class="flex items-center justify-center w-full h-full">
            <div class="relative z-10 flex flex-col items-center gap-8 w-full max-w-2xl px-6">
                <!-- Title block -->
                <div class="text-center">
                    <div class="flex items-baseline justify-center gap-3 mb-1">
                        <span class="font-display text-5xl font-black tracking-widest uppercase text-yellow-300">Mille Bornes</span>
                    </div>
                    <div class="flex items-center gap-3 justify-center mt-2">
                        <div class="h-px w-16 bg-gradient-to-r from-transparent to-yellow-800/60"></div>
                        <p class="text-[10px] tracking-[0.4em] uppercase text-yellow-500 font-serif">The Classic Road Race Card Game</p>
                        <div class="h-px w-16 bg-gradient-to-l from-transparent to-yellow-800/60"></div>
                    </div>
                </div>

                <!-- Two cards side by side -->
                <div class="grid grid-cols-2 gap-5 w-full">

                    <!-- Create Room -->
                    <div class="border border-yellow-800/60 rounded-2xl overflow-hidden shadow-[0_8px_48px_rgba(0,0,0,0.7)]">
                        <div class="bg-green-950 border-b border-yellow-800/40 px-6 py-4">
                            <p class="font-display text-base font-bold tracking-wide text-yellow-300">Create a Room</p>
                            <p class="text-[11px] text-yellow-500 mt-0.5">Start a new game and invite friends.</p>
                        </div>
                        <div class="bg-black/20 px-6 py-5">
                            <form @submit.prevent="submitCreate" class="flex flex-col gap-4">
                                <div class="flex flex-col gap-1.5">
                                    <label for="name-create"
                                           class="text-[10px] tracking-[0.3em] uppercase text-yellow-500 font-serif">
                                        Your Name
                                    </label>
                                    <input
                                        id="name-create"
                                        v-model="createForm.name"
                                        required
                                        placeholder="Enter your name"
                                        class="w-full px-3 py-2 rounded-lg bg-black/30 border border-yellow-800/40 text-yellow-100 text-sm placeholder:text-yellow-600/60 focus:outline-none focus:border-yellow-600 focus:ring-1 focus:ring-yellow-700/40 transition-colors"
                                    />
                                    <p v-if="createForm.errors.name" class="text-red-400 text-[11px]">{{ createForm.errors.name }}</p>
                                </div>
                                <button
                                    type="submit"
                                    :disabled="createForm.processing"
                                    class="w-full py-2.5 rounded-xl font-bold text-sm uppercase tracking-widest transition-all duration-200 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed bg-gradient-to-br from-yellow-700 to-yellow-500 text-yellow-950 hover:opacity-85 shadow-[0_0_20px_rgba(201,168,76,0.15)]"
                                >
                                    {{ createForm.processing ? 'Creating…' : 'Create Room' }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Join Room -->
                    <div class="border border-yellow-800/60 rounded-2xl overflow-hidden shadow-[0_8px_48px_rgba(0,0,0,0.7)]">
                        <div class="bg-green-950 border-b border-yellow-800/40 px-6 py-4">
                            <p class="font-display text-base font-bold tracking-wide text-yellow-300">Join a Room</p>
                            <p class="text-[11px] text-yellow-500 mt-0.5">Enter a code to join an existing game.</p>
                        </div>
                        <div class="bg-black/20 px-6 py-5">
                            <form @submit.prevent="submitJoin" class="flex flex-col gap-4">
                                <div class="flex flex-col gap-1.5">
                                    <label for="code-join"
                                           class="text-[10px] tracking-[0.3em] uppercase text-yellow-500 font-serif">
                                        Room Code
                                    </label>
                                    <input
                                        id="code-join"
                                        v-model="joinForm.code"
                                        required
                                        placeholder="e.g. ABC123"
                                        class="w-full px-3 py-2 rounded-lg bg-black/30 border border-yellow-800/40 text-yellow-100 text-sm placeholder:text-yellow-600/60 focus:outline-none focus:border-yellow-600 focus:ring-1 focus:ring-yellow-700/40 transition-colors font-display tracking-widest"
                                    />
                                    <p v-if="joinForm.errors.code" class="text-red-400 text-[11px]">{{ joinForm.errors.code }}</p>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label for="name-join"
                                           class="text-[10px] tracking-[0.3em] uppercase text-yellow-500 font-serif">
                                        Your Name
                                    </label>
                                    <input
                                        id="name-join"
                                        v-model="joinForm.name"
                                        required
                                        placeholder="Enter your name"
                                        class="w-full px-3 py-2 rounded-lg bg-black/30 border border-yellow-800/40 text-yellow-100 text-sm placeholder:text-yellow-600/60 focus:outline-none focus:border-yellow-600 focus:ring-1 focus:ring-yellow-700/40 transition-colors"
                                    />
                                    <p v-if="joinForm.errors.name" class="text-red-400 text-[11px]">{{ joinForm.errors.name }}</p>
                                </div>
                                <button
                                    type="submit"
                                    :disabled="joinForm.processing"
                                    class="w-full py-2.5 rounded-xl font-bold text-sm uppercase tracking-widest transition-all duration-200 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed bg-green-900/60 border border-yellow-800/50 text-yellow-300 hover:bg-green-900/80 hover:border-yellow-700 shadow-[0_0_20px_rgba(0,0,0,0.3)]"
                                >
                                    {{ joinForm.processing ? 'Joining…' : 'Join Room' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GameLayout>
</template>