<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, User } from '@lucide/vue';
import { ref } from 'vue';
import PacheLogo from '@/components/PacheLogo.vue';

defineProps<{
    status?: string;
    canResetPassword?: boolean;
}>();

const showPassword = ref(false);

const form = useForm({
    email: 'admin@pachelogistic.id',
    password: 'password',
    remember: true,
});

const demoAccounts = [
    { short: 'ADM', label: 'Super Admin HO', email: 'admin@pachelogistic.id' },
    { short: 'OMS', label: 'Admin OMS', email: 'oms@pachelogistic.id' },
    { short: 'FIN', label: 'Kasir & Finance', email: 'kasir@pachelogistic.id' },
    { short: 'WMS', label: 'Operator WMS', email: 'wms@pachelogistic.id' },
    { short: 'TMS', label: 'Dispatcher TMS', email: 'tms@pachelogistic.id' },
];

function selectDemo(email: string): void {
    form.email = email;
    form.password = 'password';
}

function submit(): void {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Masuk Workspace ERP" />

    <div
        class="relative flex min-h-screen items-center overflow-hidden bg-[#f7f9fc] text-[#18202c] dark:bg-[#090909] dark:text-[#f4f4f5]"
    >
        <!-- Full-Height Seamless Left Wave & Cargo Illustration (Pache Colors: #172b46, #2563eb, #dbeafe, #f7f9fc) -->
        <svg
            class="pointer-events-none absolute inset-y-0 left-0 hidden h-full w-[68%] select-none lg:block"
            viewBox="0 0 960 760"
            preserveAspectRatio="xMinYMid slice"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
        >
            <!-- Soft Sky/Paper Backing Glow -->
            <path
                d="M0 0H740C640 160 540 270 595 450C642 605 560 700 470 760H0V0Z"
                fill="#edf3fa"
                class="dark:fill-[#111827]"
            />

            <!-- Outer Brand Blue Crest Wave -->
            <path
                d="M0 0H340C370 115 448 188 532 252C635 330 676 424 624 548C582 648 618 712 668 760H0V0Z"
                fill="#2563eb"
                opacity="0.18"
            />

            <!-- Main Deep Navy Wave Body -->
            <path
                d="M0 0H235C275 110 356 178 446 248C544 324 584 422 528 548C485 645 524 714 576 760H0V0Z"
                fill="#172b46"
            />

            <!-- Dynamic Inner Blue Current Ribbons -->
            <path
                d="M0 120C95 140 175 190 250 265C340 355 395 452 332 585C298 656 322 718 368 760H0V120Z"
                fill="#1d4ed8"
                opacity="0.55"
            />
            <path
                d="M65 395C165 340 265 365 322 442C368 504 352 600 288 675C255 714 275 744 300 760H120C95 690 120 600 178 535C222 486 210 420 132 422C98 423 76 410 65 395Z"
                fill="#2563eb"
                opacity="0.55"
            />

            <!-- Crisp White Foam Crest Accents (Echoing Reference Wave Motion) -->
            <path
                d="M278 48C335 128 430 184 510 246C592 310 634 396 592 508"
                stroke="#2563eb"
                stroke-width="10"
                stroke-linecap="round"
                opacity="0.35"
            />
            <path
                d="M240 24C295 112 390 172 478 242C572 316 610 412 560 532C528 608 548 678 594 740"
                stroke="#ffffff"
                stroke-width="14"
                stroke-linecap="round"
            />
            <ellipse
                cx="502"
                cy="182"
                rx="18"
                ry="8"
                transform="rotate(34 502 182)"
                fill="#2563eb"
                opacity="0.35"
            />
            <ellipse
                cx="584"
                cy="258"
                rx="12"
                ry="6"
                transform="rotate(52 584 258)"
                fill="#2563eb"
                opacity="0.4"
            />

            <!-- Stylized Express Cargo Vessel / Logistics Bow Riding the Wave -->
            <g transform="translate(36, 210) rotate(-14 220 220)">
                <!-- Vessel Shadow on Water -->
                <path
                    d="M42 365C150 398 310 368 415 268C445 240 462 212 465 196C422 252 332 308 210 330C125 345 68 345 42 365Z"
                    fill="#0f1f36"
                    opacity="0.45"
                />

                <!-- Hull Base (Cool Paper White & Brand Blue) -->
                <path
                    d="M18 332C58 298 158 242 292 186C378 150 434 134 472 134C466 172 436 228 362 284C276 348 148 378 36 364L18 332Z"
                    fill="#ffffff"
                    stroke="#172b46"
                    stroke-width="4"
                    stroke-linejoin="round"
                />
                <!-- Hull Accent Stripe -->
                <path
                    d="M32 342C136 332 258 290 368 220C412 192 444 164 464 146C452 178 424 218 370 262C280 332 156 364 46 358L32 342Z"
                    fill="#2563eb"
                />

                <!-- Stacked Non-COD Freight Containers on Deck -->
                <g transform="translate(158, 178) rotate(-21)">
                    <rect
                        x="0"
                        y="0"
                        width="86"
                        height="44"
                        rx="4"
                        fill="#dbeafe"
                        stroke="#172b46"
                        stroke-width="3"
                    />
                    <line
                        x1="22"
                        y1="6"
                        x2="22"
                        y2="38"
                        stroke="#2563eb"
                        stroke-width="2.5"
                    />
                    <line
                        x1="44"
                        y1="6"
                        x2="44"
                        y2="38"
                        stroke="#2563eb"
                        stroke-width="2.5"
                    />
                    <line
                        x1="66"
                        y1="6"
                        x2="66"
                        y2="38"
                        stroke="#2563eb"
                        stroke-width="2.5"
                    />

                    <rect
                        x="94"
                        y="6"
                        width="76"
                        height="38"
                        rx="4"
                        fill="#2563eb"
                        stroke="#172b46"
                        stroke-width="3"
                    />
                    <line
                        x1="116"
                        y1="12"
                        x2="116"
                        y2="38"
                        stroke="#93c5fd"
                        stroke-width="2.5"
                    />
                    <line
                        x1="138"
                        y1="12"
                        x2="138"
                        y2="38"
                        stroke="#93c5fd"
                        stroke-width="2.5"
                    />

                    <rect
                        x="22"
                        y="-38"
                        width="72"
                        height="36"
                        rx="4"
                        fill="#93c5fd"
                        stroke="#172b46"
                        stroke-width="3"
                    />
                    <rect
                        x="102"
                        y="-30"
                        width="54"
                        height="34"
                        rx="4"
                        fill="#ffffff"
                        stroke="#172b46"
                        stroke-width="3"
                    />
                    <rect
                        x="112"
                        y="-22"
                        width="16"
                        height="12"
                        rx="2"
                        fill="#2563eb"
                    />
                    <rect
                        x="132"
                        y="-22"
                        width="16"
                        height="12"
                        rx="2"
                        fill="#2563eb"
                    />
                </g>

                <!-- Foam Splash at Bow -->
                <path
                    d="M368 256C404 238 430 248 442 268C410 264 388 274 360 288C346 276 352 264 368 256Z"
                    fill="#ffffff"
                />
            </g>
        </svg>

        <!-- Top-Left Brand Header Link -->
        <header
            class="absolute top-6 right-6 left-6 z-20 flex items-center justify-between md:top-8 md:right-10 md:left-10"
        >
            <Link
                href="/"
                class="inline-flex items-center rounded-full bg-white/90 px-4 py-2 no-underline shadow-[0_6px_20px_-8px_rgba(23,43,70,0.2)] backdrop-blur-sm dark:bg-[#172b46]/90"
                aria-label="Beranda Pache Logistic"
            >
                <PacheLogo compact :inverted="false" class="dark:hidden" />
                <PacheLogo compact inverted class="hidden dark:inline-flex" />
            </Link>

            <Link
                href="/"
                class="rounded-full border border-[#dce5f1] bg-white/90 px-4 py-2 text-[12px] font-bold text-[#172b46] no-underline shadow-[0_6px_18px_-8px_rgba(23,43,70,0.16)] backdrop-blur-sm transition hover:border-[#2563eb] hover:text-[#2563eb] dark:border-[#303030] dark:bg-[#1a1a1a] dark:text-[#f4f4f5]"
            >
                &larr; Kembali ke Beranda
            </Link>
        </header>

        <!-- Main Split Grid: Empty Illustration Space Left + Floating Rounded Card Right -->
        <main
            class="relative z-10 mx-auto grid w-full max-w-[1200px] grid-cols-1 items-center px-6 py-24 lg:grid-cols-12 lg:px-10"
        >
            <!-- Floating Rounded Login Card (Matching Reference Card Layout with Pache #2563eb Blue) -->
            <div
                class="mx-auto w-full max-w-[420px] lg:col-span-5 lg:col-start-8"
            >
                <div
                    class="rounded-[28px] bg-[#2563eb] px-7 py-9 text-white shadow-[0_28px_60px_-18px_rgba(23,43,70,0.38)] sm:px-9 sm:py-10 dark:border dark:border-[#3b82f6]/35 dark:bg-[#1d4ed8]"
                >
                    <h1
                        class="text-center text-[25px] font-[650] tracking-[-0.03em] text-white"
                    >
                        Masuk untuk melanjutkan
                    </h1>
                    <p
                        class="mt-1 text-center text-[12.5px] font-medium text-[#dbeafe]"
                    >
                        Pache Logistic · Strictly Non-COD
                    </p>

                    <div
                        v-if="status"
                        class="mt-5 rounded-full bg-[#ecfdf3] px-4 py-2 text-center text-[12px] font-semibold text-[#15803d]"
                    >
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="mt-7 space-y-4">
                        <!-- White Pill Email Input -->
                        <div>
                            <div
                                class="flex h-[48px] items-center gap-3 rounded-full bg-white px-5 shadow-[0_4px_12px_rgba(15,23,42,0.08)] focus-within:ring-3 focus-within:ring-white/40"
                            >
                                <User class="h-4 w-4 shrink-0 text-[#172b46]" />
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    autocomplete="email"
                                    placeholder="Email Perusahaan"
                                    aria-label="Email Perusahaan"
                                    class="h-full w-full border-0 bg-transparent text-[13.5px] font-semibold text-[#18202c] outline-none placeholder:font-normal placeholder:text-[#64748b]"
                                />
                            </div>
                            <p
                                v-if="form.errors.email"
                                class="mt-1.5 rounded-full bg-[#fef2f2] px-3.5 py-1 text-[11.5px] font-semibold text-[#991b1b]"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <!-- White Pill Password Input -->
                        <div>
                            <div
                                class="flex h-[48px] items-center gap-3 rounded-full bg-white px-5 shadow-[0_4px_12px_rgba(15,23,42,0.08)] focus-within:ring-3 focus-within:ring-white/40"
                            >
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="flex shrink-0 items-center justify-center border-0 bg-transparent p-0 text-[#172b46] transition hover:text-[#2563eb]"
                                    :aria-label="
                                        showPassword
                                            ? 'Sembunyikan kata sandi'
                                            : 'Tampilkan kata sandi'
                                    "
                                >
                                    <EyeOff
                                        v-if="showPassword"
                                        class="h-4 w-4"
                                    />
                                    <Eye v-else class="h-4 w-4" />
                                </button>
                                <input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Kata Sandi"
                                    aria-label="Kata Sandi"
                                    class="h-full w-full border-0 bg-transparent text-[13.5px] font-semibold text-[#18202c] outline-none placeholder:font-normal placeholder:text-[#64748b]"
                                />
                            </div>
                            <p
                                v-if="form.errors.password"
                                class="mt-1.5 rounded-full bg-[#fef2f2] px-3.5 py-1 text-[11.5px] font-semibold text-[#991b1b]"
                            >
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <!-- Remember Me + Forgot Password Row -->
                        <div
                            class="flex items-center justify-between px-1 pt-0.5 text-[12px] font-medium text-white"
                        >
                            <label
                                class="flex cursor-pointer items-center gap-2 select-none"
                            >
                                <input
                                    v-model="form.remember"
                                    type="checkbox"
                                    class="h-4 w-4 rounded-full border-white/80 bg-transparent accent-[#172b46]"
                                />
                                <span>Ingat saya</span>
                            </label>

                            <Link
                                v-if="canResetPassword"
                                href="/forgot-password"
                                class="text-[#dbeafe] no-underline hover:text-white hover:underline"
                            >
                                Lupa kata sandi?
                            </Link>
                            <Link
                                v-else
                                href="/"
                                class="text-[#dbeafe] no-underline hover:text-white hover:underline"
                            >
                                Kembali ke beranda
                            </Link>
                        </div>

                        <!-- Deep Navy Pill Submit Button -->
                        <div class="pt-2">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex h-[48px] w-full items-center justify-center rounded-full bg-[#172b46] px-6 text-[14px] font-bold tracking-[0.02em] text-white shadow-[0_12px_24px_-10px_rgba(15,23,42,0.7)] transition duration-200 hover:-translate-y-0.5 hover:bg-[#0f1f36] disabled:translate-y-0 disabled:opacity-65"
                            >
                                {{
                                    form.processing
                                        ? 'Memverifikasi...'
                                        : 'Masuk Workspace'
                                }}
                            </button>
                        </div>
                    </form>

                    <!-- Circular Quick Demo Persona Picker (Bottom Row Matching Reference) -->
                    <div class="mt-7 text-center">
                        <p class="text-[11.5px] font-medium text-[#dbeafe]">
                            atau pilih cepat akun demo (sandi:
                            <span class="font-bold text-white tabular-nums"
                                >password</span
                            >)
                        </p>
                        <div
                            class="mt-3 flex flex-wrap items-center justify-center gap-2.5"
                        >
                            <button
                                v-for="acc in demoAccounts"
                                :key="acc.email"
                                type="button"
                                @click="selectDemo(acc.email)"
                                :title="`${acc.label} (${acc.email})`"
                                class="flex h-10 w-10 items-center justify-center rounded-full text-[10.5px] font-bold tracking-[0.02em] tabular-nums transition"
                                :class="
                                    form.email === acc.email
                                        ? 'scale-105 bg-[#172b46] text-white ring-2 ring-white'
                                        : 'bg-white text-[#172b46] hover:bg-[#dbeafe]'
                                "
                            >
                                {{ acc.short }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
