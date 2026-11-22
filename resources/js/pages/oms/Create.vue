<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
    Building2,
    CreditCard,
    Plus,
    ReceiptText,
    ShieldCheck,
    Trash2,
} from '@lucide/vue';
import { computed, toRef, watch } from 'vue';
import {
    useChargeableWeight,
    type PackageColli,
} from '@/composables/useChargeableWeight';
import { formatIdr } from '@/lib/format';

interface CustomerOption {
    id: number;
    code: string;
    name: string;
    type: string;
    phone: string;
    address: string;
    is_top_enabled: boolean;
    top_days: number;
    credit_limit: number;
    credit_exposure: number;
}

interface BranchOption {
    id: number;
    code: string;
    name: string;
    city: string;
}

interface TariffOption {
    origin_branch_id: number;
    destination_branch_id: number;
    service_type: string;
    volumetric_divisor: number;
    min_weight_kg: number;
    rate_per_kg: number;
    insurance_rate_pct: number;
    sla_days: number;
}

const props = defineProps<{
    customers: CustomerOption[];
    branches: BranchOption[];
    tariffs: TariffOption[];
}>();

const form = useForm({
    customer_id: props.customers[0]?.id ?? 0,
    origin_branch_id: props.branches[0]?.id ?? 0,
    destination_branch_id: props.branches[2]?.id ?? props.branches[1]?.id ?? 0,
    reference_no: '',
    shipper_name: props.customers[0]?.name ?? '',
    shipper_phone: props.customers[0]?.phone ?? '',
    shipper_address: props.customers[0]?.address ?? '',
    consignee_name: '',
    consignee_phone: '',
    consignee_address: '',
    service_type: 'REG' as 'REG' | 'EXP' | 'CRG',
    payment_method: 'online' as 'online' | 'cash' | 'top',
    declared_value: 0,
    cash_received: true,
    items: [
        {
            description: 'Karton Barang Logistik',
            qty: 1,
            actual_weight_kg: 5,
            length_cm: 40,
            width_cm: 30,
            height_cm: 30,
        },
    ] as PackageColli[],
});

const selectedCustomer = computed(() =>
    props.customers.find((c) => c.id === form.customer_id),
);

watch(
    () => form.customer_id,
    () => {
        const c = selectedCustomer.value;
        if (!c) return;
        form.shipper_name = c.name;
        form.shipper_phone = c.phone;
        form.shipper_address = c.address;
        if (!c.is_top_enabled && form.payment_method === 'top') {
            form.payment_method = 'online';
        }
    },
);

const activeTariff = computed(() =>
    props.tariffs.find(
        (t) =>
            t.origin_branch_id === form.origin_branch_id &&
            t.destination_branch_id === form.destination_branch_id &&
            t.service_type === form.service_type,
    ),
);

const divisor = computed(() => {
    if (activeTariff.value) return Number(activeTariff.value.volumetric_divisor);
    return form.service_type === 'EXP' ? 5000 : form.service_type === 'CRG' ? 4000 : 6000;
});

const minWeight = computed(() =>
    activeTariff.value ? Number(activeTariff.value.min_weight_kg) : 1,
);

const ratePerKg = computed(() =>
    activeTariff.value ? Number(activeTariff.value.rate_per_kg) : 14000,
);

const weightSummary = useChargeableWeight(
    toRef(form, 'items'),
    divisor,
    minWeight,
);

const estimatedShippingCost = computed(
    () => weightSummary.value.chargeableKg * ratePerKg.value,
);

const estimatedInsurance = computed(() => {
    const val = Math.max(0, Number(form.declared_value) || 0);
    const pct = activeTariff.value ? Number(activeTariff.value.insurance_rate_pct) : 0.25;
    return val > 0 ? Math.round(val * (pct / 100)) : 0;
});

const estimatedTotal = computed(
    () => estimatedShippingCost.value + estimatedInsurance.value,
);

const topWillExceedLimit = computed(() => {
    if (form.payment_method !== 'top' || !selectedCustomer.value) return false;
    return (
        selectedCustomer.value.credit_exposure + estimatedTotal.value >
        selectedCustomer.value.credit_limit
    );
});

function addColli(): void {
    form.items.push({
        description: '',
        qty: 1,
        actual_weight_kg: 1,
        length_cm: 20,
        width_cm: 20,
        height_cm: 15,
    });
}

function removeColli(idx: number): void {
    if (form.items.length > 1) {
        form.items.splice(idx, 1);
    }
}

function submitBooking(): void {
    form.post('/oms/shipments', { preserveScroll: true });
}
</script>

<template>
    <Head title="Booking Resi Multi-Koli Non-COD" />

    <form @submit.prevent="submitBooking" class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <Link href="/oms/shipments" class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-[var(--brand-blue)] no-underline hover:underline">
                    <ArrowLeft class="h-3.5 w-3.5" />
                    <span>Kembali ke Daftar Resi</span>
                </Link>
                <h1 class="mt-1 text-[23px] font-[650] tracking-[-0.03em] text-[var(--ink)]">
                    Buat Resi Pengiriman Baru (Multi-Koli · Non-COD)
                </h1>
            </div>
            <div class="inline-flex items-center gap-2 rounded-[10px] border border-[#bfdbfe] bg-[#eff6ff] px-3 py-1.5 text-[12px] font-bold text-[#1d4ed8] dark:border-[#1d4ed8]/40 dark:bg-[#1e3a8a]/30 dark:text-[#93c5fd]">
                <ShieldCheck class="h-4 w-4" />
                <span>Hanya Online Payment, Cash Loket, &amp; B2B TOP</span>
            </div>
        </div>

        <!-- Live Weight & Cost Summary Strip -->
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="rounded-[14px] border border-[var(--hairline)] bg-[var(--surface-soft)] p-4">
                <span class="text-[11.5px] font-semibold text-[var(--muted)]">Berat Aktual Total</span>
                <p class="mt-1 text-[20px] font-bold text-[var(--ink)] tabular-nums">
                    {{ weightSummary.actualFormatted }} kg
                </p>
            </div>
            <div class="rounded-[14px] border border-[var(--hairline)] bg-[var(--surface-soft)] p-4">
                <span class="text-[11.5px] font-semibold text-[var(--muted)]">
                    Berat Volumetrik (÷ {{ divisor }})
                </span>
                <p class="mt-1 text-[20px] font-bold text-[var(--ink)] tabular-nums">
                    {{ weightSummary.volumetricFormatted }} kg
                </p>
            </div>
            <div class="rounded-[14px] border border-[#d3e5ff] bg-[#dcecff] p-4 text-[#214f8a] dark:border-[#303030] dark:bg-[#1a1a1a] dark:text-[var(--ink)]">
                <span class="text-[11.5px] font-semibold">Chargeable Weight (BR-01)</span>
                <p class="mt-1 text-[22px] font-bold tabular-nums">
                    {{ weightSummary.chargeableKg }} kg
                </p>
            </div>
            <div class="rounded-[14px] bg-[#2563eb] p-4 text-white">
                <span class="text-[11.5px] font-semibold text-blue-100">
                    Estimasi Total ({{ formatIdr(ratePerKg) }}/kg)
                </span>
                <p class="mt-1 text-[22px] font-bold tabular-nums">
                    {{ formatIdr(estimatedTotal) }}
                </p>
            </div>
        </div>

        <!-- Section 1: Customer, Route & Service -->
        <section class="pache-panel space-y-4 p-5">
            <h2 class="text-[16px] font-[650] text-[var(--ink)]">
                1. Pelanggan, Rute Hub &amp; Layanan
            </h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]">Pelanggan / Akun Shipper</label>
                    <select v-model="form.customer_id" class="pache-input">
                        <option v-for="c in customers" :key="c.id" :value="c.id">
                            {{ c.name }} ({{ c.is_top_enabled ? `TOP ${c.top_days}h` : 'Retail' }})
                        </option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]">Cabang / Hub Asal</label>
                    <select v-model="form.origin_branch_id" class="pache-input">
                        <option v-for="b in branches" :key="b.id" :value="b.id">
                            {{ b.code }} — {{ b.city }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]">Cabang / Hub Tujuan</label>
                    <select v-model="form.destination_branch_id" class="pache-input">
                        <option v-for="b in branches" :key="b.id" :value="b.id">
                            {{ b.code }} — {{ b.city }}
                        </option>
                    </select>
                    <p v-if="form.errors.destination_branch_id" class="mt-1 text-[12px] text-[#b42318]">
                        {{ form.errors.destination_branch_id }}
                    </p>
                </div>
                <div>
                    <label class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]">Layanan &amp; Pembagi</label>
                    <select v-model="form.service_type" class="pache-input">
                        <option value="REG">REG — Reguler (÷ 6000)</option>
                        <option value="EXP">EXP — Express Udara (÷ 5000)</option>
                        <option value="CRG">CRG — Cargo Truk (÷ 4000)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]">Nomor Referensi / PO Klien (opsional)</label>
                    <input v-model="form.reference_no" type="text" class="pache-input" placeholder="PO-2026-8812" />
                </div>
                <div>
                    <label class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]">Nilai Barang untuk Asuransi 0,25% (IDR, opsional)</label>
                    <input v-model.number="form.declared_value" type="number" min="0" step="50000" class="pache-input tabular-nums" />
                </div>
            </div>
        </section>

        <!-- Section 2: Strictly Non-COD Payment Selection -->
        <section class="pache-panel space-y-4 p-5">
            <div>
                <h2 class="text-[16px] font-[650] text-[var(--ink)]">
                    2. Metode Pembayaran (Strictly Non-COD)
                </h2>
                <p class="text-[12.5px] text-[var(--muted)]">
                    Sistem Pache Logistic menolak COD. Pilih salah satu dari 3 kanal pembayaran resmi di bawah ini.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                <!-- Online Payment -->
                <label
                    class="flex cursor-pointer flex-col gap-2 rounded-[13px] border p-4 transition"
                    :class="
                        form.payment_method === 'online'
                            ? 'border-[#2563eb] bg-[#eff6ff] dark:bg-[#1e3a8a]/25'
                            : 'border-[var(--hairline)] bg-[var(--surface-soft)]'
                    "
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-[var(--ink)]">
                            <CreditCard class="h-4 w-4 text-[#2563eb]" />
                            <span>Online Payment</span>
                        </div>
                        <input type="radio" value="online" v-model="form.payment_method" class="h-4 w-4 text-[#2563eb]" />
                    </div>
                    <p class="text-[12px] text-[var(--muted)]">
                        QRIS Dinamis / Virtual Account (BCA, Mandiri, BRI, BNI). Resi menunggu webhook lunas sebelum Manifest.
                    </p>
                </label>

                <!-- Cash Loket -->
                <label
                    class="flex cursor-pointer flex-col gap-2 rounded-[13px] border p-4 transition"
                    :class="
                        form.payment_method === 'cash'
                            ? 'border-[#15803d] bg-[#ecfdf3] dark:bg-[#14532d]/25'
                            : 'border-[var(--hairline)] bg-[var(--surface-soft)]'
                    "
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-[var(--ink)]">
                            <Building2 class="h-4 w-4 text-[#15803d]" />
                            <span>Cash (Loket Asal)</span>
                        </div>
                        <input type="radio" value="cash" v-model="form.payment_method" class="h-4 w-4 text-[#15803d]" />
                    </div>
                    <p class="text-[12px] text-[var(--muted)]">
                        Tunai di muka di loket cabang asal saat drop-off. Kurir last-mile tidak menagih penerima.
                    </p>
                </label>

                <!-- B2B TOP -->
                <label
                    class="flex flex-col gap-2 rounded-[13px] border p-4 transition"
                    :class="[
                        !selectedCustomer?.is_top_enabled
                            ? 'cursor-not-allowed opacity-55'
                            : 'cursor-pointer',
                        form.payment_method === 'top'
                            ? 'border-[#d3a56b] bg-[#f8eddf] dark:bg-[#78350f]/25'
                            : 'border-[var(--hairline)] bg-[var(--surface-soft)]',
                    ]"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-[var(--ink)]">
                            <ReceiptText class="h-4 w-4 text-[#b45309]" />
                            <span>B2B Credit (TOP)</span>
                        </div>
                        <input
                            type="radio"
                            value="top"
                            v-model="form.payment_method"
                            :disabled="!selectedCustomer?.is_top_enabled"
                            class="h-4 w-4 text-[#b45309]"
                        />
                    </div>
                    <p class="text-[12px] text-[var(--muted)]">
                        <template v-if="selectedCustomer?.is_top_enabled">
                            Kontrak aktif Net {{ selectedCustomer.top_days }} hari. Ditagihkan via Auto-Invoicing.
                        </template>
                        <template v-else>
                            Hanya tersedia untuk pelanggan kontrak korporat B2B.
                        </template>
                    </p>
                </label>
            </div>

            <!-- Cash Received Checkbox -->
            <div v-if="form.payment_method === 'cash'" class="rounded-[11px] border border-[#bbf7d0] bg-[#ecfdf3] p-3.5 dark:border-[#15803d]/40 dark:bg-[#14532d]/20">
                <label class="flex items-center gap-2.5 text-[13px] font-semibold text-[#15803d] dark:text-[#86efac]">
                    <input v-model="form.cash_received" type="checkbox" class="h-4 w-4 rounded" />
                    <span>Tunai sudah diterima lunas di Kasir Loket saat ini (langsung berstatus PAID &amp; masuk Jurnal Kas)</span>
                </label>
            </div>

            <!-- TOP Credit Limit Enforcement Preview (BR-02) -->
            <div
                v-if="form.payment_method === 'top' && selectedCustomer"
                class="rounded-[12px] border p-4"
                :class="
                    topWillExceedLimit
                        ? 'border-[#fecaca] bg-[#fef2f2] text-[#991b1b]'
                        : 'border-[#e7cfa8] bg-[#f8eddf]/60 text-[#78350f] dark:border-[#b45309]/40 dark:bg-[#78350f]/20 dark:text-[#fcd34d]'
                "
            >
                <div class="flex flex-wrap items-center justify-between gap-2 text-[13px]">
                    <strong class="flex items-center gap-1.5">
                        <AlertTriangle v-if="topWillExceedLimit" class="h-4 w-4" />
                        <span>Pengecekan Plafon Kredit B2B (BR-02)</span>
                    </strong>
                    <span class="font-bold tabular-nums">
                        Eksposur Baru: {{ formatIdr(selectedCustomer.credit_exposure + estimatedTotal) }} / Limit: {{ formatIdr(selectedCustomer.credit_limit) }}
                    </span>
                </div>
                <p v-if="topWillExceedLimit" class="mt-1 text-[12px] font-semibold">
                    Melebihi Credit Limit! Backend akan menolak penerbitan resi ini hingga ada pelunasan Invoice TOP.
                </p>
            </div>
        </section>

        <!-- Section 3: Shipper & Consignee -->
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="pache-panel space-y-3 p-5">
                <h2 class="text-[16px] font-[650] text-[var(--ink)]">3. Data Pengirim (Shipper)</h2>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[12px] font-semibold text-[var(--muted)]">Nama Pengirim</label>
                        <input v-model="form.shipper_name" type="text" required class="pache-input" />
                    </div>
                    <div>
                        <label class="mb-1 block text-[12px] font-semibold text-[var(--muted)]">Telepon Pengirim</label>
                        <input v-model="form.shipper_phone" type="text" required class="pache-input tabular-nums" />
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-[12px] font-semibold text-[var(--muted)]">Alamat Pengirim</label>
                    <textarea v-model="form.shipper_address" rows="2" required class="pache-input" />
                </div>
            </div>

            <div class="pache-panel space-y-3 p-5">
                <h2 class="text-[16px] font-[650] text-[var(--ink)]">4. Data Penerima (Consignee)</h2>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[12px] font-semibold text-[var(--muted)]">Nama Penerima</label>
                        <input v-model="form.consignee_name" type="text" required class="pache-input" placeholder="PT / Nama Lengkap" />
                    </div>
                    <div>
                        <label class="mb-1 block text-[12px] font-semibold text-[var(--muted)]">No. HP Penerima</label>
                        <input v-model="form.consignee_phone" type="text" required class="pache-input tabular-nums" placeholder="0812xxxxxxxx" />
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-[12px] font-semibold text-[var(--muted)]">Alamat Lengkap Tujuan</label>
                    <textarea v-model="form.consignee_address" rows="2" required class="pache-input" placeholder="Jalan, No., Kecamatan, Kota" />
                </div>
            </div>
        </section>

        <!-- Section 4: Multi-Koli Table -->
        <section class="pache-panel space-y-4 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-[16px] font-[650] text-[var(--ink)]">5. Rincian Koli &amp; Dimensi</h2>
                    <p class="text-[12px] text-[var(--muted)]">Berat aktual dan dimensi P×L×T langsung menghitung berat tagih secara reaktif.</p>
                </div>
                <button type="button" @click="addColli" class="pache-btn-outline">
                    <Plus class="h-4 w-4" />
                    <span>Tambah Koli</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="pache-table">
                    <thead>
                        <tr>
                            <th>Deskripsi Isi Koli</th>
                            <th class="w-20">Qty</th>
                            <th class="w-28">Berat (kg)</th>
                            <th class="w-24">P (cm)</th>
                            <th class="w-24">L (cm)</th>
                            <th class="w-24">T (cm)</th>
                            <th class="w-12" />
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, idx) in form.items" :key="idx">
                            <td>
                                <input v-model="item.description" type="text" required class="pache-input" placeholder="Isi paket" />
                            </td>
                            <td>
                                <input v-model.number="item.qty" type="number" min="1" required class="pache-input tabular-nums" />
                            </td>
                            <td>
                                <input v-model.number="item.actual_weight_kg" type="number" min="0.1" step="0.1" required class="pache-input tabular-nums" />
                            </td>
                            <td>
                                <input v-model.number="item.length_cm" type="number" min="1" required class="pache-input tabular-nums" />
                            </td>
                            <td>
                                <input v-model.number="item.width_cm" type="number" min="1" required class="pache-input tabular-nums" />
                            </td>
                            <td>
                                <input v-model.number="item.height_cm" type="number" min="1" required class="pache-input tabular-nums" />
                            </td>
                            <td>
                                <button
                                    type="button"
                                    @click="removeColli(idx)"
                                    :disabled="form.items.length === 1"
                                    class="rounded-lg p-2 text-[var(--muted)] hover:bg-[#fef2f2] hover:text-[#b42318] disabled:opacity-30"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4 border-t border-[var(--hairline)] pt-4">
                <div class="text-[13px] text-[var(--muted)] tabular-nums">
                    Ongkir: <strong class="text-[var(--ink)]">{{ formatIdr(estimatedShippingCost) }}</strong> ·
                    Asuransi: <strong class="text-[var(--ink)]">{{ formatIdr(estimatedInsurance) }}</strong> ·
                    Total: <strong class="text-[16px] text-[var(--brand-blue)]">{{ formatIdr(estimatedTotal) }}</strong>
                </div>
                <button type="submit" :disabled="form.processing" class="pache-btn-primary px-6">
                    {{ form.processing ? 'Menerbitkan AWB...' : 'Terbitkan Resi (AWB Non-COD)' }}
                </button>
            </div>
        </section>
    </form>
</template>
