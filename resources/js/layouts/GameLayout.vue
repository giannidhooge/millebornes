<template>
    <div class="board-root w-screen h-screen overflow-hidden bg-green-950 font-sans">
        <slot />
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Libre+Baskerville:wght@400;700&family=Source+Sans+3:wght@400;600&display=swap');

.font-display { font-family: 'Playfair Display', serif; }
.font-serif   { font-family: 'Libre Baskerville', serif; }
.font-sans    { font-family: 'Source Sans 3', sans-serif; }

.board-root {
    background-image:
        radial-gradient(ellipse at 50% 0%, rgba(201,168,76,0.07) 0%, transparent 60%),
        repeating-linear-gradient(0deg,  transparent, transparent 24px, rgba(255,255,255,0.015) 24px, rgba(255,255,255,0.015) 25px),
        repeating-linear-gradient(90deg, transparent, transparent 24px, rgba(255,255,255,0.015) 24px, rgba(255,255,255,0.015) 25px);
}

/* Scrollbar styling — no Tailwind equivalent */
.thin-scroll { scrollbar-width: thin; scrollbar-color: #78350f transparent; }
</style>