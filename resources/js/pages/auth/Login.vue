<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowRight,
    Building2,
    CreditCard,
    Lock,
    ReceiptText,
    ShieldCheck,
    UserCheck,
} from '@lucide/vue';
import PacheLogo from '@/components/PacheLogo.vue';

defineProps<{
    status?: string;
    canResetPassword?: boolean;
}>();

const form = useForm({
    email: 'admin@pachelogistic.id',
    password: 'password',
    remember: true,
});

const demoAccounts = [
    { label: 'Super Admin HO', email: 'admin@pachelogistic.id', role: 'Akses Penuh Multi-Cabang' },
    { label: 'Admin Loket OMS', email: 'oms@pachelogistic.id', role: 'Booking & Cetak AWB A6' },
    { label: 'Kasir & Finance', email: 'kasir@pachelogistic.id', role: 'Cash Loket, Online & TOP' },
    { label: 'Operator Gudang', email: 'wms@pachelogistic.id', role: 'WMS Bin & Immutable Ledger' },
    { label: 'Dispatcher TMS', email: 'tms@pachelogistic.id', role: 'Manifest, Armada & e-POD' },
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

    <div class="grid min-h-screen place-items-center bg-[#edf3fa] p-4 text-[#172b46] dark:bg-[#090909] dark:text-[#f4f4f5] md:p-6">
        <div class="grid w-full max-w-[1020px] grid-cols-1 overflow-hidden rounded-[24px] border border-[#dce5f1] bg-white shadow-[0_24px_64px_-24px_rgba(23,43,70,0.18)] dark:border-[#303030] dark:bg-[#111111] lg:grid-cols-[1.02fr_0.98fr]">
            <!-- Left Cool-Paper Showcase Panel -->
            <div class="relative flex flex-col justify-between overflow-hidden bg-[#edf5ff] p-8 text-[#172b46] dark:bg-[#1a1a1a] dark:text-[#f4f4f5] md:p-10">
                <div>
                    <Link href="/" class="no-underline">
                        <PacheLogo />
                    </Link>

                    <div class="mt-8">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-white bg-white/80 px-3 py-1 text-[11px] font-bold text-[#1d4ed8] dark:border-[#303030] dark:bg-[#242424] dark:text-[#93c5fd]">
                            <ShieldCheck class="h-3.5 w-3.5" />
                            STRICTLY NON-COD ERP
                        </span>
                        <h1 class="mt-4 text-[32px] leading-[1.18] font-[650] tracking-[-0.04em]">
                            Kendali operasional<br />
                            <span class="text-[#2563eb] dark:text-[#60a5fa]">Pache Logistic.</span>
                        </h1>
                        <p class="mt-3 text-[13.5px] leading-[1.75] text-[#596579] dark:text-[#a1a1aa]">
                            Satu pintu untuk OMS, WMS, TMS, dan Finance dengan proteksi pembayaran Non-COD di setiap tahap.
                        </p>
                    </div>

                    <div class="mt-6 space-y-2.5">
                        <div class="flex items-center gap-3 rounded-[12px] border border-white bg-white/75 p-3 dark:border-[#303030] dark:bg-[#111111]/80">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-[9px] bg-[#dbeafe] text-[#2563eb] dark:bg-[#1e3a8a]/40 dark:text-[#93c5fd]">
                                <CreditCard class="h-4 w-4" />
                            </div>
                            <div>
                                <p class="text-[12.5px] font-bold">Online Payment (QRIS / VA)</p>
                                <p class="text-[11px] text-[#64748b] dark:text-[#a1a1aa]">Auto-settlement webhook sebelum Manifest</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-[12px] border border-white bg-white/75 p-3 dark:border-[#303030] dark:bg-[#111111]/80">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-[9px] bg-[#ecfdf3] text-[#15803d] dark:bg-[#14532d]/40 dark:text-[#86efac]">
                                <Building2 class="h-4 w-4" />
                            </div>
                            <div>
                                <p class="text-[12.5px] font-bold">Cash Tunai di Loket Asal</p>
                                <p class="text-[11px] text-[#64748b] dark:text-[#a1a1aa]">Lunas di muka, kurir bebas beban uang tunai</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-[12px] border border-white bg-white/75 p-3 dark:border-[#303030] dark:bg-[#111111]/80">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-[9px] bg-[#f8eddf] text-[#b45309] dark:bg-[#78350f]/40 dark:text-[#fcd34d]">
                                <ReceiptText class="h-4 w-4" />
                            </div>
                            <div>
                                <p class="text-[12.5px] font-bold">B2B Term of Payment (TOP)</p>
                                <p class="text-[11px] text-[#64748b] dark:text-[#a1a1aa]">Kontrol Credit Limit BR-02 &amp; Auto-Invoicing</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-between border-t border-[#d3e5ff] pt-4 text-[11px] text-[#617fa6] dark:border-[#303030] dark:text-[#a1a1aa]">
                    <span>Laravel 13 · Inertia v3 · PostgreSQL</span>
                    <Link href="/tracking" class="font-semibold text-[#2563eb] no-underline hover:underline dark:text-[#60a5fa]">
                        Cek Resi Publik →
                    </Link>
                </div>
            </div>

            <!-- Right Login Form Panel -->
            <div class="flex flex-col justify-center p-8 md:p-10">
                <div class="mb-6 flex items-start gap-3.5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[12px] bg-[#eff6ff] text-[#2563eb] dark:bg-[#242424] dark:text-[#60a5fa]">
                        <Lock class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-[10px] font-bold tracking-[0.14em] text-[#2563eb] uppercase dark:text-[#60a5fa]">
                            OTENTIKASI STAF &amp; KLIEN B2B
                        </p>
                        <h2 class="mt-0.5 text-[24px] font-[650] tracking-[-0.03em]">
                            Masuk ke Workspace
                        </h2>
                    </div>
                </div>

                <div v-if="status" class="mb-4 rounded-[10px] border border-[#bbf7d0] bg-[#ecfdf3] px-3.5 py-2.5 text-[12.5px] text-[#15803d]">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label for="email" class="mb-1.5 block text-[12px] font-semibold text-[#596579] dark:text-[#a1a1aa]">
                            Alamat Email Perusahaan
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="email"
                            class="pache-input h-[46px]"
                            placeholder="nama@pachelogistic.id"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-[12px] font-medium text-[#b42318]">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-[12px] font-semibold text-[#596579] dark:text-[#a1a1aa]">
                            Kata Sandi
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="pache-input h-[46px]"
                            placeholder="••••••••"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-[12px] font-medium text-[#b42318]">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <label class="flex items-center gap-2 text-[13px] text-[#596579] dark:text-[#a1a1aa]">
                        <input v-model="form.remember" type="checkbox" class="h-4 w-4 rounded border-[#dce5f1] text-[#2563eb]" />
                        <span>Ingat sesi login saya</span>
                    </label>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex h-[48px] w-full items-center justify-between rounded-[11px] bg-[#2563eb] px-5 text-[14px] font-bold text-white transition hover:bg-[#1d4ed8] disabled:opacity-60"
                    >
                        <span>{{ form.processing ? 'Memverifikasi...' : 'Masuk Workspace ERP' }}</span>
                        <ArrowRight class="h-4 w-4" />
                    </button>
                </form>

                <!-- Quick Demo Persona Picker -->
                <div class="mt-6 border-t border-[#e2ebf5] pt-5 dark:border-[#303030]">
                    <p class="mb-2.5 flex items-center gap-1.5 text-[11px] font-bold tracking-[0.08em] text-[#64748b] uppercase">
                        <UserCheck class="h-3.5 w-3.5 text-[#2563eb]" />
                        <span>Akses Cepat Akun Demo (password: password)</span>
                    </p>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <button
                            v-for="acc in demoAccounts"
                            :key="acc.email"
                            type="button"
                            @click="selectDemo(acc.email)"
                            class="rounded-[10px] border px-3 py-2 text-left transition"
                            :class="
                                form.email === acc.email
                                    ? 'border-[#2563eb] bg-[#eff6ff] dark:border-[#60a5fa] dark:bg-[#1e3a8a]/30'
                                    : 'border-[#e2ebf5] bg-[#f8fbff] hover:border-[#93c5fd] dark:border-[#303030] dark:bg-[#1a1a1a]'
                            "
                        >
                            <strong class="block text-[12px] font-bold">{{ acc.label }}</strong>
                            <span class="block text-[10.5px] text-[#64748b] dark:text-[#a1a1aa]">{{ acc.role }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
