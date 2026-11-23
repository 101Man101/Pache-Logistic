<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Menu, X } from '@lucide/vue';
import { onMounted, onUnmounted, ref } from 'vue';
import PacheLogo from '@/components/PacheLogo.vue';

const props = withDefaults(
    defineProps<{
        overlay?: boolean;
        activePath?: string;
    }>(),
    {
        overlay: false,
        activePath: '/',
    },
);

const mobileMenuOpen = ref(false);
const isScrolled = ref(false);

function handleScroll(): void {
    isScrolled.value = window.scrollY > 24;
}

onMounted(() => {
    handleScroll();
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

function sectionHref(hash: string): string {
    return props.activePath === '/' ? hash : `/${hash}`;
}
</script>

<template>
    <header
        class="z-50 border-b border-white/15 transition-all duration-200"
        :class="[
            overlay ? 'fixed inset-x-0 top-0' : 'sticky top-0',
            isScrolled
                ? 'bg-[#172b46]/95 shadow-[0_10px_28px_-12px_rgba(15,23,42,0.55)] backdrop-blur-md'
                : overlay
                  ? 'bg-transparent'
                  : 'bg-[#172b46]',
        ]"
    >
        <div
            class="mx-auto flex max-w-[1200px] items-center justify-between gap-6 px-6 py-4 md:px-10 md:py-5"
        >
            <Link
                href="/"
                class="no-underline"
                aria-label="Beranda Pache Logistic"
            >
                <PacheLogo :inverted="true" />
            </Link>

            <nav
                class="hidden items-center gap-7 md:flex"
                aria-label="Navigasi utama"
            >
                <Link
                    href="/"
                    class="text-[11.5px] font-bold tracking-[0.1em] uppercase no-underline transition hover:text-[#93c5fd]"
                    :class="
                        activePath === '/'
                            ? 'text-white'
                            : 'text-white/85 hover:text-white'
                    "
                >
                    Beranda
                </Link>
                <a
                    :href="sectionHref('#services')"
                    class="text-[11.5px] font-bold tracking-[0.1em] text-white/85 uppercase no-underline transition hover:text-white"
                >
                    Layanan
                </a>
                <a
                    :href="sectionHref('#values')"
                    class="text-[11.5px] font-bold tracking-[0.1em] text-white/85 uppercase no-underline transition hover:text-white"
                >
                    Nilai &amp; Cabang
                </a>
                <a
                    :href="sectionHref('#clients')"
                    class="text-[11.5px] font-bold tracking-[0.1em] text-white/85 uppercase no-underline transition hover:text-white"
                >
                    Mitra
                </a>
                <Link
                    href="/tracking"
                    class="text-[11.5px] font-bold tracking-[0.1em] uppercase no-underline transition hover:text-white"
                    :class="
                        activePath === '/tracking'
                            ? 'text-[#93c5fd]'
                            : 'text-white/85'
                    "
                >
                    Cek Resi
                </Link>
            </nav>

            <button
                type="button"
                @click="mobileMenuOpen = !mobileMenuOpen"
                class="flex h-10 w-10 items-center justify-center rounded-[8px] border border-white/30 text-white hover:bg-white/10 md:hidden"
                aria-label="Toggle navigasi"
            >
                <X v-if="mobileMenuOpen" class="h-5 w-5" />
                <Menu v-else class="h-5 w-5" />
            </button>
        </div>

        <div
            v-if="mobileMenuOpen"
            class="border-t border-white/15 bg-[#172b46]/95 px-6 py-4 md:hidden"
        >
            <div class="flex flex-col gap-3">
                <Link
                    href="/"
                    class="text-[13px] font-semibold text-white uppercase no-underline"
                    @click="mobileMenuOpen = false"
                >
                    Beranda
                </Link>
                <a
                    :href="sectionHref('#services')"
                    class="text-[13px] font-semibold text-white uppercase no-underline"
                    @click="mobileMenuOpen = false"
                >
                    Layanan
                </a>
                <a
                    :href="sectionHref('#values')"
                    class="text-[13px] font-semibold text-white uppercase no-underline"
                    @click="mobileMenuOpen = false"
                >
                    Nilai &amp; Cabang
                </a>
                <a
                    :href="sectionHref('#clients')"
                    class="text-[13px] font-semibold text-white uppercase no-underline"
                    @click="mobileMenuOpen = false"
                >
                    Mitra
                </a>
                <Link
                    href="/tracking"
                    class="text-[13px] font-semibold text-white uppercase no-underline"
                    @click="mobileMenuOpen = false"
                >
                    Cek Resi
                </Link>
            </div>
        </div>
    </header>
</template>
