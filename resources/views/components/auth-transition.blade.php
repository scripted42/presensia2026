{{-- resources/views/components/auth-transition.blade.php --}}
<div 
    x-cloak
    x-show="state === 'loading'"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    class="fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-[#f8fafc]"
    style="will-change: opacity;"
    role="status"
    aria-live="polite"
>
    <!-- Background subtle ambient glow circles -->
    <div class="absolute -top-24 -left-24 w-96 h-96 bg-teal/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-sun/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col items-center text-center px-6">
        <!-- SVG Progress Ring + Bobbing Icon Container -->
        <div class="relative w-32 h-32 flex items-center justify-center mb-6">
            <svg class="w-full h-full -rotate-90 transform" viewBox="0 0 120 120">
                <!-- Background Ring -->
                <circle 
                    cx="60" cy="60" r="50" 
                    fill="none" 
                    stroke="currentColor" 
                    stroke-width="7" 
                    class="text-slate-100" 
                />
                <!-- Animated Progress Ring using pathLength="1" -->
                <circle 
                    cx="60" cy="60" r="50" 
                    fill="none" 
                    stroke="currentColor" 
                    stroke-width="7" 
                    stroke-linecap="round"
                    pathLength="1"
                    stroke-dasharray="1"
                    :stroke-dashoffset="1 - progress"
                    class="text-teal transition-all duration-700 ease-out"
                />
            </svg>

            <!-- Centered SVG School Backpack & Book Icon with Gentle Bob -->
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none icon-bob">
                <svg class="w-12 h-12 text-teal" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Backpack body -->
                    <rect x="10" y="14" width="28" height="26" rx="8" fill="currentColor" fill-opacity="0.12" stroke="currentColor" stroke-width="2.5" />
                    <!-- Backpack top handle -->
                    <path d="M18 14V10C18 7.79086 19.7909 6 22 6H26C28.2091 6 30 7.79086 30 10V14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                    <!-- Front pocket / book flap with accent coral -->
                    <rect x="15" y="24" width="18" height="12" rx="4" fill="#FF6F59" fill-opacity="0.2" stroke="#FF6F59" stroke-width="2" />
                    <!-- Zip detail / badge -->
                    <circle cx="24" cy="22" r="2" fill="#FFC857" />
                    <!-- Bookmark ribbon -->
                    <path d="M29 24V31L26.5 29.5L24 31V24" fill="#FFC857" />
                </svg>
            </div>
        </div>

        <!-- Dynamic Status Message with Crossfade -->
        <div class="h-14 flex flex-col items-center justify-center">
            <h3 
                x-text="statusText"
                class="font-display text-xl sm:text-2xl font-bold text-slate-800 tracking-tight transition-opacity duration-300"
                :class="{ 'opacity-0': textFading, 'opacity-100': !textFading }"
            ></h3>
            <p class="font-sans text-xs font-medium text-slate-400 mt-1">Presensia &bull; SMPN 14 Surabaya</p>
        </div>
    </div>
</div>

<style>
/* Gentle 6px bob animation (1.1s ease-in-out infinite) */
@keyframes softBob {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-6px);
    }
}

.icon-bob {
    animation: softBob 1.1s ease-in-out infinite;
}

/* Respect user's prefers-reduced-motion setting */
@media (prefers-reduced-motion: reduce) {
    .icon-bob {
        animation: none !important;
    }
    * {
        transition-duration: 0.01ms !important;
    }
}
</style>
