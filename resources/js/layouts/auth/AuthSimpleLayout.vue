<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import PacheLogo from '@/components/PacheLogo.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col justify-between overflow-hidden bg-gradient-to-br from-[#edf3fa] via-[#f7f9fc] to-[#ffffff] text-[var(--ink)] dark:from-[#090909] dark:via-[#111111] dark:to-[#172b46]/20"
    >
        <!-- Soft Cool-Paper Wave Accent -->
        <svg
            viewBox="0 0 1440 320"
            preserveAspectRatio="none"
            class="pointer-events-none fixed inset-x-0 bottom-0 h-[36vh] w-full text-[#172b46]/8 dark:text-[#2563eb]/10"
            aria-hidden="true"
        >
            <path
                fill="currentColor"
                d="M0,224L80,208C160,192,320,160,480,165.3C640,171,800,213,960,218.7C1120,224,1280,192,1360,176L1440,160L1440,320L0,320Z"
            />
        </svg>

        <header
            class="relative z-10 mx-auto flex w-full max-w-[1200px] items-center justify-between px-6 py-6 md:px-10"
        >
            <Link
                :href="home()"
                class="inline-flex items-center rounded-full bg-white/90 px-4 py-2 no-underline shadow-[0_6px_20px_-8px_rgba(23,43,70,0.16)] backdrop-blur-sm dark:bg-[#172b46]/90"
            >
                <PacheLogo compact :inverted="false" class="dark:hidden" />
                <PacheLogo compact inverted class="hidden dark:inline-flex" />
            </Link>

            <Link
                :href="home()"
                class="inline-flex items-center gap-1.5 rounded-full border border-[#dce5f1] bg-white/90 px-4 py-2 text-[12px] font-bold text-[#172b46] no-underline shadow-[0_6px_18px_-8px_rgba(23,43,70,0.14)] backdrop-blur-sm transition hover:border-[#2563eb] hover:text-[#2563eb] dark:border-[#303030] dark:bg-[#1a1a1a] dark:text-[#f4f4f5]"
            >
                <ArrowLeft class="h-3.5 w-3.5" />
                <span>Beranda</span>
            </Link>
        </header>

        <main
            class="relative z-10 mx-auto flex w-full max-w-md flex-1 items-center justify-center px-6 pb-14"
        >
            <div
                class="w-full rounded-[24px] border border-[var(--hairline)] bg-[var(--surface)] p-7 shadow-[0_24px_54px_-22px_rgba(23,43,70,0.22)] sm:p-8"
            >
                <div class="mb-6 space-y-1.5 text-center">
                    <p
                        class="text-[10.5px] font-bold tracking-[0.14em] text-[var(--brand-blue)] uppercase"
                    >
                        PACHE LOGISTIC · STRICTLY NON-COD
                    </p>
                    <h1
                        class="text-[22px] font-[650] tracking-[-0.03em] text-[var(--ink)]"
                    >
                        {{ title }}
                    </h1>
                    <p
                        v-if="description"
                        class="text-[13.5px] leading-relaxed text-[var(--muted)]"
                    >
                        {{ description }}
                    </p>
                </div>

                <slot />
            </div>
        </main>
    </div>
</template>
