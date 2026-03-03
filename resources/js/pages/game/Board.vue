<script setup lang="ts">
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { onMounted, ref, computed, watch } from 'vue';
import { action } from '@/routes/games';
import { welcome } from '@/routes/index';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { useEcho } from "@laravel/echo-vue";
import GameLayout from "@/layouts/GameLayout.vue";
import type { Game } from '@/models/Game';
import type { Player } from '@/models/Player';

const props = defineProps<{
    player: Player;
    game: Game;
}>();

const form = useForm({
    action: '',
    card_index: null,
    target_player_id: null,
});

const page = usePage();
const game = ref(props.game);
const isMyTurn = ref(false);
const iWasAttacked = ref(false);
const showHazardModal = ref(false);
const pendingHazardCardIndex = ref<number | null>(null);
const selectedTargetId = ref<number | null>(null);
const showFlashModal = ref(false);
const flashMessage = ref('');
const flashType = ref<'error' | 'success' | 'info'>('info');

const checkIsMyTurn = () => props.player.unique_identifier === game.value.current_player?.unique_identifier;

const checkIWasAttacked = () => props.player.unique_identifier === game.value.last_targeted_player?.unique_identifier;

const opponents = () => game.value.players.filter(p => p.unique_identifier !== props.player.unique_identifier);

const postError = (errors: any, fallback: string) => triggerFlash(Object.values(errors).join('\n') || fallback, 'error');

const myPlayerData = computed(() =>
    game.value.players.find(p => p.unique_identifier === props.player.unique_identifier)
);

const triggerFlash = (message: string, type: 'error' | 'success' | 'info' = 'info') => {
    flashMessage.value = message;
    flashType.value = type;
    showFlashModal.value = true;
};

const playCard = (cardIndex: number) => {
    const card = props.player.hand[cardIndex];
    if (card.type === 'hazard') {
        pendingHazardCardIndex.value = cardIndex;
        selectedTargetId.value = null;
        showHazardModal.value = true;
        return;
    }

    form.action = 'play';
    form.card_index = cardIndex;
    form.target_player_id = null;
    form.post(action(game.value.unique_identifier), {
        onError: (e) => postError(e, 'Failed to play card.'),
    });
};

const coupFourre = () => {
    form.action = 'coup_fourre';
    form.card_index = cardIndex;
    form.target_player_id = null;
    form.post(action(game.value.unique_identifier), {
        onError: (e) => postError(e, 'Failed to draw card.'),
    });
};

const confirmHazardTarget = () => {
    if (selectedTargetId.value === null) return;
    form.action = 'play';
    form.card_index = pendingHazardCardIndex.value;
    form.target_player_id = selectedTargetId.value;
    form.post(action(game.value.unique_identifier), {
        onError: (e) => postError(e, 'Failed to play card.'),
    });
    showHazardModal.value = false;
    pendingHazardCardIndex.value = null;
    selectedTargetId.value = null;
};

const cancelHazardModal = () => {
    showHazardModal.value = false;
    pendingHazardCardIndex.value = null;
    selectedTargetId.value = null;
};

const drawCard = () => {
    form.action = 'draw';
    form.post(action(game.value.unique_identifier), {
        onError: (e) => postError(e, 'Failed to draw card.'),
    });
};

const discardCard = (cardIndex: number) => {
    form.action = 'discard';
    form.card_index = cardIndex;
    form.post(action(game.value.unique_identifier), {
        onError: (e) => postError(e, 'Failed to discard card.'),
    });
};

const cardTypeBadge = (type: string) => {
    switch (type) {
        case 'hazard': return 'bg-red-900/80 text-red-300';
        case 'remedy': return 'bg-emerald-900/80 text-emerald-300';
        case 'safety': return 'bg-amber-900/80 text-amber-300';
        case 'distance': return 'bg-blue-900/80 text-blue-300';
        default: return 'bg-stone-800/80 text-stone-300';
    }
};

const goHome = () => {
    router.visit(welcome());
};

onMounted(() => {
    isMyTurn.value = checkIsMyTurn();
});

watch(
  () => page.flash,
  () => {
    if (page.flash?.message) {
        flashMessage.value = page.flash.message as string;
        flashMessage.value = flashMessage.value.replaceAll('_', ' ');
        flashType.value = 'error';
        showFlashModal.value = true;
    }
  },
  { deep: true, immediate: true }
);

useEcho(
    `game.${game.value.unique_identifier}`,
    "SwitchTurn",
    (e: any) => {
        game.value = e.game;
        isMyTurn.value = checkIsMyTurn();
        iWasAttacked.value = checkIWasAttacked();

        if (e.message) triggerFlash(e.message, 'info');
    },
);

useEcho(
    `game.${game.value.unique_identifier}`,
    "CardDrawn",
    (e: any) => {
        game.value = e.game;
    },
);

useEcho(
    `game.${game.value.unique_identifier}`,
    "PlayerWon",
    (e: any) => {
        game.value = e.game;
        isMyTurn.value = false;

        const iWon = e.player.unique_identifier === props.player.unique_identifier;
        const status = iWon ? 'success' : 'error';
        const message = iWon ? 'You won!' : `You lost! ${e.player.name} won!`;

        triggerFlash(message, status)
    },
);
</script>

<template>
    <Head title="Mille Bornes" />
    <GameLayout>
        <div class="flex flex-col w-full h-full">
            <!-- ── TOP BAR ── -->
            <header class="flex-shrink-0 flex items-center justify-between px-6 border-b border-yellow-800/40 shadow-lg z-10 bg-green-950" style="height:52px">
                <!-- Title -->
                <div class="flex items-baseline gap-2">
                    <span class="font-display text-xl font-black tracking-widest uppercase text-yellow-300">Mille Bornes</span>
                </div>

                <div v-if="game.status === 'finished'">
                     <button
                        class="w-full py-1 px-3 rounded text-[10px] font-bold uppercase tracking-wide text-yellow-950 bg-gradient-to-br from-yellow-700 to-yellow-500 hover:opacity-85 transition-opacity cursor-pointer"
                        @click="goHome()"
                    >Play again</button>
                </div>

                <!-- Turn indicator -->
                <div v-if="game.status !== 'finished'"
                    class="flex items-center gap-2 px-4 py-1.5 rounded-full border text-xs font-semibold tracking-widest transition-all duration-300"
                    :class="isMyTurn
                        ? 'border-yellow-500 text-yellow-200 bg-yellow-500/10 shadow-[0_0_16px_rgba(201,168,76,0.25)]'
                        : 'border-yellow-600/40 text-yellow-500 bg-black/30'"
                >
                    <span
                        class="w-2 h-2 rounded-full transition-all duration-300"
                        :class="isMyTurn ? 'bg-yellow-300 shadow-[0_0_8px_theme(colors.yellow.400)] animate-pulse' : 'bg-yellow-600'"
                    ></span>
                    <span v-if="isMyTurn">Your Turn</span>
                    <span v-else>{{ game.current_player?.name }}'s Turn</span>
                </div>
            </header>

            <!-- ── MAIN 3-COLUMN GRID ── -->
            <main class="flex-1 min-h-0 grid overflow-hidden" style="grid-template-columns: 220px 1fr 260px">

                <!-- ── LEFT: Opponents ── -->
                <aside class="border-r border-yellow-800/20 bg-black/15 p-3 overflow-y-auto overflow-x-hidden thin-scroll">
                    <div class="text-center text-[10px] tracking-[0.3em] uppercase text-yellow-500 pb-1.5 mb-3 border-b border-yellow-800/20 font-serif">
                        Opponents
                    </div>

                    <div v-for="opp in opponents()" :key="opp.id"
                         class="mb-3 p-2.5 rounded-lg border border-yellow-800/20 bg-black/25 hover:border-yellow-700/45 transition-colors">
                        <p class="font-bold text-sm text-yellow-50 mb-1 font-serif">{{ opp.name }}</p>
                        <div class="flex items-baseline gap-1 mb-2">
                            <span class="font-display text-xl font-black text-yellow-300">{{ opp.distance }}</span>
                            <span class="text-[10px] text-yellow-500 uppercase tracking-widest">km</span>
                        </div>

                        <div class="flex gap-2 mb-1.5">
                            <div v-for="(pile, key) in [
                                { card: opp.last_battle_pile_card, icon: '⚔', label: 'Battle' },
                                { card: opp.last_speed_pile_card,  icon: '⚡', label: 'Speed' }
                            ]" :key="key" class="flex flex-col items-center gap-1">
                                <div class="w-18 h-24 rounded overflow-hidden"
                                     :class="pile.card ? 'border border-yellow-800/50 bg-black/30' : 'border border-dashed border-yellow-800/25 flex items-center justify-center text-base opacity-40'">
                                    <img v-if="pile.card"
                                         :src="`/img/cards/${pile.card.type}_${pile.card.subtype}.png`"
                                         :alt="pile.card.label" class="w-full h-full object-cover" />
                                    <span v-else>{{ pile.icon }}</span>
                                </div>
                                <span class="text-[9px] uppercase tracking-wide text-yellow-500">{{ pile.label }}</span>
                            </div>
                        </div>

                        <div v-if="opp.safeties?.length" class="flex flex-wrap gap-1 mt-1.5">
                            <img v-for="(s, i) in opp.safeties" :key="i"
                                 :src="`/img/cards/${s.type}_${s.subtype}.png`" :alt="s.label"
                                 class="w-18 h-24 object-cover rounded border border-yellow-800/40" />
                        </div>

                        <span v-if="opp.speed_limit"
                              class="mt-1.5 inline-block px-1.5 py-0.5 bg-red-900 rounded text-[9px] uppercase tracking-wide text-red-300 font-bold">
                            ⚠ Speed Limit
                        </span>
                    </div>
                </aside>

                <!-- ── CENTER: Table ── -->
                <section class="relative flex flex-col items-center justify-around px-6 py-4">
                    <!-- Decorative inset border -->
                    <div class="absolute inset-3 border border-yellow-800/10 rounded-xl pointer-events-none"></div>
                    <!-- Vignette -->
                    <div class="absolute inset-0 pointer-events-none"
                         style="background: radial-gradient(ellipse at 50% 50%, transparent 0%, rgba(5,20,10,0.45) 100%)"></div>

                    <!-- Deck + Discard -->
                    <div class="flex items-center gap-6 z-10">
                        <!-- Draw pile -->
                        <div class="flex flex-col items-center gap-2">
                            <div
                                class="relative w-[70px] h-[100px] rounded-md border-2 overflow-hidden shadow-[2px_4px_12px_rgba(0,0,0,0.5)] transition-all duration-200 bg-gradient-to-br from-blue-950 to-blue-900"
                                :class="isMyTurn && player.hand.length <= 6
                                    ? 'border-yellow-800/60 cursor-pointer hover:-translate-y-1 hover:border-yellow-500 hover:shadow-[2px_8px_20px_rgba(0,0,0,0.6),0_0_16px_rgba(201,168,76,0.25)]'
                                    : 'border-yellow-600/40'"
                                @click="isMyTurn && player.hand.length <= 6 && drawCard()"
                            >
                                <div class="absolute inset-1 border border-yellow-800/20 rounded pointer-events-none"></div>
                                <div class="relative z-10 flex flex-col items-center justify-center h-full gap-0.5">
                                    <span class="font-display text-2xl font-black text-yellow-300">{{ game.deck_size }}</span>
                                    <span class="text-[9px] tracking-[0.2em] uppercase text-yellow-500">cards</span>
                                    <span v-if="isMyTurn && player.hand.length <= 6"
                                          class="mt-1.5 text-[8px] tracking-wide uppercase text-yellow-400 animate-pulse">Draw</span>
                                </div>
                            </div>
                            <span class="text-[10px] tracking-[0.2em] uppercase text-yellow-500">Draw Pile</span>
                        </div>

                        <span class="text-3xl text-yellow-500/40">→</span>

                        <!-- Discard pile -->
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-[70px] h-[100px] rounded-md border-2 border-yellow-800/30 overflow-hidden bg-black/30 shadow-[2px_4px_12px_rgba(0,0,0,0.4)]">
                                <img v-if="game.last_discard_card"
                                     :src="`/img/cards/${game.last_discard_card.type}_${game.last_discard_card.subtype}.png`"
                                     :alt="game.last_discard_card.label"
                                     class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex items-center justify-center border border-dashed border-yellow-800/20 text-xl opacity-30">—</div>
                            </div>
                            <span class="text-[10px] tracking-[0.2em] uppercase text-yellow-500">Discard</span>
                        </div>
                    </div>

                    <!-- My piles + distance odometer -->
                    <div class="flex items-center gap-6 z-10">
                        <!-- Battle pile -->
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-[70px] h-[100px] rounded-md border-2 border-yellow-800/30 overflow-hidden bg-black/25 shadow-[2px_4px_10px_rgba(0,0,0,0.4)]">
                                <img v-if="myPlayerData?.last_battle_pile_card"
                                     :src="`/img/cards/${myPlayerData.last_battle_pile_card.type}_${myPlayerData.last_battle_pile_card.subtype}.png`"
                                     :alt="myPlayerData.last_battle_pile_card.label"
                                     class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex items-center justify-center text-2xl opacity-30">⚔</div>
                            </div>
                            <span class="text-[10px] tracking-[0.2em] uppercase text-yellow-500">Battle</span>
                        </div>

                        <!-- Distance odometer -->
                        <div class="flex flex-col items-center px-5 py-3 border-2 border-yellow-800/60 rounded-xl bg-black/30 shadow-[0_0_20px_rgba(201,168,76,0.1)]">
                            <span class="font-display text-5xl font-black text-yellow-300 leading-none">{{ myPlayerData?.distance ?? 0 }}</span>
                            <span class="text-xs tracking-[0.3em] uppercase text-yellow-500 mt-1">km</span>
                            <span v-if="myPlayerData?.speed_limit"
                                  class="mt-2 px-2 py-0.5 bg-red-900 rounded text-[10px] uppercase tracking-wide text-red-300 font-bold">
                                ⚠ Speed Limit
                            </span>
                        </div>

                        <!-- Speed pile -->
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-[70px] h-[100px] rounded-md border-2 border-yellow-800/30 overflow-hidden bg-black/25 shadow-[2px_4px_10px_rgba(0,0,0,0.4)]">
                                <img v-if="myPlayerData?.last_speed_pile_card"
                                     :src="`/img/cards/${myPlayerData.last_speed_pile_card.type}_${myPlayerData.last_speed_pile_card.subtype}.png`"
                                     :alt="myPlayerData.last_speed_pile_card.label"
                                     class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex items-center justify-center text-2xl opacity-30">⚡</div>
                            </div>
                            <span class="text-[10px] tracking-[0.2em] uppercase text-yellow-500">Speed</span>
                        </div>
                    </div>

                    <!-- Safeties row -->
                    <div v-if="myPlayerData?.safeties?.length" class="flex items-center gap-3 z-10">
                        <span class="text-[10px] tracking-[0.3em] uppercase text-yellow-500">Safeties</span>
                        <div class="flex gap-2">
                            <div v-for="(s, i) in myPlayerData.safeties" :key="i"
                                 class="w-13 h-[72px] border border-yellow-700/40 rounded overflow-hidden bg-black/20">
                                <img :src="`/img/cards/${s.type}_${s.subtype}.png`" :alt="s.label" class="w-full h-full object-cover" />
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ── RIGHT: Hand ── -->
                <aside class="border-l border-yellow-800/20 bg-black/20 p-3 flex flex-col min-h-0 overflow-y-auto overflow-x-hidden thin-scroll">
                    <div class="text-center text-[10px] tracking-[0.3em] uppercase text-yellow-500 pb-1.5 mb-3 border-b border-yellow-800/20 font-serif">
                        Your Hand
                    </div>

                    <!-- Cards fill the height evenly, no scroll -->
                    <div class="flex flex-col gap-1.5">
                        <div
                            v-for="(card, index) in player?.hand"
                            :key="index"
                            class="flex-1 min-h-0 flex items-stretch gap-2 border border-yellow-800/20 rounded-md overflow-hidden bg-black/25 transition-all duration-150 hover:border-yellow-700/50"
                            :class="isMyTurn && player.hand.length >= 7 ? 'hover:-translate-x-0.5' : ''"
                        >
                            <!-- Portrait card image -->
                            <div class="relative flex-shrink-0 w-[70px]">
                                <img
                                    :src="`/img/cards/${card.type}_${card.subtype}.png`"
                                    :alt="card.label"
                                    class="w-full h-full object-cover"
                                />
                                <span
                                    class="absolute top-1 left-0.5 text-[7px] tracking-wide uppercase px-1 py-0.5 rounded font-bold leading-none"
                                    :class="cardTypeBadge(card.type)"
                                >{{ card.type }}</span>
                            </div>

                            <!-- Buttons beside the card -->
                            <div class="flex-1 flex flex-col justify-center gap-1.5 pr-2 py-2">
                                <p class="text-[10px] font-semibold text-yellow-200/70 capitalize leading-tight font-serif">{{ card.label ?? card.subtype }}</p>


                                <template v-if="iWasAttacked && card.type === 'safety'">
                                    <button
                                        class="w-full py-1 rounded text-[10px] font-bold uppercase tracking-wide text-yellow-950 bg-gradient-to-br from-yellow-700 to-yellow-500 hover:opacity-85 transition-opacity cursor-pointer"
                                        @click="coupFourre(index)"
                                    >Coup Fourre</button>
                                </template>

                                <template v-else>
                                    <template v-if="isMyTurn && player.hand.length >= 7">
                                        <button
                                            class="w-full py-1 rounded text-[10px] font-bold uppercase tracking-wide text-yellow-950 bg-gradient-to-br from-yellow-700 to-yellow-500 hover:opacity-85 transition-opacity cursor-pointer"
                                            @click="playCard(index)"
                                        >Play</button>
                                        <button
                                            class="w-full py-1 rounded text-[10px] font-bold uppercase tracking-wide text-red-300 bg-red-900/60 border border-red-900/80 hover:bg-red-900/80 transition-colors cursor-pointer"
                                            @click="discardCard(index)"
                                        >Discard</button>
                                    </template>
                                    <p v-if="isMyTurn === false" class="text-[9px] uppercase tracking-wide text-yellow-600 opacity-60">Not your turn</p>
                                    <p v-if="isMyTurn && player.hand.length == 6" class="text-[9px] uppercase tracking-wide text-yellow-600 opacity-60">Draw a card first</p>
                                </template>
                            </div>
                        </div>

                        <p v-if="!player?.hand?.length"
                           class="text-center py-6 text-[11px] tracking-[0.2em] uppercase text-yellow-500 opacity-60">
                            No cards in hand
                        </p>
                    </div>
                </aside>
            </main>
        </div>
    </GameLayout>

    <!-- ── HAZARD TARGET MODAL ── -->
    <Dialog :open="showHazardModal" @update:open="cancelHazardModal">
        <DialogContent class="bg-green-950 border border-yellow-800/60 rounded-xl text-yellow-100 font-sans max-w-sm">
            <DialogHeader>
                <DialogTitle class="font-display text-lg tracking-wide text-yellow-300">Choose Your Target</DialogTitle>
                <DialogDescription class="text-yellow-200/60 text-sm">
                    Select an opponent to unleash this hazard upon.
                </DialogDescription>
            </DialogHeader>

            <div class="flex flex-col gap-2 py-3">
                <button
                    v-for="opp in opponents()" :key="opp.id"
                    @click="selectedTargetId = opp.unique_identifier"
                    class="flex items-center justify-between w-full px-3.5 py-2.5 rounded-lg border cursor-pointer transition-all text-left"
                    :class="selectedTargetId === opp.unique_identifier
                        ? 'border-yellow-500 bg-yellow-500/10 text-yellow-100'
                        : 'border-yellow-800/25 bg-black/20 hover:border-yellow-700/50 text-yellow-200'"
                >
                    <div>
                        <p class="font-bold text-sm text-yellow-50 font-serif">{{ opp.name }}</p>
                        <p class="text-xs text-yellow-500">{{ opp.distance }} km</p>
                    </div>
                    <span v-if="selectedTargetId === opp.unique_identifier" class="text-yellow-300 text-base font-bold">✓</span>
                </button>
            </div>

            <DialogFooter class="flex gap-2">
                <button
                    class="px-5 py-2 rounded-lg border border-yellow-800/30 bg-transparent text-yellow-200 text-sm tracking-wide hover:border-yellow-700 transition-colors cursor-pointer"
                    @click="cancelHazardModal"
                >Cancel</button>
                <button
                    class="px-5 py-2 rounded-lg bg-gradient-to-br from-red-900 to-red-700 border border-red-700/80 text-red-200 text-sm font-bold tracking-wide transition-opacity cursor-pointer disabled:opacity-35 disabled:cursor-not-allowed hover:opacity-85"
                    :disabled="selectedTargetId === null"
                    @click="confirmHazardTarget"
                >Launch Hazard</button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- ── FLASH / ERROR MODAL ── -->
    <Dialog :open="showFlashModal" @update:open="showFlashModal = false">
        <DialogContent class="bg-green-950 border border-yellow-800/60 rounded-xl text-yellow-100 font-sans max-w-sm text-center">
            <div
                class="w-12 h-12 rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-3"
                :class="{
                    'bg-red-900/40 text-red-300 border border-red-900': flashType === 'error',
                    'bg-emerald-900/40 text-emerald-300 border border-emerald-900': flashType === 'success',
                    'bg-yellow-500/15 text-yellow-300 border border-yellow-800/60': flashType === 'info',
                }"
            >
                <span v-if="flashType === 'error'">✕</span>
                <span v-else-if="flashType === 'success'">✓</span>
                <span v-else>ℹ</span>
            </div>

            <DialogHeader>
                <DialogTitle
                    class="font-display text-lg tracking-wide"
                    :class="{
                        'text-red-300': flashType === 'error',
                        'text-emerald-300': flashType === 'success',
                        'text-yellow-300':  flashType === 'info',
                    }"
                >
                </DialogTitle>
                <DialogDescription class="text-yellow-200/70 text-sm mt-1.5 whitespace-pre-wrap text-center">
                    {{ flashMessage }}
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="mt-2 justify-center">
                <button
                    class="mx-auto px-7 py-2 rounded-lg text-sm font-bold tracking-wide cursor-pointer hover:opacity-85 transition-opacity"
                    :class="{
                        'bg-red-900 text-red-200': flashType === 'error',
                        'bg-emerald-900 text-emerald-200': flashType === 'success',
                        'bg-gradient-to-br from-yellow-700 to-yellow-500 text-yellow-950': flashType === 'info',
                    }"
                    @click="showFlashModal = false"
                >Got it</button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
/* Custom font utilities — Tailwind can't generate these without config */
.font-display { font-family: 'Playfair Display', serif; }
.font-serif   { font-family: 'Libre Baskerville', serif; }
.font-sans    { font-family: 'Source Sans 3', sans-serif; }

/* Scrollbar styling — no Tailwind equivalent */
.thin-scroll { scrollbar-width: thin; scrollbar-color: #78350f transparent; }
</style>