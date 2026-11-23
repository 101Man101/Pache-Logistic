<script setup lang="ts">
export interface PartnerClientRow {
    code: string;
    name: string;
    type: string;
}

defineProps<{
    clients: PartnerClientRow[];
}>();

function primaryTitle(name: string): string {
    return name
        .replace(/^(PT|CV)\s+/i, '')
        .split(/\s+/)
        .slice(0, 2)
        .join(' ')
        .toUpperCase();
}

function secondarySubtitle(name: string): string {
    const words = name.replace(/^(PT|CV)\s+/i, '').split(/\s+/);
    return words.length > 2 ? words.slice(2).join(' ') : 'Mitra Resmi';
}
</script>

<template>
    <section
        class="border-b border-[#dce5f1] bg-[#f7f9fc] py-14 md:py-16"
        id="clients"
    >
        <div class="mx-auto max-w-[1200px] px-6 md:px-10">
            <h2
                class="text-center text-[11.5px] font-bold tracking-[0.18em] text-[#596579] uppercase"
            >
                KOLABORASI DENGAN MITRA TERKEMUKA
            </h2>
        </div>

        <!-- Frameless Infinite Horizontal Logo Marquee with Soft Edge Fades -->
        <div
            class="group/marquee relative mx-auto mt-10 max-w-[1200px] overflow-hidden py-2"
        >
            <!-- Left Fade Overlay -->
            <div
                class="pointer-events-none absolute inset-y-0 left-0 z-10 w-16 sm:w-28"
                style="
                    background: linear-gradient(
                        to right,
                        #f7f9fc 0%,
                        rgba(247, 249, 252, 0.85) 45%,
                        rgba(247, 249, 252, 0) 100%
                    );
                "
                aria-hidden="true"
            />

            <!-- Right Fade Overlay -->
            <div
                class="pointer-events-none absolute inset-y-0 right-0 z-10 w-16 sm:w-28"
                style="
                    background: linear-gradient(
                        to left,
                        #f7f9fc 0%,
                        rgba(247, 249, 252, 0.85) 45%,
                        rgba(247, 249, 252, 0) 100%
                    );
                "
                aria-hidden="true"
            />

            <!-- Marquee Scrolling Track (2 identical halves for pixel-seamless -50% loop) -->
            <div
                class="pache-partner-track flex w-max items-center group-hover/marquee:[animation-play-state:paused]"
            >
                <div
                    v-for="copy in 2"
                    :key="copy"
                    :aria-hidden="copy === 2"
                    class="flex shrink-0 items-center gap-14 pr-14 md:gap-20 md:pr-20"
                >
                    <div
                        v-for="client in clients"
                        :key="`${copy}-${client.code}`"
                        :title="client.name"
                        class="flex shrink-0 cursor-pointer items-center gap-3 opacity-55 grayscale transition-all duration-300 select-none hover:scale-105 hover:opacity-100 hover:grayscale-0"
                    >
                        <!-- 1. PT Nusantara Medika Farma -->
                        <template v-if="client.code === 'CUST-B2B-001'">
                            <svg
                                viewBox="0 0 40 40"
                                class="h-10 w-10 shrink-0"
                                fill="none"
                                aria-hidden="true"
                            >
                                <rect
                                    x="4"
                                    y="4"
                                    width="32"
                                    height="32"
                                    rx="10"
                                    fill="#0f766e"
                                />
                                <path
                                    d="M20 11v18M11 20h18"
                                    stroke="#ffffff"
                                    stroke-width="4.2"
                                    stroke-linecap="round"
                                />
                            </svg>
                            <div class="leading-none">
                                <span
                                    class="block text-[17px] font-extrabold tracking-[-0.02em] text-[#0f766e]"
                                >
                                    NUSANTARA
                                </span>
                                <span
                                    class="mt-1 block text-[10px] font-bold tracking-[0.14em] text-[#172b46] uppercase"
                                >
                                    MEDIKA FARMA
                                </span>
                            </div>
                        </template>

                        <!-- 2. PT Sinar Baja Industri -->
                        <template v-else-if="client.code === 'CUST-B2B-002'">
                            <svg
                                viewBox="0 0 42 40"
                                class="h-10 w-10 shrink-0"
                                fill="none"
                                aria-hidden="true"
                            >
                                <polygon
                                    points="6,34 15,6 27,6 18,34"
                                    fill="#1d4ed8"
                                />
                                <polygon
                                    points="20,34 29,6 37,6 28,34"
                                    fill="#d97706"
                                />
                            </svg>
                            <div class="leading-none">
                                <span
                                    class="block text-[18px] font-black tracking-[-0.03em] text-[#172b46] italic"
                                >
                                    SINAR BAJA
                                </span>
                                <span
                                    class="mt-1 block text-[9.5px] font-bold tracking-[0.18em] text-[#1d4ed8] uppercase"
                                >
                                    INDUSTRI LOGAM
                                </span>
                            </div>
                        </template>

                        <!-- 3. CV Karya Elektronik Sejahtera -->
                        <template v-else-if="client.code === 'CUST-B2B-003'">
                            <svg
                                viewBox="0 0 40 40"
                                class="h-10 w-10 shrink-0"
                                fill="none"
                                aria-hidden="true"
                            >
                                <circle cx="20" cy="20" r="16" fill="#2563eb" />
                                <path
                                    d="M22 9L13 22h7l-2 9 9-13h-7l2-9z"
                                    fill="#ffffff"
                                />
                            </svg>
                            <div class="leading-none">
                                <span
                                    class="block text-[17.5px] font-extrabold tracking-[-0.02em] text-[#1d4ed8]"
                                >
                                    KARYA
                                </span>
                                <span
                                    class="mt-1 block text-[10px] font-semibold tracking-[0.12em] text-[#475569] uppercase"
                                >
                                    ELEKTRONIK
                                </span>
                            </div>
                        </template>

                        <!-- 4. PT Agro Prima Distribusi -->
                        <template v-else-if="client.code === 'CUST-B2B-004'">
                            <svg
                                viewBox="0 0 40 40"
                                class="h-10 w-10 shrink-0"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M8 29C8 15 19 8 33 8c0 14-7 25-21 25-2.5 0-4-1.5-4-4z"
                                    fill="#15803d"
                                />
                                <path
                                    d="M12 29c5-6 11-11 18-16"
                                    stroke="#ffffff"
                                    stroke-width="2.4"
                                    stroke-linecap="round"
                                />
                            </svg>
                            <div class="leading-none">
                                <span
                                    class="block text-[17px] font-extrabold tracking-[-0.02em] text-[#15803d]"
                                >
                                    AGRO PRIMA
                                </span>
                                <span
                                    class="mt-1 block text-[9.5px] font-bold tracking-[0.15em] text-[#64748b] uppercase"
                                >
                                    DISTRIBUSI
                                </span>
                            </div>
                        </template>

                        <!-- 5. Atelier Tekstil Kemang -->
                        <template v-else-if="client.code === 'CUST-RTL-001'">
                            <svg
                                viewBox="0 0 40 40"
                                class="h-9 w-9 shrink-0"
                                fill="none"
                                aria-hidden="true"
                            >
                                <polygon
                                    points="20,4 36,20 20,36 4,20"
                                    stroke="#4338ca"
                                    stroke-width="3"
                                />
                                <polygon
                                    points="20,11 29,20 20,29 11,20"
                                    fill="#be123c"
                                />
                            </svg>
                            <div class="leading-none">
                                <span
                                    class="block text-[16.5px] font-bold tracking-[0.04em] text-[#312e81]"
                                >
                                    ATELIER
                                </span>
                                <span
                                    class="mt-1 block text-[10px] font-semibold tracking-[0.16em] text-[#be123c] uppercase"
                                >
                                    TEKSTIL KEMANG
                                </span>
                            </div>
                        </template>

                        <!-- 6. Kopi Roastery Dago -->
                        <template v-else-if="client.code === 'CUST-RTL-002'">
                            <svg
                                viewBox="0 0 40 40"
                                class="h-10 w-10 shrink-0"
                                fill="none"
                                aria-hidden="true"
                            >
                                <circle cx="20" cy="20" r="16" fill="#9a3412" />
                                <path
                                    d="M15 27c2-5 8-9 10-14M13 21c2-4 6-7 8-10"
                                    stroke="#fef3c7"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                />
                            </svg>
                            <div class="leading-none">
                                <span
                                    class="block text-[17px] font-extrabold tracking-[-0.02em] text-[#7c2d12]"
                                >
                                    KOPI DAGO
                                </span>
                                <span
                                    class="mt-1 block text-[10px] font-bold tracking-[0.16em] text-[#b45309] uppercase"
                                >
                                    ROASTERY
                                </span>
                            </div>
                        </template>

                        <!-- Fallback for any dynamic customer added via MDM -->
                        <template v-else>
                            <svg
                                viewBox="0 0 40 40"
                                class="h-9 w-9 shrink-0"
                                fill="none"
                                aria-hidden="true"
                            >
                                <rect
                                    x="5"
                                    y="5"
                                    width="30"
                                    height="30"
                                    rx="8"
                                    fill="#2563eb"
                                />
                                <path
                                    d="M12 20h16M20 12l8 8-8 8"
                                    stroke="#ffffff"
                                    stroke-width="2.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            <div class="leading-none">
                                <span
                                    class="block text-[17px] font-extrabold tracking-[-0.02em] text-[#172b46]"
                                >
                                    {{ primaryTitle(client.name) }}
                                </span>
                                <span
                                    class="mt-1 block text-[9.5px] font-bold tracking-[0.14em] text-[#2563eb] uppercase"
                                >
                                    {{ secondarySubtitle(client.name) }}
                                </span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes pache-partner-marquee {
    0% {
        transform: translate3d(0, 0, 0);
    }
    100% {
        transform: translate3d(-50%, 0, 0);
    }
}

.pache-partner-track {
    animation: pache-partner-marquee 28s linear infinite;
    will-change: transform;
}

@media (prefers-reduced-motion: reduce) {
    .pache-partner-track {
        animation: none;
    }
}
</style>
