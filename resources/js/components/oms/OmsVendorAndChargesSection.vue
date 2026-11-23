<script setup lang="ts">
import { Handshake, Percent, Plus, Trash2 } from '@lucide/vue';
import { formatIdr } from '@/lib/format';
import type {
    AdditionalChargeInput,
    ServiceType,
    SurchargeOption,
    VendorOption,
} from '@/types/erp';

defineProps<{
    vendors: VendorOption[];
    surcharges: SurchargeOption[];
    serviceType: ServiceType;
    additionalCharges: AdditionalChargeInput[];
    routeSurchargeBreakdown: {
        fuel: number;
        handling: number;
        heavy: number;
        total: number;
    };
    computeSurchargeAmount: (s: SurchargeOption) => number;
}>();

const vendorId = defineModel<number | null>('vendorId', { required: true });
const vendorAwbNo = defineModel<string>('vendorAwbNo', { required: true });
const vendorCost = defineModel<number>('vendorCost', { required: true });
const surchargeIds = defineModel<number[]>('surchargeIds', { required: true });

const emit = defineEmits<{
    applyVendorBaseCost: [];
    addChargeRow: [];
    removeChargeRow: [idx: number];
    updateChargeField: [
        idx: number,
        field: 'description' | 'amount',
        value: string | number,
    ];
}>();
</script>

<template>
    <section class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <!-- Vendor Assignment -->
        <div class="pache-panel space-y-3.5 p-5">
            <div class="flex items-center gap-2">
                <Handshake class="h-4 w-4 text-[var(--brand-blue)]" />
                <h2 class="text-[16px] font-[650] text-[var(--ink)]">
                    2A. Penugasan Vendor 3PL (Opsional)
                </h2>
            </div>
            <p class="text-[12px] text-[var(--muted)]">
                Pilih mitra maskapai, linehaul truk, atau last-mile jika
                pengiriman diteruskan lewat vendor eksternal.
            </p>

            <div>
                <label
                    class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                    >Mitra Vendor</label
                >
                <select v-model="vendorId" class="pache-input">
                    <option :value="null">
                        — Armada Internal Pache Logistic —
                    </option>
                    <option v-for="v in vendors" :key="v.id" :value="v.id">
                        {{ v.code }} · {{ v.name }} ({{
                            formatIdr(v.base_cost_per_kg)
                        }}/kg)
                    </option>
                </select>
            </div>

            <div v-if="vendorId" class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label
                        class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                        >No. Resi / SMU Vendor</label
                    >
                    <input
                        v-model="vendorAwbNo"
                        type="text"
                        class="pache-input tabular-nums"
                        placeholder="SMU-126-889120"
                    />
                </div>
                <div>
                    <div class="mb-1 flex items-center justify-between">
                        <label
                            class="text-[12px] font-semibold text-[var(--muted)]"
                            >Biaya Beli Vendor (IDR)</label
                        >
                        <button
                            type="button"
                            @click="emit('applyVendorBaseCost')"
                            class="text-[11px] font-bold text-[var(--brand-blue)] hover:underline"
                        >
                            Hitung Otomatis
                        </button>
                    </div>
                    <input
                        v-model.number="vendorCost"
                        type="number"
                        min="0"
                        step="5000"
                        class="pache-input tabular-nums"
                    />
                </div>
            </div>
        </div>

        <!-- Surcharge & Add Charge -->
        <div class="pache-panel space-y-3.5 p-5">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <Percent class="h-4 w-4 text-[var(--brand-blue)]" />
                    <h2 class="text-[16px] font-[650] text-[var(--ink)]">
                        2B. Surcharge &amp; Add Charge
                    </h2>
                </div>
                <button
                    type="button"
                    @click="emit('addChargeRow')"
                    class="pache-btn-outline min-h-[32px] px-3 text-[12px]"
                >
                    <Plus class="h-3.5 w-3.5" />
                    <span>Add Charge</span>
                </button>
            </div>

            <!-- Automatic Route Surcharge Preview -->
            <div
                v-if="routeSurchargeBreakdown.total > 0"
                class="rounded-[10px] border border-[#bfdbfe] bg-[#eff6ff] p-3 text-[12px] text-[#1d4ed8] dark:border-[#1d4ed8]/40 dark:bg-[#1e3a8a]/25 dark:text-[#93c5fd]"
            >
                <strong class="block"
                    >Surcharge Otomatis Rute ({{ serviceType }}):</strong
                >
                <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 tabular-nums">
                    <span v-if="routeSurchargeBreakdown.fuel > 0"
                        >Fuel Surcharge:
                        {{ formatIdr(routeSurchargeBreakdown.fuel) }}</span
                    >
                    <span v-if="routeSurchargeBreakdown.handling > 0"
                        >Handling:
                        {{ formatIdr(routeSurchargeBreakdown.handling) }}</span
                    >
                    <span v-if="routeSurchargeBreakdown.heavy > 0"
                        >Heavy Weight:
                        {{ formatIdr(routeSurchargeBreakdown.heavy) }}</span
                    >
                </div>
            </div>

            <!-- Preset Surcharges Checkboxes -->
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <label
                    v-for="s in surcharges"
                    :key="s.id"
                    class="flex cursor-pointer items-center justify-between gap-2 rounded-[10px] border px-3 py-2 text-[12px] transition"
                    :class="
                        surchargeIds.includes(s.id)
                            ? 'border-[var(--brand-blue)] bg-[#eff6ff] dark:bg-[#1e3a8a]/25'
                            : 'border-[var(--hairline)] bg-[var(--surface-soft)]'
                    "
                >
                    <span class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            :value="s.id"
                            v-model="surchargeIds"
                            class="h-4 w-4 rounded accent-[#2563eb]"
                        />
                        <span class="font-semibold text-[var(--ink)]">{{
                            s.name
                        }}</span>
                    </span>
                    <span
                        class="font-bold text-[var(--brand-blue)] tabular-nums"
                    >
                        {{ formatIdr(computeSurchargeAmount(s)) }}
                    </span>
                </label>
            </div>

            <!-- Custom Add Charge Rows -->
            <div
                v-if="additionalCharges.length"
                class="space-y-2 border-t border-[var(--hairline)] pt-3"
            >
                <p
                    class="text-[11px] font-bold tracking-[0.06em] text-[var(--muted)] uppercase"
                >
                    Biaya Tambahan Custom (Add Charge)
                </p>
                <div
                    v-for="(chg, idx) in additionalCharges"
                    :key="idx"
                    class="grid grid-cols-1 gap-2 sm:grid-cols-[1.2fr_0.75fr_auto]"
                >
                    <input
                        :value="chg.description"
                        @input="
                            emit(
                                'updateChargeField',
                                idx,
                                'description',
                                ($event.target as HTMLInputElement).value,
                            )
                        "
                        type="text"
                        required
                        class="pache-input"
                        placeholder="Deskripsi biaya (misal Sewa Forklift)"
                    />
                    <input
                        :value="chg.amount"
                        @input="
                            emit(
                                'updateChargeField',
                                idx,
                                'amount',
                                Number(
                                    ($event.target as HTMLInputElement).value,
                                ) || 0,
                            )
                        "
                        type="number"
                        min="1"
                        step="1000"
                        required
                        class="pache-input tabular-nums"
                        placeholder="Nominal Rp"
                    />
                    <button
                        type="button"
                        @click="emit('removeChargeRow', idx)"
                        class="rounded-lg p-2 text-[var(--muted)] hover:bg-[#fef2f2] hover:text-[#b42318]"
                        title="Hapus Add Charge"
                    >
                        <Trash2 class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>
