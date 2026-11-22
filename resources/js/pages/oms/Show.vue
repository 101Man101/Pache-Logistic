<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
    CheckCircle2,
    MapPin,
    Printer,
    ShieldCheck,
} from '@lucide/vue';
import {
    formatIdr,
    formatWeight,
    paymentBadgeClass,
    paymentStatusBadgeClass,
    shipmentStatusBadgeClass,
} from '@/lib/format';

interface ShipmentDetail {
    id: number;
    awb_number: string;
    reference_no: string | null;
    customer_name: string;
    customer_top_days: number;
    origin: string;
    destination: string;
    shipper_name: string;
    shipper_phone: string;
    shipper_address: string;
    consignee_name: string;
    consignee_phone: string;
    consignee_address: string;
    service_type: string;
    volumetric_divisor: number;
    actual_weight_kg: number;
    volumetric_weight_kg: number;
    chargeable_weight_kg: number;
    shipping_cost: number;
    insurance_cost: number;
    total_amount: number;
    payment_method: string;
    payment_method_label: string;
    payment_status: string;
    payment_status_label: string;
    payment_reference: string | null;
    paid_at: string | null;
    status: string;
    status_label: string;
    allowed_transitions: Array<{ value: string; label: string }>;
    epod: {
        receiver_name: string;
        receiver_relation: string;
        latitude: string;
        longitude: string;
        notes: string | null;
        delivered_at: string;
    } | null;
    items: Array<{
        id: number;
        description: string;
        qty: number;
        actual_weight_kg: number;
        length_cm: number;
        width_cm: number;
        height_cm: number;
    }>;
    logs: Array<{
        id: number;
        status: string;
        location: string;
        description: string;
        created_at: string;
    }>;
}

const props = defineProps<{
    shipment: ShipmentDetail;
}>();

const epodForm = useForm({
    receiver_name: '',
    receiver_relation: 'Yang Bersangkutan',
    latitude: -6.2088,
    longitude: 106.8456,
    notes: 'Paket diterima utuh (Strictly Non-COD).',
});

function confirmPayment(): void {
    router.post(`/oms/shipments/${props.shipment.awb_number}/confirm-payment`, {}, { preserveScroll: true });
}

function transitionTo(statusValue: string): void {
    router.post(
        `/oms/shipments/${props.shipment.awb_number}/transition`,
        { status: statusValue },
        { preserveScroll: true },
    );
}

function submitEpod(): void {
    epodForm.post(`/oms/shipments/${props.shipment.awb_number}/epod`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="`Resi ${shipment.awb_number}`" />

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <Link href="/oms/shipments" class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-[var(--brand-blue)] no-underline hover:underline">
                    <ArrowLeft class="h-3.5 w-3.5" />
                    <span>Daftar Pengiriman</span>
                </Link>
                <div class="mt-1 flex flex-wrap items-center gap-2.5">
                    <h1 class="text-[24px] font-bold tracking-[-0.03em] text-[var(--ink)] tabular-nums">
                        {{ shipment.awb_number }}
                    </h1>
                    <span class="rounded-[6px] border px-2.5 py-0.5 text-[11.5px] font-bold" :class="shipmentStatusBadgeClass(shipment.status)">
                        {{ shipment.status_label }}
                    </span>
                    <span class="rounded-[6px] border px-2.5 py-0.5 text-[11.5px] font-bold" :class="paymentBadgeClass(shipment.payment_method)">
                        NON-COD · {{ shipment.payment_method.toUpperCase() }}
                    </span>
                    <span class="rounded-[6px] border px-2.5 py-0.5 text-[11.5px] font-bold" :class="paymentStatusBadgeClass(shipment.payment_status)">
                        {{ shipment.payment_status_label }}
                    </span>
                </div>
            </div>
            <Link :href="`/oms/shipments/${shipment.awb_number}/label`" class="pache-btn-primary">
                <Printer class="h-4 w-4" />
                <span>Cetak Label A6 (Non-COD)</span>
            </Link>
        </div>

        <!-- BR-08 Non-COD Dispatch Gate Alert when PENDING -->
        <div
            v-if="shipment.payment_status === 'pending'"
            class="flex flex-wrap items-center justify-between gap-4 rounded-[14px] border border-[#fecaca] bg-[#fef2f2] p-4 dark:border-[#dc2626]/40 dark:bg-[#7f1d1d]/25"
        >
            <div class="flex items-start gap-3">
                <AlertTriangle class="mt-0.5 h-5 w-5 shrink-0 text-[#dc2626]" />
                <div>
                    <strong class="block text-[14px] text-[#991b1b] dark:text-[#fca5a5]">
                        Tertahan oleh Non-COD Dispatch Gate (BR-08)
                    </strong>
                    <p class="mt-0.5 text-[13px] text-[#7f1d1d] dark:text-[#fecaca]">
                        Pembayaran <strong>{{ shipment.payment_method_label }}</strong> senilai
                        <strong class="tabular-nums">{{ formatIdr(shipment.total_amount) }}</strong>
                        <span v-if="shipment.payment_reference"> (Ref: <code class="font-bold">{{ shipment.payment_reference }}</code>)</span>
                        belum terkonfirmasi lunas. Paket tidak dapat masuk Manifest atau InTransit.
                    </p>
                </div>
            </div>
            <button type="button" @click="confirmPayment" class="pache-btn-primary bg-[#15803d] border-[#15803d] hover:bg-[#166534]">
                <CheckCircle2 class="h-4 w-4" />
                <span>
                    {{ shipment.payment_method === 'online' ? 'Simulasikan Webhook Online PAID' : 'Konfirmasi Terima Cash Loket' }}
                </span>
            </button>
        </div>

        <!-- Main Grid -->
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-[1.2fr_0.8fr]">
            <div class="space-y-5">
                <!-- Route & Parties -->
                <section class="pache-panel grid grid-cols-1 gap-5 p-5 sm:grid-cols-2">
                    <div>
                        <p class="text-[11px] font-bold tracking-[0.08em] text-[var(--muted)] uppercase">Pengirim · {{ shipment.origin }}</p>
                        <p class="mt-1.5 text-[15px] font-bold text-[var(--ink)]">{{ shipment.shipper_name }}</p>
                        <p class="text-[13px] text-[var(--muted)] tabular-nums">{{ shipment.shipper_phone }}</p>
                        <p class="mt-1 text-[13px] text-[var(--muted)]">{{ shipment.shipper_address }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold tracking-[0.08em] text-[var(--muted)] uppercase">Penerima · {{ shipment.destination }}</p>
                        <p class="mt-1.5 text-[15px] font-bold text-[var(--ink)]">{{ shipment.consignee_name }}</p>
                        <p class="text-[13px] text-[var(--muted)] tabular-nums">{{ shipment.consignee_phone }}</p>
                        <p class="mt-1 text-[13px] text-[var(--muted)]">{{ shipment.consignee_address }}</p>
                    </div>
                </section>

                <!-- Weight & Billing -->
                <section class="pache-panel p-5">
                    <h2 class="mb-3 text-[16px] font-[650] text-[var(--ink)]">Rincian Berat &amp; Biaya (Non-COD)</h2>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-[10px] bg-[var(--surface-soft)] p-3">
                            <span class="text-[11px] text-[var(--muted)]">Aktual</span>
                            <strong class="mt-0.5 block text-[15px] tabular-nums">{{ formatWeight(shipment.actual_weight_kg) }}</strong>
                        </div>
                        <div class="rounded-[10px] bg-[var(--surface-soft)] p-3">
                            <span class="text-[11px] text-[var(--muted)]">Volumetrik (÷{{ shipment.volumetric_divisor }})</span>
                            <strong class="mt-0.5 block text-[15px] tabular-nums">{{ formatWeight(shipment.volumetric_weight_kg) }}</strong>
                        </div>
                        <div class="rounded-[10px] bg-[var(--surface-soft)] p-3">
                            <span class="text-[11px] text-[var(--muted)]">Chargeable</span>
                            <strong class="mt-0.5 block text-[15px] text-[var(--brand-blue)] tabular-nums">{{ formatWeight(shipment.chargeable_weight_kg) }}</strong>
                        </div>
                        <div class="rounded-[10px] bg-[var(--surface-soft)] p-3">
                            <span class="text-[11px] text-[var(--muted)]">Total Tagihan</span>
                            <strong class="mt-0.5 block text-[15px] tabular-nums">{{ formatIdr(shipment.total_amount) }}</strong>
                        </div>
                    </div>

                    <table class="pache-table mt-4">
                        <thead>
                            <tr>
                                <th>Deskripsi Koli</th>
                                <th>Qty</th>
                                <th>Berat</th>
                                <th>Dimensi (P×L×T cm)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="i in shipment.items" :key="i.id">
                                <td>{{ i.description }}</td>
                                <td class="tabular-nums">{{ i.qty }}</td>
                                <td class="tabular-nums">{{ formatWeight(i.actual_weight_kg) }}</td>
                                <td class="tabular-nums">{{ i.length_cm }} × {{ i.width_cm }} × {{ i.height_cm }}</td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <!-- State Machine Transitions (BR-03) & e-POD (BR-07) -->
                <section class="pache-panel space-y-4 p-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-[16px] font-[650] text-[var(--ink)]">
                            Kontrol Status Pengiriman (BR-03 State Machine)
                        </h2>
                        <span class="inline-flex items-center gap-1 text-[12px] font-semibold text-[#15803d]">
                            <ShieldCheck class="h-4 w-4" />
                            Non-COD Protected
                        </span>
                    </div>

                    <div v-if="shipment.allowed_transitions.length" class="flex flex-wrap gap-2">
                        <button
                            v-for="tr in shipment.allowed_transitions.filter((t) => t.value !== 'delivered')"
                            :key="tr.value"
                            type="button"
                            @click="transitionTo(tr.value)"
                            class="pache-btn-outline"
                        >
                            Lanjut ke: {{ tr.label }}
                        </button>
                    </div>
                    <p v-else class="text-[13px] text-[var(--muted)]">
                        Resi telah mencapai status final ({{ shipment.status_label }}).
                    </p>

                    <!-- Mandatory e-POD Form (BR-07) -->
                    <div v-if="shipment.status === 'out_for_delivery'" class="rounded-[12px] border border-[#bfdbfe] bg-[#eff6ff] p-4 dark:border-[#1d4ed8]/40 dark:bg-[#1e3a8a]/20">
                        <h3 class="text-[14px] font-bold text-[#1d4ed8] dark:text-[#93c5fd]">
                            Unggah Bukti Terima Digital (e-POD — BR-07 · Tanpa Penagihan Tunai)
                        </h3>
                        <form @submit.prevent="submitEpod" class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-[11.5px] font-semibold">Nama Jelas Penerima</label>
                                <input v-model="epodForm.receiver_name" type="text" required class="pache-input" placeholder="Nama penerima di lokasi" />
                            </div>
                            <div>
                                <label class="mb-1 block text-[11.5px] font-semibold">Relasi Penerima</label>
                                <select v-model="epodForm.receiver_relation" class="pache-input">
                                    <option>Yang Bersangkutan</option>
                                    <option>Keluarga</option>
                                    <option>Resepsionis</option>
                                    <option>Sekuriti</option>
                                    <option>Kepala Gudang</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-[11.5px] font-semibold">Latitude GPS</label>
                                <input v-model.number="epodForm.latitude" type="number" step="0.000001" required class="pache-input tabular-nums" />
                            </div>
                            <div>
                                <label class="mb-1 block text-[11.5px] font-semibold">Longitude GPS</label>
                                <input v-model.number="epodForm.longitude" type="number" step="0.000001" required class="pache-input tabular-nums" />
                            </div>
                            <div class="sm:col-span-2">
                                <button type="submit" :disabled="epodForm.processing" class="pache-btn-primary">
                                    <CheckCircle2 class="h-4 w-4" />
                                    <span>Simpan e-POD &amp; Selesaikan Pengiriman (Delivered)</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Saved e-POD Box -->
                    <div v-if="shipment.epod" class="rounded-[12px] border border-[#bbf7d0] bg-[#ecfdf3] p-4 text-[13px] text-[#15803d] dark:border-[#15803d]/40 dark:bg-[#14532d]/25 dark:text-[#86efac]">
                        <strong class="block font-bold">e-POD Terverifikasi ({{ shipment.epod.delivered_at }})</strong>
                        <p class="mt-1">
                            Penerima: <strong>{{ shipment.epod.receiver_name }}</strong> ({{ shipment.epod.receiver_relation }}) · GPS:
                            <span class="tabular-nums">{{ shipment.epod.latitude }}, {{ shipment.epod.longitude }}</span>
                        </p>
                        <p v-if="shipment.epod.notes" class="mt-1 opacity-90">{{ shipment.epod.notes }}</p>
                    </div>
                </section>
            </div>

            <!-- Right Timeline -->
            <section class="pache-panel p-5">
                <h2 class="mb-4 text-[16px] font-[650] text-[var(--ink)]">Riwayat Pelacakan (Tracking Logs)</h2>
                <ol class="relative ml-2 space-y-5 border-l-2 border-[var(--hairline)] pl-5">
                    <li v-for="(log, idx) in shipment.logs" :key="log.id" class="relative">
                        <span
                            class="absolute top-1 -left-[27px] h-3.5 w-3.5 rounded-full border-2 border-[var(--surface)]"
                            :class="idx === 0 ? 'bg-[var(--brand-blue)]' : 'bg-[var(--muted)]'"
                        />
                        <p class="text-[11.5px] font-semibold text-[var(--muted)] tabular-nums">{{ log.created_at }}</p>
                        <p class="mt-0.5 flex items-center gap-1.5 text-[13.5px] font-bold text-[var(--ink)]">
                            <MapPin class="h-3.5 w-3.5 text-[var(--brand-blue)]" />
                            <span>{{ log.location }}</span>
                        </p>
                        <p class="mt-0.5 text-[13px] text-[var(--muted)]">{{ log.description }}</p>
                    </li>
                </ol>
            </section>
        </div>
    </div>
</template>
