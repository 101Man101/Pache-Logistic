<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    Building2,
    Database,
    Globe,
    LayoutDashboard,
    LogOut,
    Menu,
    Moon,
    Package,
    PackagePlus,
    ReceiptText,
    Search,
    Sun,
    Truck,
    Warehouse,
    X,
} from '@lucide/vue';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import PacheLogo from '@/components/PacheLogo.vue';
import { useAppearance } from '@/composables/useAppearance';

interface BranchItem {
    id: number;
    code: string;
    name: string;
    city: string;
    type: string;
}

interface SearchHit {
    type: string;
    title: string;
    subtitle: string;
    href: string;
}

const page = usePage();
const { resolvedAppearance, updateAppearance } = useAppearance();

const currentUrl = computed(() => page.url);
const user = computed(
    () =>
        page.props.auth?.user as {
            id: number;
            name: string;
            email: string;
            role?: string;
            role_label?: string;
            allowed_modules?: string[];
        } | null,
);

const allowedModules = computed(() => user.value?.allowed_modules ?? []);
const canCreateShipment = computed(() =>
    ['super_admin', 'admin_oms'].includes(user.value?.role ?? ''),
);
const branches = computed(
    () => (page.props.branches as BranchItem[] | undefined) ?? [],
);
const activeBranch = computed(
    () => page.props.activeBranch as BranchItem | null,
);
const flash = computed(
    () =>
        (page.props.flash as
            | { success?: string | null; error?: string | null }
            | undefined) ?? {},
);

const mobileOpen = ref(false);
const searchOpen = ref(false);
const searchQuery = ref('');
const searchResults = ref<SearchHit[]>([]);
const searchLoading = ref(false);
let searchTimer: ReturnType<typeof setTimeout> | null = null;

interface NavItem {
    label: string;
    href: string;
    icon: typeof LayoutDashboard;
    module?: string;
    createOnly?: boolean;
}

const navGroups: Array<{ label: string; items: NavItem[] }> = [
    {
        label: 'RINGKASAN',
        items: [
            { label: 'Dashboard', href: '/dashboard', icon: LayoutDashboard },
        ],
    },
    {
        label: 'OPERASIONAL',
        items: [
            {
                label: 'Pengiriman (OMS)',
                href: '/oms/shipments',
                icon: Package,
                module: 'oms',
            },
            {
                label: 'Buat Resi Baru',
                href: '/oms/shipments/create',
                icon: PackagePlus,
                module: 'oms',
                createOnly: true,
            },
            {
                label: 'Gudang (WMS)',
                href: '/wms/inventory',
                icon: Warehouse,
                module: 'wms',
            },
            {
                label: 'Manifest & Armada',
                href: '/tms/manifests',
                icon: Truck,
                module: 'tms',
            },
        ],
    },
    {
        label: 'KEUANGAN & MASTER',
        items: [
            {
                label: 'Finance Non-COD',
                href: '/finance',
                icon: ReceiptText,
                module: 'finance',
            },
            {
                label: 'Master & Tarif',
                href: '/master',
                icon: Database,
                module: 'master',
            },
        ],
    },
    {
        label: 'PUBLIK',
        items: [{ label: 'Cek Resi Publik', href: '/tracking', icon: Globe }],
    },
];

const filteredNavGroups = computed(() =>
    navGroups
        .map((group) => ({
            ...group,
            items: group.items.filter(
                (item) =>
                    (!item.module ||
                        allowedModules.value.includes(item.module)) &&
                    (!item.createOnly || canCreateShipment.value),
            ),
        }))
        .filter((group) => group.items.length > 0),
);

const mobileBottomNavItems = computed(() =>
    [
        { label: 'Beranda', href: '/dashboard', icon: LayoutDashboard },
        { label: 'OMS', href: '/oms/shipments', icon: Package, module: 'oms' },
        {
            label: 'WMS',
            href: '/wms/inventory',
            icon: Warehouse,
            module: 'wms',
        },
        { label: 'TMS', href: '/tms/manifests', icon: Truck, module: 'tms' },
        {
            label: 'Finance',
            href: '/finance',
            icon: ReceiptText,
            module: 'finance',
        },
    ]
        .filter(
            (item) =>
                !item.module || allowedModules.value.includes(item.module),
        )
        .slice(0, 4),
);

function isActive(href: string): boolean {
    if (href === '/oms/shipments') {
        return (
            currentUrl.value === '/oms/shipments' ||
            (currentUrl.value.startsWith('/oms/shipments') &&
                !currentUrl.value.startsWith('/oms/shipments/create'))
        );
    }
    return currentUrl.value === href || currentUrl.value.startsWith(`${href}?`);
}

function switchBranch(event: Event): void {
    const branchId = Number((event.target as HTMLSelectElement).value);
    if (!branchId) return;
    router.post(
        '/master/switch-branch',
        { branch_id: branchId },
        { preserveScroll: true },
    );
}

function toggleTheme(): void {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
}

function logout(): void {
    router.post('/logout');
}

watch(searchQuery, (val) => {
    if (searchTimer) clearTimeout(searchTimer);
    if (val.trim().length < 2) {
        searchResults.value = [];
        return;
    }
    searchLoading.value = true;
    searchTimer = setTimeout(async () => {
        try {
            const res = await fetch(
                `/api/erp/search?q=${encodeURIComponent(val.trim())}`,
                {
                    headers: { Accept: 'application/json' },
                },
            );
            if (res.ok) {
                const json = (await res.json()) as { items?: SearchHit[] };
                searchResults.value = json.items ?? [];
            }
        } finally {
            searchLoading.value = false;
        }
    }, 180);
});

function handleKeydown(e: KeyboardEvent): void {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        searchOpen.value = !searchOpen.value;
    } else if (e.key === 'Escape' && searchOpen.value) {
        searchOpen.value = false;
    }
}

onMounted(() => window.addEventListener('keydown', handleKeydown));
onUnmounted(() => window.removeEventListener('keydown', handleKeydown));
</script>

<template>
    <div class="pache-workspace">
        <!-- Fixed Transparent Sidebar over Cool-Paper --bone -->
        <aside class="pache-sidebar no-print">
            <div class="flex items-center px-2 pt-2 pb-4">
                <Link href="/dashboard" class="no-underline">
                    <PacheLogo />
                </Link>
            </div>

            <nav
                class="flex-1 overflow-y-auto pr-1"
                aria-label="Navigasi ERP Pache Logistic"
            >
                <div
                    v-for="group in filteredNavGroups"
                    :key="group.label"
                    class="mb-4"
                >
                    <p
                        class="mb-1.5 px-2.5 text-[10px] font-bold tracking-[0.12em] text-[var(--muted)] uppercase"
                    >
                        {{ group.label }}
                    </p>
                    <div class="grid gap-0.5">
                        <Link
                            v-for="item in group.items"
                            :key="item.href"
                            :href="item.href"
                            class="pache-nav-link"
                            :class="{ 'is-active': isActive(item.href) }"
                        >
                            <component
                                :is="item.icon"
                                class="h-[18px] w-[18px] shrink-0"
                                :stroke-width="1.65"
                            />
                            <span class="truncate">{{ item.label }}</span>
                        </Link>
                    </div>
                </div>
            </nav>

            <!-- User Card -->
            <div class="mt-2 border-t border-[var(--hairline)] pt-3">
                <div
                    class="flex items-center justify-between gap-2 px-1.5 pt-1"
                >
                    <div class="min-w-0">
                        <p
                            class="truncate text-[12.5px] font-semibold text-[var(--ink)]"
                        >
                            {{ user?.name || 'Operator Pache' }}
                        </p>
                        <p class="truncate text-[10.5px] text-[var(--muted)]">
                            {{
                                user?.role_label ||
                                user?.role?.replace('_', ' ') ||
                                'ERP Staff'
                            }}
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="logout"
                        title="Keluar"
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-[var(--muted)] transition hover:bg-[var(--surface-pink)] hover:text-[#b42318]"
                    >
                        <LogOut class="h-4 w-4" :stroke-width="1.7" />
                    </button>
                </div>
            </div>
        </aside>

        <!-- Topbar (52px) -->
        <header
            class="no-print col-span-full flex h-[52px] items-center justify-between gap-3 px-2 md:px-4"
        >
            <div class="flex items-center gap-2.5">
                <button
                    type="button"
                    @click="mobileOpen = true"
                    class="flex h-9 w-9 items-center justify-center rounded-[10px] border border-[var(--hairline)] bg-[var(--surface)] text-[var(--ink)] md:hidden"
                    aria-label="Buka menu"
                >
                    <Menu class="h-4 w-4" />
                </button>

                <button
                    type="button"
                    @click="searchOpen = true"
                    class="flex h-[38px] w-full max-w-[360px] items-center gap-2.5 rounded-[11px] border border-[var(--hairline)] bg-[var(--surface)] px-3 text-left text-[13px] text-[var(--muted)] transition hover:border-[var(--brand-blue)] md:w-[320px]"
                >
                    <Search
                        class="h-4 w-4 shrink-0 text-[var(--brand-blue)]"
                        :stroke-width="1.8"
                    />
                    <span class="flex-1 truncate"
                        >Cari AWB, Manifest, Pelanggan...</span
                    >
                    <kbd
                        class="hidden rounded border border-[var(--hairline)] bg-[var(--surface-soft)] px-1.5 py-0.5 text-[10px] font-semibold text-[var(--ink)] sm:inline"
                    >
                        Ctrl K
                    </kbd>
                </button>
            </div>

            <div class="flex items-center gap-2">
                <!-- Multi-Branch Switcher -->
                <label
                    v-if="branches.length"
                    class="hidden items-center gap-2 rounded-[10px] border border-[var(--hairline)] bg-[var(--surface)] px-2.5 py-1.5 text-[12.5px] sm:flex"
                >
                    <Building2
                        class="h-4 w-4 text-[var(--brand-blue)]"
                        :stroke-width="1.7"
                    />
                    <select
                        :value="activeBranch?.id"
                        @change="switchBranch"
                        class="bg-transparent font-semibold text-[var(--ink)] outline-none"
                        aria-label="Pilih Cabang Aktif"
                    >
                        <option
                            v-for="b in branches"
                            :key="b.id"
                            :value="b.id"
                            class="bg-[var(--surface)] text-[var(--ink)]"
                        >
                            {{ b.code }} — {{ b.city }}
                        </option>
                    </select>
                </label>

                <button
                    type="button"
                    @click="toggleTheme"
                    class="flex h-9 w-9 items-center justify-center rounded-[10px] border border-[var(--hairline)] bg-[var(--surface)] text-[var(--ink)] transition hover:border-[var(--brand-blue)] hover:text-[var(--brand-blue)]"
                    :title="
                        resolvedAppearance === 'dark'
                            ? 'Beralih ke Mode Terang'
                            : 'Beralih ke Mode Gelap'
                    "
                >
                    <Sun
                        v-if="resolvedAppearance === 'dark'"
                        class="h-4 w-4"
                        :stroke-width="1.8"
                    />
                    <Moon v-else class="h-4 w-4" :stroke-width="1.8" />
                </button>
            </div>
        </header>

        <!-- Floating 24px Rounded Main Sheet -->
        <div class="pache-main">
            <main class="pache-content-scroll">
                <div
                    class="mx-auto w-full max-w-[1600px] px-4 py-5 md:px-7 md:py-6"
                >
                    <!-- Flash Notifications -->
                    <div
                        v-if="flash.success"
                        class="mb-4 flex items-center justify-between rounded-[12px] border border-[#bbf7d0] bg-[#ecfdf3] px-4 py-3 text-[13.5px] font-medium text-[#15803d] dark:border-[#15803d]/40 dark:bg-[#14532d]/30 dark:text-[#86efac]"
                    >
                        <span>{{ flash.success }}</span>
                    </div>
                    <div
                        v-if="flash.error"
                        class="mb-4 flex items-center justify-between rounded-[12px] border border-[#fecaca] bg-[#fef2f2] px-4 py-3 text-[13.5px] font-medium text-[#991b1b] dark:border-[#dc2626]/40 dark:bg-[#7f1d1d]/30 dark:text-[#fca5a5]"
                    >
                        <span>{{ flash.error }}</span>
                    </div>

                    <slot />
                </div>
            </main>
        </div>

        <!-- Mobile Slide Drawer -->
        <div
            v-if="mobileOpen"
            class="fixed inset-0 z-50 flex md:hidden"
            @click.self="mobileOpen = false"
        >
            <div
                class="fixed inset-0 bg-black/40 backdrop-blur-xs"
                @click="mobileOpen = false"
            />
            <div
                class="relative z-10 flex w-[260px] flex-col bg-[var(--surface)] p-4 shadow-xl"
            >
                <div class="mb-4 flex items-center justify-between">
                    <PacheLogo compact />
                    <button
                        type="button"
                        @click="mobileOpen = false"
                        class="p-1.5 text-[var(--muted)]"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>
                <nav class="flex-1 space-y-4 overflow-y-auto">
                    <div v-for="group in filteredNavGroups" :key="group.label">
                        <p
                            class="mb-1 px-2 text-[10px] font-bold tracking-[0.12em] text-[var(--muted)] uppercase"
                        >
                            {{ group.label }}
                        </p>
                        <Link
                            v-for="item in group.items"
                            :key="item.href"
                            :href="item.href"
                            @click="mobileOpen = false"
                            class="pache-nav-link"
                            :class="{ 'is-active': isActive(item.href) }"
                        >
                            <component :is="item.icon" class="h-4 w-4" />
                            <span>{{ item.label }}</span>
                        </Link>
                    </div>
                </nav>
            </div>
        </div>

        <!-- Mobile Bottom Nav -->
        <nav
            class="no-print fixed right-2 bottom-2 left-2 z-40 grid gap-1 rounded-[17px] border border-[var(--hairline)] bg-[var(--surface)] p-1.5 shadow-lg md:hidden"
            :style="{
                gridTemplateColumns: `repeat(${mobileBottomNavItems.length}, minmax(0, 1fr))`,
            }"
        >
            <Link
                v-for="item in mobileBottomNavItems"
                :key="item.href"
                :href="item.href"
                class="flex flex-col items-center justify-center rounded-xl py-1.5 text-[11px] font-semibold"
                :class="
                    isActive(item.href)
                        ? 'bg-[var(--surface-pink)] text-[var(--brand-blue)]'
                        : 'text-[var(--muted)]'
                "
            >
                <component :is="item.icon" class="mb-0.5 h-4 w-4" />
                <span>{{ item.label }}</span>
            </Link>
        </nav>

        <!-- Global Command Palette (Ctrl+K — FR-OMS-05) -->
        <div
            v-if="searchOpen"
            class="fixed inset-0 z-50 flex items-start justify-center bg-black/45 px-4 pt-20 backdrop-blur-xs"
            @click.self="searchOpen = false"
        >
            <div
                class="w-full max-w-[560px] overflow-hidden rounded-[18px] border border-[var(--hairline)] bg-[var(--surface)] shadow-2xl"
            >
                <div
                    class="flex items-center gap-3 border-b border-[var(--hairline)] px-4 py-3.5"
                >
                    <Search class="h-5 w-5 text-[var(--brand-blue)]" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        v-focus
                        placeholder="Ketik Nomor AWB (PCH-...), Manifest, No. HP, atau Nama Pelanggan..."
                        class="w-full bg-transparent text-[15px] text-[var(--ink)] outline-none placeholder:text-[var(--muted)]"
                    />
                    <button
                        type="button"
                        @click="searchOpen = false"
                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--surface-soft)] text-[var(--muted)]"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <div class="max-h-[340px] overflow-y-auto p-2">
                    <p
                        v-if="searchLoading"
                        class="px-3 py-6 text-center text-[13px] text-[var(--muted)]"
                    >
                        Mencari data Pache Logistic...
                    </p>
                    <p
                        v-else-if="searchQuery.trim().length < 2"
                        class="px-3 py-6 text-center text-[13px] text-[var(--muted)]"
                    >
                        Masukkan minimal 2 karakter (contoh:
                        <span class="font-semibold text-[var(--ink)]"
                            >PCH-JKT</span
                        >
                        atau
                        <span class="font-semibold text-[var(--ink)]"
                            >Nusantara</span
                        >).
                    </p>
                    <p
                        v-else-if="searchResults.length === 0"
                        class="px-3 py-6 text-center text-[13px] text-[var(--muted)]"
                    >
                        Tidak ditemukan data untuk "{{ searchQuery }}".
                    </p>
                    <div v-else class="space-y-1">
                        <Link
                            v-for="hit in searchResults"
                            :key="`${hit.type}-${hit.title}`"
                            :href="hit.href"
                            @click="searchOpen = false"
                            class="flex items-center justify-between rounded-[10px] px-3 py-2.5 text-left transition hover:bg-[var(--surface-pink)]"
                        >
                            <div>
                                <p
                                    class="text-[14px] font-semibold text-[var(--ink)] tabular-nums"
                                >
                                    {{ hit.title }}
                                </p>
                                <p class="text-[12px] text-[var(--muted)]">
                                    {{ hit.subtitle }}
                                </p>
                            </div>
                            <span
                                class="rounded bg-[var(--surface-soft)] px-2 py-0.5 text-[10px] font-bold text-[var(--brand-blue)]"
                            >
                                {{ hit.type }}
                            </span>
                        </Link>
                    </div>
                </div>
                <div
                    class="flex items-center justify-between border-t border-[var(--hairline)] bg-[var(--surface-soft)] px-4 py-2 text-[11px] text-[var(--muted)]"
                >
                    <span
                        >Tekan <kbd class="rounded border px-1">Esc</kbd> untuk
                        menutup</span
                    >
                    <span>Pache Logistic Command Palette</span>
                </div>
            </div>
        </div>
    </div>
</template>
