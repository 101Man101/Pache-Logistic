<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ShieldCheck } from '@lucide/vue';
import { computed, toRef, watch } from 'vue';
import OmsBookingSummaryStrip from '@/components/oms/OmsBookingSummaryStrip.vue';
import OmsColliTableSection from '@/components/oms/OmsColliTableSection.vue';
import OmsPaymentMethodSection from '@/components/oms/OmsPaymentMethodSection.vue';
import OmsVendorAndChargesSection from '@/components/oms/OmsVendorAndChargesSection.vue';
import {
    useChargeableWeight,
    type PackageColli,
} from '@/composables/useChargeableWeight';
import type {
    AdditionalChargeInput,
    BranchOption,
    CustomerOption,
    PaymentMethod,
    ServiceType,
    SurchargeOption,
    TariffOption,
    VendorOption,
} from '@/types/erp';

const props = defineProps<{
    customers: CustomerOption[];
    branches: BranchOption[];
    tariffs: TariffOption[];
    vendors: VendorOption[];
    surcharges: SurchargeOption[];
}>();

const form = useForm({
    customer_id: props.customers[0]?.id ?? 0,
    origin_branch_id: props.branches[0]?.id ?? 0,
    destination_branch_id: props.branches[2]?.id ?? props.branches[1]?.id ?? 0,
    vendor_id: null as number | null,
    vendor_awb_no: '',
    vendor_cost: 0,
    reference_no: '',
    shipper_name: props.customers[0]?.name ?? '',
    shipper_phone: props.customers[0]?.phone ?? '',
    shipper_address: props.customers[0]?.address ?? '',
    consignee_name: '',
    consignee_phone: '',
    consignee_address: '',
    service_type: 'REG' as ServiceType,
    payment_method: 'online' as PaymentMethod,
    declared_value: 0,
    cash_received: true,
    surcharge_ids: [] as number[],
    additional_charges: [] as AdditionalChargeInput[],
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

const selectedVendor = computed(
    () => props.vendors.find((v) => v.id === form.vendor_id) ?? null,
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
    if (activeTariff.value)
        return Number(activeTariff.value.volumetric_divisor);
    return form.service_type === 'EXP'
        ? 5000
        : form.service_type === 'CRG'
          ? 4000
          : 6000;
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
    const pct = activeTariff.value
        ? Number(activeTariff.value.insurance_rate_pct)
        : 0.25;
    return val > 0 ? Math.round(val * (pct / 100)) : 0;
});

const routeSurchargeBreakdown = computed(() => {
    const t = activeTariff.value;
    if (!t) {
        return { fuel: 0, handling: 0, heavy: 0, total: 0 };
    }
    const fuel =
        Number(t.fuel_surcharge_pct) > 0
            ? Math.round(
                  estimatedShippingCost.value *
                      (Number(t.fuel_surcharge_pct) / 100),
              )
            : 0;
    const handling =
        Number(t.handling_fee) > 0 ? Math.round(Number(t.handling_fee)) : 0;
    const threshold =
        t.heavy_weight_threshold_kg !== null
            ? Number(t.heavy_weight_threshold_kg)
            : null;
    const heavyRate = Number(t.heavy_weight_surcharge_per_kg) || 0;
    const heavy =
        threshold !== null &&
        weightSummary.value.chargeableKg > threshold &&
        heavyRate > 0
            ? Math.round(
                  (weightSummary.value.chargeableKg - threshold) * heavyRate,
              )
            : 0;

    return {
        fuel,
        handling,
        heavy,
        total: fuel + handling + heavy,
    };
});

function computeSurchargeAmount(s: SurchargeOption): number {
    const rate = Number(s.amount) || 0;
    if (s.calc_mode === 'percentage') {
        return Math.round(estimatedShippingCost.value * (rate / 100));
    }
    if (s.calc_mode === 'per_kg') {
        return Math.round(weightSummary.value.chargeableKg * rate);
    }
    return Math.round(rate);
}

const selectedPresetSurchargesTotal = computed(() =>
    props.surcharges
        .filter((s) => form.surcharge_ids.includes(s.id))
        .reduce((sum, s) => sum + computeSurchargeAmount(s), 0),
);

const estimatedSurchargeTotal = computed(
    () =>
        routeSurchargeBreakdown.value.total +
        selectedPresetSurchargesTotal.value,
);

const estimatedAddChargeTotal = computed(() =>
    form.additional_charges.reduce(
        (sum, c) => sum + Math.max(0, Number(c.amount) || 0),
        0,
    ),
);

const estimatedTotal = computed(
    () =>
        estimatedShippingCost.value +
        estimatedInsurance.value +
        estimatedSurchargeTotal.value +
        estimatedAddChargeTotal.value,
);

const topWillExceedLimit = computed(() => {
    if (form.payment_method !== 'top' || !selectedCustomer.value) return false;
    return (
        selectedCustomer.value.credit_exposure + estimatedTotal.value >
        selectedCustomer.value.credit_limit
    );
});

function applyVendorBaseCost(): void {
    if (!selectedVendor.value) return;
    form.vendor_cost = Math.round(
        weightSummary.value.chargeableKg *
            Number(selectedVendor.value.base_cost_per_kg || 0),
    );
}

function addChargeRow(): void {
    form.additional_charges.push({
        description: '',
        amount: 25000,
        notes: '',
    });
}

function removeChargeRow(idx: number): void {
    form.additional_charges.splice(idx, 1);
}

function updateChargeField(
    idx: number,
    field: 'description' | 'amount',
    value: string | number,
): void {
    const row = form.additional_charges[idx];
    if (!row) return;
    if (field === 'description') {
        row.description = String(value);
    } else {
        row.amount = Number(value) || 0;
    }
}

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

function updateColliField(
    idx: number,
    field: keyof PackageColli,
    value: string | number,
): void {
    const item = form.items[idx];
    if (!item) return;
    if (field === 'description') {
        item.description = String(value);
    } else {
        item[field] = Number(value) || 0;
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
                <Link
                    href="/oms/shipments"
                    class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-[var(--brand-blue)] no-underline hover:underline"
                >
                    <ArrowLeft class="h-3.5 w-3.5" />
                    <span>Kembali ke Daftar Resi</span>
                </Link>
                <h1 class="pache-page-title">
                    Buat Resi Pengiriman Baru (Multi-Koli · Non-COD)
                </h1>
            </div>
            <div class="pache-status-pill is-info px-3 py-1.5 text-[12px]">
                <ShieldCheck class="h-4 w-4" />
                <span>Hanya Online Payment, Cash Loket, &amp; B2B TOP</span>
            </div>
        </div>

        <OmsBookingSummaryStrip
            :divisor="divisor"
            :actual-formatted="weightSummary.actualFormatted"
            :volumetric-formatted="weightSummary.volumetricFormatted"
            :chargeable-kg="weightSummary.chargeableKg"
            :surcharge-and-add-charge-total="
                estimatedSurchargeTotal + estimatedAddChargeTotal
            "
            :rate-per-kg="ratePerKg"
            :estimated-total="estimatedTotal"
        />

        <!-- Section 1: Customer, Route & Service -->
        <section class="pache-panel space-y-4 p-5">
            <h2 class="text-[16px] font-[650] text-[var(--ink)]">
                1. Pelanggan, Rute Hub &amp; Layanan
            </h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label
                        class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]"
                        >Pelanggan / Akun Shipper</label
                    >
                    <select v-model="form.customer_id" class="pache-input">
                        <option
                            v-for="c in customers"
                            :key="c.id"
                            :value="c.id"
                        >
                            {{ c.name }} ({{
                                c.is_top_enabled
                                    ? `TOP ${c.top_days}h`
                                    : 'Retail'
                            }})
                        </option>
                    </select>
                </div>
                <div>
                    <label
                        class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]"
                        >Cabang / Hub Asal</label
                    >
                    <select v-model="form.origin_branch_id" class="pache-input">
                        <option v-for="b in branches" :key="b.id" :value="b.id">
                            {{ b.code }} — {{ b.city }}
                        </option>
                    </select>
                </div>
                <div>
                    <label
                        class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]"
                        >Cabang / Hub Tujuan</label
                    >
                    <select
                        v-model="form.destination_branch_id"
                        class="pache-input"
                    >
                        <option v-for="b in branches" :key="b.id" :value="b.id">
                            {{ b.code }} — {{ b.city }}
                        </option>
                    </select>
                    <p
                        v-if="form.errors.destination_branch_id"
                        class="mt-1 text-[12px] text-[#b42318]"
                    >
                        {{ form.errors.destination_branch_id }}
                    </p>
                </div>
                <div>
                    <label
                        class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]"
                        >Layanan &amp; Pembagi</label
                    >
                    <select v-model="form.service_type" class="pache-input">
                        <option value="REG">REG — Reguler (÷ 6000)</option>
                        <option value="EXP">
                            EXP — Express Udara (÷ 5000)
                        </option>
                        <option value="CRG">CRG — Cargo Truk (÷ 4000)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label
                        class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]"
                        >Nomor Referensi / PO Klien (opsional)</label
                    >
                    <input
                        v-model="form.reference_no"
                        type="text"
                        class="pache-input"
                        placeholder="PO-2026-8812"
                    />
                </div>
                <div>
                    <label
                        class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]"
                    >
                        Nilai Barang untuk Asuransi
                        {{ activeTariff?.insurance_rate_pct ?? 0.25 }}% (IDR,
                        opsional)
                    </label>
                    <input
                        v-model.number="form.declared_value"
                        type="number"
                        min="0"
                        step="50000"
                        class="pache-input tabular-nums"
                    />
                </div>
            </div>
        </section>

        <OmsVendorAndChargesSection
            v-model:vendor-id="form.vendor_id"
            v-model:vendor-awb-no="form.vendor_awb_no"
            v-model:vendor-cost="form.vendor_cost"
            v-model:surcharge-ids="form.surcharge_ids"
            :vendors="vendors"
            :surcharges="surcharges"
            :service-type="form.service_type"
            :additional-charges="form.additional_charges"
            :route-surcharge-breakdown="routeSurchargeBreakdown"
            :compute-surcharge-amount="computeSurchargeAmount"
            @apply-vendor-base-cost="applyVendorBaseCost"
            @add-charge-row="addChargeRow"
            @remove-charge-row="removeChargeRow"
            @update-charge-field="updateChargeField"
        />

        <OmsPaymentMethodSection
            v-model:payment-method="form.payment_method"
            v-model:cash-received="form.cash_received"
            :selected-customer="selectedCustomer"
            :estimated-total="estimatedTotal"
            :top-will-exceed-limit="topWillExceedLimit"
        />

        <!-- Section 4 & 5: Shipper & Consignee -->
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="pache-panel space-y-3 p-5">
                <h2 class="text-[16px] font-[650] text-[var(--ink)]">
                    4. Data Pengirim (Shipper)
                </h2>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >Nama Pengirim</label
                        >
                        <input
                            v-model="form.shipper_name"
                            type="text"
                            required
                            class="pache-input"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >Telepon Pengirim</label
                        >
                        <input
                            v-model="form.shipper_phone"
                            type="text"
                            required
                            class="pache-input tabular-nums"
                        />
                    </div>
                </div>
                <div>
                    <label
                        class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                        >Alamat Pengirim</label
                    >
                    <textarea
                        v-model="form.shipper_address"
                        rows="2"
                        required
                        class="pache-input"
                    />
                </div>
            </div>

            <div class="pache-panel space-y-3 p-5">
                <h2 class="text-[16px] font-[650] text-[var(--ink)]">
                    5. Data Penerima (Consignee)
                </h2>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >Nama Penerima</label
                        >
                        <input
                            v-model="form.consignee_name"
                            type="text"
                            required
                            class="pache-input"
                            placeholder="PT / Nama Lengkap"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >No. HP Penerima</label
                        >
                        <input
                            v-model="form.consignee_phone"
                            type="text"
                            required
                            class="pache-input tabular-nums"
                            placeholder="0812xxxxxxxx"
                        />
                    </div>
                </div>
                <div>
                    <label
                        class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                        >Alamat Lengkap Tujuan</label
                    >
                    <textarea
                        v-model="form.consignee_address"
                        rows="2"
                        required
                        class="pache-input"
                        placeholder="Jalan, No., Kecamatan, Kota"
                    />
                </div>
            </div>
        </section>

        <OmsColliTableSection
            :items="form.items"
            :processing="form.processing"
            :estimated-shipping-cost="estimatedShippingCost"
            :estimated-insurance="estimatedInsurance"
            :estimated-surcharge-total="estimatedSurchargeTotal"
            :estimated-add-charge-total="estimatedAddChargeTotal"
            :estimated-total="estimatedTotal"
            @add-colli="addColli"
            @remove-colli="removeColli"
            @update-colli-field="updateColliField"
        />
    </form>
</template>
