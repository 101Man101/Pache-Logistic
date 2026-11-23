<script setup lang="ts">
import { Plus, Trash2 } from '@lucide/vue';
import { computed } from 'vue';
import { calculateDraftTotals, type DraftLineRow } from '@/lib/finance';
import { formatIdr } from '@/lib/format';

const props = withDefaults(
    defineProps<{
        defaultUnit?: string;
        descriptionPlaceholder?: string;
    }>(),
    {
        defaultUnit: 'jasa',
        descriptionPlaceholder: 'Deskripsi layanan / biaya...',
    },
);

const lines = defineModel<DraftLineRow[]>('lines', { required: true });
const applyPpn = defineModel<boolean>('applyPpn', { required: true });
const applyPph23 = defineModel<boolean>('applyPph23', { required: true });

const totals = computed(() =>
    calculateDraftTotals(lines.value, applyPpn.value, applyPph23.value),
);

function addRow(): void {
    lines.value = [
        ...lines.value,
        {
            shipment_id: null,
            description: '',
            quantity: 1,
            unit: props.defaultUnit,
            unit_price: 0,
        },
    ];
}

function removeRow(index: number): void {
    if (lines.value.length <= 1) {
        return;
    }
    lines.value = lines.value.filter((_, idx) => idx !== index);
}
</script>

<template>
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <label
                class="text-[12px] font-bold tracking-[0.06em] text-[var(--muted)] uppercase"
            >
                Rincian Item Tagihan
            </label>
            <button
                type="button"
                @click="addRow"
                class="pache-btn-outline min-h-[32px] px-3 text-[12px]"
            >
                <Plus class="h-3.5 w-3.5" />
                <span>Tambah Baris</span>
            </button>
        </div>

        <div
            class="overflow-x-auto rounded-[10px] border border-[var(--hairline)]"
        >
            <table class="pache-table">
                <thead>
                    <tr>
                        <th>Deskripsi</th>
                        <th class="w-[100px]">Qty</th>
                        <th class="w-[110px]">Satuan</th>
                        <th class="w-[170px]">Harga Satuan (Rp)</th>
                        <th class="w-[150px] text-right">Jumlah</th>
                        <th class="w-[54px]"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, idx) in lines" :key="idx">
                        <td>
                            <input
                                v-model="row.description"
                                type="text"
                                required
                                :placeholder="descriptionPlaceholder"
                                class="pache-input min-h-[36px] text-[13px]"
                            />
                            <span
                                v-if="row.shipment_id"
                                class="mt-0.5 block text-[11px] font-semibold text-[var(--brand-blue)]"
                            >
                                Terhubung ke Resi AWB #{{ row.shipment_id }}
                            </span>
                        </td>
                        <td>
                            <input
                                v-model.number="row.quantity"
                                type="number"
                                step="0.01"
                                min="0.01"
                                required
                                class="pache-input min-h-[36px] text-[13px] tabular-nums"
                            />
                        </td>
                        <td>
                            <input
                                v-model="row.unit"
                                type="text"
                                maxlength="24"
                                placeholder="unit"
                                class="pache-input min-h-[36px] text-[13px]"
                            />
                        </td>
                        <td>
                            <input
                                v-model.number="row.unit_price"
                                type="number"
                                step="1"
                                min="0"
                                required
                                class="pache-input min-h-[36px] text-[13px] tabular-nums"
                            />
                        </td>
                        <td class="text-right font-bold tabular-nums">
                            {{
                                formatIdr(
                                    Number(row.quantity || 0) *
                                        Number(row.unit_price || 0),
                                )
                            }}
                        </td>
                        <td class="text-right">
                            <button
                                type="button"
                                :disabled="lines.length <= 1"
                                @click="removeRow(idx)"
                                class="pache-btn-outline min-h-[32px] px-2 text-[#b42318] disabled:opacity-35"
                                title="Hapus baris"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Tax Options & Live Totals -->
        <div
            class="flex flex-col justify-between gap-4 rounded-[10px] border border-[var(--hairline)] bg-[var(--surface-soft)] px-4 py-3.5 sm:flex-row sm:items-center"
        >
            <div class="flex flex-wrap items-center gap-5 text-[13px]">
                <label class="inline-flex cursor-pointer items-center gap-2">
                    <input
                        v-model="applyPpn"
                        type="checkbox"
                        class="h-4 w-4 rounded border-[var(--hairline)] accent-[var(--brand-blue)]"
                    />
                    <span class="font-semibold text-[var(--ink)]"
                        >Kenakan PPN 11%</span
                    >
                </label>
                <label class="inline-flex cursor-pointer items-center gap-2">
                    <input
                        v-model="applyPph23"
                        type="checkbox"
                        class="h-4 w-4 rounded border-[var(--hairline)] accent-[var(--brand-blue)]"
                    />
                    <span class="font-semibold text-[var(--ink)]"
                        >Potong PPh 23 (2%)</span
                    >
                </label>
            </div>

            <div
                class="flex flex-wrap items-center gap-4 text-[13px] tabular-nums"
            >
                <span class="text-[var(--muted)]"
                    >Subtotal:
                    <strong class="text-[var(--ink)]">{{
                        formatIdr(totals.subtotal)
                    }}</strong></span
                >
                <span v-if="applyPpn" class="text-[var(--muted)]"
                    >PPN:
                    <strong class="text-[var(--ink)]"
                        >+{{ formatIdr(totals.ppn) }}</strong
                    ></span
                >
                <span v-if="applyPph23" class="text-[#b42318]"
                    >PPh 23:
                    <strong>−{{ formatIdr(totals.pph23) }}</strong></span
                >
                <span class="text-[14.5px] font-bold text-[var(--brand-blue)]"
                    >Grand Total: {{ formatIdr(totals.total) }}</span
                >
            </div>
        </div>
    </div>
</template>
