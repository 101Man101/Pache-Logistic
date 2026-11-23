<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
    CheckCircle2,
    Handshake,
    MapPin,
    Plus,
    Printer,
    ShieldCheck,
    Trash2,
} from '@lucide/vue';
import { computed } from 'vue';
import {
    formatIdr,
    formatWeight,
    paymentBadgeClass,
    paymentStatusBadgeClass,
    shipmentStatusBadgeClass,
} from '@/lib/format';

interface ChargeItem {
    id: number;
    charge_type: 'surcharge' | 'add_charge';
    code: string | null;
    description: string;
    calc_mode: string;
    rate_value: number;
    quantity: number;
    amount: number;
    notes: string | null;
}

interface ShipmentDetail {
    id: number;
    awb_number: string;
    reference_no: string | null;
    customer_name: string;
    customer_top_days: number;
    origin: string;
    destination: string;
    vendor_id: number | null;
    vendor: {
        id: number;
        code: string;
        name: string;
        type: string;
    } | null;
    vendor_awb_no: string | null;
    vendor_cost: number;
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
    surcharge_cost: number;
    additional_charge_cost: number;
    total_amount: number;
    is_invoiced: boolean;
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
    charges: ChargeItem[];
    logs: Array<{
        id: number;
        status: string;
        location: string;
        description: string;
        created_at: string;
    }>;
}

interface VendorOption {
    id: number;
    code: string;
    name: string;
    type: string;
    base_cost_per_kg: number;
}

interface SurchargeOption {
    id: number;
    code: string;
    name: string;
    calc_mode: 'fixed' | 'percentage' | 'per_kg';
    amount: number;
    description: string | null;
}

const props = defineProps<{
    shipment: ShipmentDetail;
    activeVendors: VendorOption[];
    activeSurcharges: SurchargeOption[];
}>();

const epodForm = useForm({
    receiver_name: '',
    receiver_relation: 'Yang Bersangkutan',
    latitude: -6.2088,
    longitude: 106.8456,
    notes: 'Paket diterima utuh (Strictly Non-COD).',
});

const chargeForm = useForm({
    surcharge_id: null as number | null,
    charge_type: 'add_charge' as 'surcharge' | 'add_charge',
    description: '',
    amount: 25000 as number | null,
    notes: '',
});

const vendorForm = useForm({
    vendor_id: props.shipment.vendor_id,
    vendor_awb_no: props.shipment.vendor_awb_no ?? '',
    vendor_cost: props.shipment.vendor_cost ?? 0,
});

const estimatedMargin = computed(
    () =>
        props.shipment.total_amount - (Number(props.shipment.vendor_cost) || 0),
);

function confirmPayment(): void {
    router.post(
        `/oms/shipments/${props.shipment.awb_number}/confirm-payment`,
        {},
        { preserveScroll: true },
    );
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

function onPresetChange(): void {
    const selected = props.activeSurcharges.find(
        (s) => s.id === chargeForm.surcharge_id,
    );
    if (!selected) return;
    chargeForm.charge_type = 'surcharge';
    chargeForm.description = selected.name;
    if (selected.calc_mode === 'percentage') {
        chargeForm.amount = Math.round(
            props.shipment.shipping_cost * (Number(selected.amount) / 100),
        );
    } else if (selected.calc_mode === 'per_kg') {
        chargeForm.amount = Math.round(
            props.shipment.chargeable_weight_kg * Number(selected.amount),
        );
    } else {
        chargeForm.amount = Math.round(Number(selected.amount));
    }
}

function submitCharge(): void {
    chargeForm.post(`/oms/shipments/${props.shipment.awb_number}/charges`, {
        preserveScroll: true,
        onSuccess: () =>
            chargeForm.reset('surcharge_id', 'description', 'notes'),
    });
}

function deleteCharge(chargeId: number): void {
    router.delete(
        `/oms/shipments/${props.shipment.awb_number}/charges/${chargeId}`,
        {
            preserveScroll: true,
        },
    );
}

function saveVendor(): void {
    vendorForm.post(`/oms/shipments/${props.shipment.awb_number}/vendor`, {
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
                <Link
                    href="/oms/shipments"
                    class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-[var(--brand-blue)] no-underline hover:underline"
                >
                    <ArrowLeft class="h-3.5 w-3.5" />
                    <span>Daftar Pengiriman</span>
                </Link>
                <div class="mt-1 flex flex-wrap items-center gap-2.5">
                    <h1
                        class="text-[24px] font-bold tracking-[-0.03em] text-[var(--ink)] tabular-nums"
                    >
                        {{ shipment.awb_number }}
                    </h1>
                    <span
                        class="rounded-[6px] border px-2.5 py-0.5 text-[11.5px] font-bold"
                        :class="shipmentStatusBadgeClass(shipment.status)"
                    >
                        {{ shipment.status_label }}
                    </span>
                    <span
                        class="rounded-[6px] border px-2.5 py-0.5 text-[11.5px] font-bold"
                        :class="paymentBadgeClass(shipment.payment_method)"
                    >
                        NON-COD · {{ shipment.payment_method.toUpperCase() }}
                    </span>
                    <span
                        class="rounded-[6px] border px-2.5 py-0.5 text-[11.5px] font-bold"
                        :class="
                            paymentStatusBadgeClass(shipment.payment_status)
                        "
                    >
                        {{ shipment.payment_status_label }}
                    </span>
                </div>
            </div>
            <Link
                :href="`/oms/shipments/${shipment.awb_number}/label`"
                class="pache-btn-primary"
            >
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
                    <strong
                        class="block text-[14px] text-[#991b1b] dark:text-[#fca5a5]"
                    >
                        Tertahan oleh Non-COD Dispatch Gate (BR-08)
                    </strong>
                    <p
                        class="mt-0.5 text-[13px] text-[#7f1d1d] dark:text-[#fecaca]"
                    >
                        Pembayaran
                        <strong>{{ shipment.payment_method_label }}</strong>
                        senilai
                        <strong class="tabular-nums">{{
                            formatIdr(shipment.total_amount)
                        }}</strong>
                        <span v-if="shipment.payment_reference">
                            (Ref:
                            <code class="font-bold">{{
                                shipment.payment_reference
                            }}</code
                            >)</span
                        >
                        belum terkonfirmasi lunas. Paket tidak dapat masuk
                        Manifest atau InTransit.
                    </p>
                </div>
            </div>
            <button
                type="button"
                @click="confirmPayment"
                class="pache-btn-primary border-[#15803d] bg-[#15803d] hover:bg-[#166534]"
            >
                <CheckCircle2 class="h-4 w-4" />
                <span>
                    {{
                        shipment.payment_method === 'online'
                            ? 'Simulasikan Webhook Online PAID'
                            : 'Konfirmasi Terima Cash Loket'
                    }}
                </span>
            </button>
        </div>

        <!-- Main Grid -->
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-[1.2fr_0.8fr]">
            <div class="space-y-5">
                <!-- Route & Parties -->
                <section
                    class="pache-panel grid grid-cols-1 gap-5 p-5 sm:grid-cols-2"
                >
                    <div>
                        <p
                            class="text-[11px] font-bold tracking-[0.08em] text-[var(--muted)] uppercase"
                        >
                            Pengirim · {{ shipment.origin }}
                        </p>
                        <p
                            class="mt-1.5 text-[15px] font-bold text-[var(--ink)]"
                        >
                            {{ shipment.shipper_name }}
                        </p>
                        <p class="text-[13px] text-[var(--muted)] tabular-nums">
                            {{ shipment.shipper_phone }}
                        </p>
                        <p class="mt-1 text-[13px] text-[var(--muted)]">
                            {{ shipment.shipper_address }}
                        </p>
                    </div>
                    <div>
                        <p
                            class="text-[11px] font-bold tracking-[0.08em] text-[var(--muted)] uppercase"
                        >
                            Penerima · {{ shipment.destination }}
                        </p>
                        <p
                            class="mt-1.5 text-[15px] font-bold text-[var(--ink)]"
                        >
                            {{ shipment.consignee_name }}
                        </p>
                        <p class="text-[13px] text-[var(--muted)] tabular-nums">
                            {{ shipment.consignee_phone }}
                        </p>
                        <p class="mt-1 text-[13px] text-[var(--muted)]">
                            {{ shipment.consignee_address }}
                        </p>
                    </div>
                </section>

                <!-- Weight & Billing -->
                <section class="pache-panel p-5">
                    <h2 class="mb-3 text-[16px] font-[650] text-[var(--ink)]">
                        Rincian Berat &amp; Biaya (Non-COD)
                    </h2>
                    <div
                        class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6"
                    >
                        <div
                            class="rounded-[10px] bg-[var(--surface-soft)] p-3"
                        >
                            <span class="text-[11px] text-[var(--muted)]"
                                >Chargeable</span
                            >
                            <strong
                                class="mt-0.5 block text-[14.5px] text-[var(--brand-blue)] tabular-nums"
                                >{{
                                    formatWeight(shipment.chargeable_weight_kg)
                                }}</strong
                            >
                        </div>
                        <div
                            class="rounded-[10px] bg-[var(--surface-soft)] p-3"
                        >
                            <span class="text-[11px] text-[var(--muted)]"
                                >Ongkir Dasar</span
                            >
                            <strong
                                class="mt-0.5 block text-[14.5px] tabular-nums"
                                >{{ formatIdr(shipment.shipping_cost) }}</strong
                            >
                        </div>
                        <div
                            class="rounded-[10px] bg-[var(--surface-soft)] p-3"
                        >
                            <span class="text-[11px] text-[var(--muted)]"
                                >Asuransi</span
                            >
                            <strong
                                class="mt-0.5 block text-[14.5px] tabular-nums"
                                >{{
                                    formatIdr(shipment.insurance_cost)
                                }}</strong
                            >
                        </div>
                        <div
                            class="rounded-[10px] bg-[var(--surface-soft)] p-3"
                        >
                            <span class="text-[11px] text-[var(--muted)]"
                                >Surcharge</span
                            >
                            <strong
                                class="mt-0.5 block text-[14.5px] tabular-nums"
                                >{{
                                    formatIdr(shipment.surcharge_cost)
                                }}</strong
                            >
                        </div>
                        <div
                            class="rounded-[10px] bg-[var(--surface-soft)] p-3"
                        >
                            <span class="text-[11px] text-[var(--muted)]"
                                >Add Charge</span
                            >
                            <strong
                                class="mt-0.5 block text-[14.5px] tabular-nums"
                                >{{
                                    formatIdr(shipment.additional_charge_cost)
                                }}</strong
                            >
                        </div>
                        <div class="rounded-[10px] bg-[#2563eb] p-3 text-white">
                            <span class="text-[11px] text-blue-100"
                                >Total Tagihan</span
                            >
                            <strong
                                class="mt-0.5 block text-[14.5px] tabular-nums"
                                >{{ formatIdr(shipment.total_amount) }}</strong
                            >
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
                                <td class="tabular-nums">
                                    {{ formatWeight(i.actual_weight_kg) }}
                                </td>
                                <td class="tabular-nums">
                                    {{ i.length_cm }} × {{ i.width_cm }} ×
                                    {{ i.height_cm }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <!-- Surcharge & Add Charge Panel -->
                <section class="pache-panel space-y-4 p-5">
                    <div
                        class="flex flex-wrap items-center justify-between gap-2"
                    >
                        <div>
                            <h2
                                class="text-[16px] font-[650] text-[var(--ink)]"
                            >
                                Surcharge &amp; Add Charge
                            </h2>
                            <p class="text-[12px] text-[var(--muted)]">
                                Tambahkan surcharge standar atau biaya tambahan
                                operasional sebelum resi masuk faktur B2B.
                            </p>
                        </div>
                        <span
                            v-if="shipment.is_invoiced"
                            class="rounded-[6px] bg-[#fef3c7] px-2.5 py-1 text-[11px] font-bold text-[#92400e]"
                        >
                            TERKUNCI (SUDAH INVOICE)
                        </span>
                    </div>

                    <table v-if="shipment.charges.length" class="pache-table">
                        <thead>
                            <tr>
                                <th>Jenis</th>
                                <th>Keterangan</th>
                                <th class="text-right">Nominal</th>
                                <th v-if="!shipment.is_invoiced" class="w-12" />
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="c in shipment.charges" :key="c.id">
                                <td>
                                    <span
                                        class="rounded px-2 py-0.5 text-[10.5px] font-bold uppercase"
                                        :class="
                                            c.charge_type === 'surcharge'
                                                ? 'bg-[#eff6ff] text-[#1d4ed8]'
                                                : 'bg-[#f8eddf] text-[#92400e]'
                                        "
                                    >
                                        {{
                                            c.charge_type === 'surcharge'
                                                ? 'Surcharge'
                                                : 'Add Charge'
                                        }}
                                    </span>
                                </td>
                                <td>
                                    <strong class="block text-[13px]">{{
                                        c.description
                                    }}</strong>
                                    <span
                                        v-if="c.notes"
                                        class="text-[11.5px] text-[var(--muted)]"
                                        >{{ c.notes }}</span
                                    >
                                </td>
                                <td class="text-right font-bold tabular-nums">
                                    {{ formatIdr(c.amount) }}
                                </td>
                                <td
                                    v-if="!shipment.is_invoiced"
                                    class="text-right"
                                >
                                    <button
                                        type="button"
                                        @click="deleteCharge(c.id)"
                                        class="rounded p-1.5 text-[var(--muted)] hover:bg-[#fef2f2] hover:text-[#b42318]"
                                        title="Hapus item biaya"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-else class="text-[12.5px] text-[var(--muted)]">
                        Belum ada surcharge atau biaya tambahan pada resi ini.
                    </p>

                    <form
                        v-if="!shipment.is_invoiced"
                        @submit.prevent="submitCharge"
                        class="grid grid-cols-1 gap-3 rounded-[12px] border border-[var(--hairline)] bg-[var(--surface-soft)] p-4 sm:grid-cols-2"
                    >
                        <div>
                            <label
                                class="mb-1 block text-[11.5px] font-semibold text-[var(--muted)]"
                                >Pilih Preset Surcharge (opsional)</label
                            >
                            <select
                                v-model="chargeForm.surcharge_id"
                                @change="onPresetChange"
                                class="pache-input"
                            >
                                <option :value="null">
                                    — Custom Add Charge —
                                </option>
                                <option
                                    v-for="s in activeSurcharges"
                                    :key="s.id"
                                    :value="s.id"
                                >
                                    {{ s.code }} · {{ s.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-[11.5px] font-semibold text-[var(--muted)]"
                                >Kategori Biaya</label
                            >
                            <select
                                v-model="chargeForm.charge_type"
                                class="pache-input"
                            >
                                <option value="add_charge">
                                    Add Charge (Biaya Tambahan)
                                </option>
                                <option value="surcharge">Surcharge</option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-[11.5px] font-semibold text-[var(--muted)]"
                                >Deskripsi Biaya</label
                            >
                            <input
                                v-model="chargeForm.description"
                                type="text"
                                required
                                class="pache-input"
                                placeholder="Packing Kayu / Tunggu Armada"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-[11.5px] font-semibold text-[var(--muted)]"
                                >Nominal (IDR)</label
                            >
                            <input
                                v-model.number="chargeForm.amount"
                                type="number"
                                min="1"
                                step="1000"
                                required
                                class="pache-input tabular-nums"
                            />
                        </div>
                        <div
                            class="flex flex-wrap items-center justify-between gap-3 sm:col-span-2"
                        >
                            <input
                                v-model="chargeForm.notes"
                                type="text"
                                class="pache-input sm:flex-1"
                                placeholder="Catatan operasional (opsional)"
                            />
                            <button
                                type="submit"
                                :disabled="chargeForm.processing"
                                class="pache-btn-primary"
                            >
                                <Plus class="h-4 w-4" />
                                <span>Tambah Biaya</span>
                            </button>
                        </div>
                    </form>
                </section>

                <!-- Vendor 3PL Assignment -->
                <section class="pache-panel space-y-3 p-5">
                    <div
                        class="flex flex-wrap items-center justify-between gap-2"
                    >
                        <div class="flex items-center gap-2">
                            <Handshake
                                class="h-4 w-4 text-[var(--brand-blue)]"
                            />
                            <h2
                                class="text-[16px] font-[650] text-[var(--ink)]"
                            >
                                Vendor / Mitra 3PL
                            </h2>
                        </div>
                        <span
                            class="text-[12px] font-bold tabular-nums"
                            :class="
                                estimatedMargin >= 0
                                    ? 'text-[#15803d]'
                                    : 'text-[#991b1b]'
                            "
                        >
                            Est. Margin: {{ formatIdr(estimatedMargin) }}
                        </span>
                    </div>

                    <form
                        @submit.prevent="saveVendor"
                        class="grid grid-cols-1 gap-3 sm:grid-cols-3"
                    >
                        <div>
                            <label
                                class="mb-1 block text-[11.5px] font-semibold text-[var(--muted)]"
                                >Pilih Vendor</label
                            >
                            <select
                                v-model="vendorForm.vendor_id"
                                class="pache-input"
                            >
                                <option :value="null">
                                    — Armada Internal Pache —
                                </option>
                                <option
                                    v-for="v in activeVendors"
                                    :key="v.id"
                                    :value="v.id"
                                >
                                    {{ v.code }} · {{ v.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-[11.5px] font-semibold text-[var(--muted)]"
                                >No. Resi / SMU Vendor</label
                            >
                            <input
                                v-model="vendorForm.vendor_awb_no"
                                type="text"
                                class="pache-input tabular-nums"
                                placeholder="SMU / AWB Partner"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-[11.5px] font-semibold text-[var(--muted)]"
                                >Biaya Vendor (IDR)</label
                            >
                            <input
                                v-model.number="vendorForm.vendor_cost"
                                type="number"
                                min="0"
                                step="5000"
                                class="pache-input tabular-nums"
                            />
                        </div>
                        <div class="flex justify-end sm:col-span-3">
                            <button
                                type="submit"
                                :disabled="vendorForm.processing"
                                class="pache-btn-outline"
                            >
                                Simpan Penugasan Vendor
                            </button>
                        </div>
                    </form>
                </section>

                <!-- State Machine Transitions (BR-03) & e-POD (BR-07) -->
                <section class="pache-panel space-y-4 p-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-[16px] font-[650] text-[var(--ink)]">
                            Kontrol Status Pengiriman (BR-03 State Machine)
                        </h2>
                        <span
                            class="inline-flex items-center gap-1 text-[12px] font-semibold text-[#15803d]"
                        >
                            <ShieldCheck class="h-4 w-4" />
                            Non-COD Protected
                        </span>
                    </div>

                    <div
                        v-if="shipment.allowed_transitions.length"
                        class="flex flex-wrap gap-2"
                    >
                        <button
                            v-for="tr in shipment.allowed_transitions.filter(
                                (t) => t.value !== 'delivered',
                            )"
                            :key="tr.value"
                            type="button"
                            @click="transitionTo(tr.value)"
                            class="pache-btn-outline"
                        >
                            Lanjut ke: {{ tr.label }}
                        </button>
                    </div>
                    <p v-else class="text-[13px] text-[var(--muted)]">
                        Resi telah mencapai status final ({{
                            shipment.status_label
                        }}).
                    </p>

                    <!-- Mandatory e-POD Form (BR-07) -->
                    <div
                        v-if="shipment.status === 'out_for_delivery'"
                        class="rounded-[12px] border border-[#bfdbfe] bg-[#eff6ff] p-4 dark:border-[#1d4ed8]/40 dark:bg-[#1e3a8a]/20"
                    >
                        <h3
                            class="text-[14px] font-bold text-[#1d4ed8] dark:text-[#93c5fd]"
                        >
                            Unggah Bukti Terima Digital (e-POD — BR-07 · Tanpa
                            Penagihan Tunai)
                        </h3>
                        <form
                            @submit.prevent="submitEpod"
                            class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2"
                        >
                            <div>
                                <label
                                    class="mb-1 block text-[11.5px] font-semibold"
                                    >Nama Jelas Penerima</label
                                >
                                <input
                                    v-model="epodForm.receiver_name"
                                    type="text"
                                    required
                                    class="pache-input"
                                    placeholder="Nama penerima di lokasi"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-[11.5px] font-semibold"
                                    >Relasi Penerima</label
                                >
                                <select
                                    v-model="epodForm.receiver_relation"
                                    class="pache-input"
                                >
                                    <option>Yang Bersangkutan</option>
                                    <option>Keluarga</option>
                                    <option>Resepsionis</option>
                                    <option>Sekuriti</option>
                                    <option>Kepala Gudang</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-[11.5px] font-semibold"
                                    >Latitude GPS</label
                                >
                                <input
                                    v-model.number="epodForm.latitude"
                                    type="number"
                                    step="0.000001"
                                    required
                                    class="pache-input tabular-nums"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-[11.5px] font-semibold"
                                    >Longitude GPS</label
                                >
                                <input
                                    v-model.number="epodForm.longitude"
                                    type="number"
                                    step="0.000001"
                                    required
                                    class="pache-input tabular-nums"
                                />
                            </div>
                            <div class="sm:col-span-2">
                                <button
                                    type="submit"
                                    :disabled="epodForm.processing"
                                    class="pache-btn-primary"
                                >
                                    <CheckCircle2 class="h-4 w-4" />
                                    <span
                                        >Simpan e-POD &amp; Selesaikan
                                        Pengiriman (Delivered)</span
                                    >
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Saved e-POD Box -->
                    <div
                        v-if="shipment.epod"
                        class="rounded-[12px] border border-[#bbf7d0] bg-[#ecfdf3] p-4 text-[13px] text-[#15803d] dark:border-[#15803d]/40 dark:bg-[#14532d]/25 dark:text-[#86efac]"
                    >
                        <strong class="block font-bold"
                            >e-POD Terverifikasi ({{
                                shipment.epod.delivered_at
                            }})</strong
                        >
                        <p class="mt-1">
                            Penerima:
                            <strong>{{ shipment.epod.receiver_name }}</strong>
                            ({{ shipment.epod.receiver_relation }}) · GPS:
                            <span class="tabular-nums"
                                >{{ shipment.epod.latitude }},
                                {{ shipment.epod.longitude }}</span
                            >
                        </p>
                        <p v-if="shipment.epod.notes" class="mt-1 opacity-90">
                            {{ shipment.epod.notes }}
                        </p>
                    </div>
                </section>
            </div>

            <!-- Right Timeline -->
            <section class="pache-panel p-5">
                <h2 class="mb-4 text-[16px] font-[650] text-[var(--ink)]">
                    Riwayat Pelacakan (Tracking Logs)
                </h2>
                <ol
                    class="relative ml-2 space-y-5 border-l-2 border-[var(--hairline)] pl-5"
                >
                    <li
                        v-for="(log, idx) in shipment.logs"
                        :key="log.id"
                        class="relative"
                    >
                        <span
                            class="absolute top-1 -left-[27px] h-3.5 w-3.5 rounded-full border-2 border-[var(--surface)]"
                            :class="
                                idx === 0
                                    ? 'bg-[var(--brand-blue)]'
                                    : 'bg-[var(--muted)]'
                            "
                        />
                        <p
                            class="text-[11.5px] font-semibold text-[var(--muted)] tabular-nums"
                        >
                            {{ log.created_at }}
                        </p>
                        <p
                            class="mt-0.5 flex items-center gap-1.5 text-[13.5px] font-bold text-[var(--ink)]"
                        >
                            <MapPin
                                class="h-3.5 w-3.5 text-[var(--brand-blue)]"
                            />
                            <span>{{ log.location }}</span>
                        </p>
                        <p class="mt-0.5 text-[13px] text-[var(--muted)]">
                            {{ log.description }}
                        </p>
                    </li>
                </ol>
            </section>
        </div>
    </div>
</template>
