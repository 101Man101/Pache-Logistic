<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowUpRight,
    CheckCircle2,
    Lock,
    MapPin,
    Package,
    Search,
    ShieldCheck,
    Unlock,
} from '@lucide/vue';
import { ref } from 'vue';
import PacheLogo from '@/components/PacheLogo.vue';
import {
    formatWeight,
    paymentBadgeClass,
    paymentStatusBadgeClass,
    shipmentStatusBadgeClass,
} from '@/lib/format';

interface TrackingResult {
    awb_number: string;
    reference_no: string | null;
    service_type: string;
    status: string;
    status_label: string;
    payment_method: string;
    payment_method_label: string;
    payment_status: string;
    payment_status_label: string;
    chargeable_weight_kg: number;
    origin: string;
    destination: string;
    shipper_name: string;
    consignee_name: string;
    consignee_phone: string;
    consignee_address: string;
    pii_unlocked: boolean;
    epod_receiver_name: string | null;
    epod_receiver_relation: string | null;
    delivered_at: string | null;
    logs: Array<{
        id: number;
        status: string;
        location: string;
        description: string;
        created_at: string;
    }>;
}

const props = defineProps<{
    queryAwb: string;
    phoneLast4: string;
    results: TrackingResult[];
    notFound: string[];
    sampleAwbs: string[];
}>();

const awbInput = ref(props.queryAwb);
const phone4Input = ref(props.phoneLast4);

function submitTracking(): void {
    router.get(
        '/tracking',
        {
            awb: awbInput.value,
            phone_last4: phone4Input.value || undefined,
        },
        { preserveState: true },
    );
}

function fillSample(code: string): void {
    awbInput.value = code;
    submitTracking();
}
</script>

<template>
    <Head title="Cek Resi Publik Multi-AWB" />

    <div class="pache-landing min-h-screen">
        <header class="pache-landing-header">
            <div class="mx-auto flex max-w-[1280px] items-center justify-between gap-4 px-6 py-4 md:px-10">
                <Link href="/" class="no-underline">
                    <PacheLogo />
                </Link>
                <div class="flex items-center gap-3">
                    <Link
                        href="/"
                        class="inline-flex items-center gap-1.5 text-[13px] font-medium text-[#596579] no-underline hover:text-[#2563eb]"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        <span>Beranda</span>
                    </Link>
                    <Link href="/login" class="pache-header-cta">
                        <span>Masuk ERP</span>
                        <ArrowUpRight class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-[1060px] px-6 py-10 md:px-10 md:py-12">
            <div class="mb-8">
                <span class="text-[11.5px] font-bold tracking-[0.12em] text-[#2563eb] uppercase">
                    PELACAKAN PUBLIK · FR-TRK-01
                </span>
                <h1 class="mt-2 text-[clamp(30px,3.4vw,42px)] leading-[1.18] font-[650] tracking-[-0.04em] text-[#18202c]">
                    Cek Resi Multi-AWB Pache Logistic
                </h1>
                <p class="mt-2.5 max-w-[640px] text-[14.5px] leading-[1.75] text-[#596579]">
                    Lacak hingga <strong>10 nomor AWB</strong> sekaligus. Data sensitif penerima disamarkan otomatis demi privasi kecuali Anda memasukkan 4 digit terakhir nomor HP penerima.
                </p>
            </div>

            <!-- Tracking Form Card -->
            <form
                @submit.prevent="submitTracking"
                class="rounded-[16px] border border-[#dce5f1] bg-white p-6 shadow-[0_6px_24px_-12px_rgba(24,32,44,0.12)]"
            >
                <div class="grid grid-cols-1 gap-4 md:grid-cols-[1fr_230px]">
                    <div>
                        <label class="mb-1.5 block text-[12.5px] font-semibold text-[#18202c]">
                            Nomor AWB (pisahkan dengan koma atau baris baru)
                        </label>
                        <textarea
                            v-model="awbInput"
                            rows="2"
                            placeholder="PCH-JKT-261121-00001, PCH-JKT-261121-00003"
                            class="w-full rounded-[9px] border border-[#d5dfed] bg-[#f7f9fc] px-3.5 py-2.5 text-[14px] font-medium text-[#18202c] outline-none tabular-nums focus:border-[#2563eb] focus:bg-white"
                        />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[12.5px] font-semibold text-[#18202c]">
                            4 Digit Akhir HP Penerima (opsional)
                        </label>
                        <input
                            v-model="phone4Input"
                            type="text"
                            maxlength="4"
                            placeholder="Contoh: 7890"
                            class="h-[46px] w-full rounded-[9px] border border-[#d5dfed] bg-[#f7f9fc] px-3.5 text-[14px] text-[#18202c] outline-none tabular-nums focus:border-[#2563eb] focus:bg-white"
                        />
                        <p class="mt-1 text-[11px] text-[#748095]">
                            Membuka penyamaran alamat lengkap penerima.
                        </p>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-[11.5px] font-medium text-[#748095]">Contoh AWB:</span>
                        <button
                            v-for="code in sampleAwbs"
                            :key="code"
                            type="button"
                            @click="fillSample(code)"
                            class="rounded-md border border-[#dce5f1] bg-[#f7f9fc] px-2.5 py-1 text-[12px] font-semibold text-[#18202c] tabular-nums hover:border-[#2563eb] hover:text-[#2563eb]"
                        >
                            {{ code }}
                        </button>
                    </div>
                    <button
                        type="submit"
                        class="inline-flex h-[44px] items-center gap-2 rounded-[9px] bg-[#2563eb] px-6 text-[13.5px] font-semibold text-white transition hover:bg-[#1d4ed8]"
                    >
                        <Search class="h-4 w-4" />
                        <span>Lacak Sekarang</span>
                    </button>
                </div>
            </form>

            <!-- Not Found Warning -->
            <div
                v-if="notFound.length"
                class="mt-5 rounded-[12px] border border-[#fecaca] bg-[#fef2f2] px-4 py-3 text-[13px] font-medium text-[#991b1b]"
            >
                Nomor AWB tidak ditemukan: <span class="font-bold tabular-nums">{{ notFound.join(', ') }}</span>
            </div>

            <!-- Results -->
            <div v-if="results.length" class="mt-7 space-y-6">
                <article
                    v-for="item in results"
                    :key="item.awb_number"
                    class="overflow-hidden rounded-[16px] border border-[#dce5f1] bg-white shadow-[0_6px_22px_-12px_rgba(24,32,44,0.1)]"
                >
                    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[#e2ebf5] bg-[#f3f7fc] px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-[10px] bg-[#2563eb] text-white">
                                <Package class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="text-[17px] font-bold tracking-[-0.02em] text-[#172b46] tabular-nums">
                                    {{ item.awb_number }}
                                </p>
                                <p class="text-[12.5px] text-[#596579]">
                                    {{ item.origin }} → {{ item.destination }} · Layanan <strong>{{ item.service_type }}</strong> · {{ formatWeight(item.chargeable_weight_kg) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-[6px] border px-2.5 py-1 text-[11.5px] font-bold" :class="paymentBadgeClass(item.payment_method)">
                                NON-COD · {{ item.payment_method_label }}
                            </span>
                            <span class="rounded-[6px] border px-2.5 py-1 text-[11.5px] font-bold" :class="paymentStatusBadgeClass(item.payment_status)">
                                {{ item.payment_status_label }}
                            </span>
                            <span class="rounded-[6px] border px-2.5 py-1 text-[11.5px] font-bold" :class="shipmentStatusBadgeClass(item.status)">
                                {{ item.status_label }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-[0.85fr_1.15fr]">
                        <div class="space-y-4">
                            <div class="rounded-[12px] border border-[#e2ebf5] bg-[#f8fbff] p-4">
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="text-[11px] font-bold tracking-[0.08em] text-[#64748b] uppercase">
                                        Penerima Paket
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold" :class="item.pii_unlocked ? 'text-[#15803d]' : 'text-[#64748b]'">
                                        <Unlock v-if="item.pii_unlocked" class="h-3.5 w-3.5" />
                                        <Lock v-else class="h-3.5 w-3.5" />
                                        {{ item.pii_unlocked ? 'Terverifikasi' : 'PII Masked' }}
                                    </span>
                                </div>
                                <p class="text-[14.5px] font-bold text-[#172b46]">{{ item.consignee_name }}</p>
                                <p class="mt-0.5 text-[13px] text-[#596579] tabular-nums">{{ item.consignee_phone }}</p>
                                <p class="mt-1.5 text-[12.5px] leading-relaxed text-[#596579]">{{ item.consignee_address }}</p>
                            </div>

                            <div class="rounded-[12px] border border-[#bfdbfe] bg-[#eff6ff] p-3.5 text-[12.5px] text-[#1d4ed8]">
                                <div class="flex items-center gap-2 font-bold">
                                    <ShieldCheck class="h-4 w-4" />
                                    <span>Pengiriman Strictly Non-COD</span>
                                </div>
                                <p class="mt-1 text-[12px] text-[#214f8a]">
                                    Kurir Pache Logistic tidak memungut uang tunai apa pun saat menyerahkan paket ini.
                                </p>
                            </div>

                            <div v-if="item.epod_receiver_name" class="rounded-[12px] border border-[#bbf7d0] bg-[#ecfdf3] p-4 text-[12.5px] text-[#15803d]">
                                <div class="flex items-center gap-1.5 font-bold">
                                    <CheckCircle2 class="h-4 w-4" />
                                    <span>Bukti Terima Digital (e-POD)</span>
                                </div>
                                <p class="mt-1">
                                    Diterima oleh <strong>{{ item.epod_receiver_name }}</strong> ({{ item.epod_receiver_relation }}) pada <span class="tabular-nums">{{ item.delivered_at }}</span>.
                                </p>
                            </div>
                        </div>

                        <div>
                            <h3 class="mb-3 text-[12px] font-bold tracking-[0.08em] text-[#64748b] uppercase">
                                Riwayat Perjalanan Paket
                            </h3>
                            <ol class="relative ml-2 space-y-4 border-l-2 border-[#dce5f1] pl-5">
                                <li v-for="(log, idx) in item.logs" :key="log.id" class="relative">
                                    <span
                                        class="absolute top-1 -left-[27px] flex h-3.5 w-3.5 items-center justify-center rounded-full border-2 border-white"
                                        :class="idx === 0 ? 'bg-[#2563eb]' : 'bg-[#94a3b8]'"
                                    />
                                    <p class="text-[11.5px] font-semibold text-[#64748b] tabular-nums">
                                        {{ log.created_at }}
                                    </p>
                                    <p class="mt-0.5 flex items-center gap-1.5 text-[13.5px] font-bold text-[#172b46]">
                                        <MapPin class="h-3.5 w-3.5 text-[#2563eb]" />
                                        <span>{{ log.location }}</span>
                                    </p>
                                    <p class="mt-0.5 text-[13px] text-[#596579]">
                                        {{ log.description }}
                                    </p>
                                </li>
                            </ol>
                        </div>
                    </div>
                </article>
            </div>
        </main>
    </div>
</template>
