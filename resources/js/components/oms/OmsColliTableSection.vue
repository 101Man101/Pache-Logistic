<script setup lang="ts">
import { Plus, Trash2 } from '@lucide/vue';
import type { PackageColli } from '@/composables/useChargeableWeight';
import { formatIdr } from '@/lib/format';

defineProps<{
    items: PackageColli[];
    processing: boolean;
    estimatedShippingCost: number;
    estimatedInsurance: number;
    estimatedSurchargeTotal: number;
    estimatedAddChargeTotal: number;
    estimatedTotal: number;
}>();

const emit = defineEmits<{
    addColli: [];
    removeColli: [idx: number];
    updateColliField: [
        idx: number,
        field: keyof PackageColli,
        value: string | number,
    ];
}>();
</script>

<template>
    <section class="pache-panel space-y-4 p-5">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-[16px] font-[650] text-[var(--ink)]">
                    6. Rincian Koli &amp; Dimensi
                </h2>
                <p class="text-[12px] text-[var(--muted)]">
                    Berat aktual dan dimensi P×L×T langsung menghitung berat
                    tagih secara reaktif.
                </p>
            </div>
            <button
                type="button"
                @click="emit('addColli')"
                class="pache-btn-outline"
            >
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
                    <tr v-for="(item, idx) in items" :key="idx">
                        <td>
                            <input
                                :value="item.description"
                                @input="
                                    emit(
                                        'updateColliField',
                                        idx,
                                        'description',
                                        ($event.target as HTMLInputElement)
                                            .value,
                                    )
                                "
                                type="text"
                                required
                                class="pache-input"
                                placeholder="Isi paket"
                            />
                        </td>
                        <td>
                            <input
                                :value="item.qty"
                                @input="
                                    emit(
                                        'updateColliField',
                                        idx,
                                        'qty',
                                        Number(
                                            ($event.target as HTMLInputElement)
                                                .value,
                                        ) || 0,
                                    )
                                "
                                type="number"
                                min="1"
                                required
                                class="pache-input tabular-nums"
                            />
                        </td>
                        <td>
                            <input
                                :value="item.actual_weight_kg"
                                @input="
                                    emit(
                                        'updateColliField',
                                        idx,
                                        'actual_weight_kg',
                                        Number(
                                            ($event.target as HTMLInputElement)
                                                .value,
                                        ) || 0,
                                    )
                                "
                                type="number"
                                min="0.1"
                                step="0.1"
                                required
                                class="pache-input tabular-nums"
                            />
                        </td>
                        <td>
                            <input
                                :value="item.length_cm"
                                @input="
                                    emit(
                                        'updateColliField',
                                        idx,
                                        'length_cm',
                                        Number(
                                            ($event.target as HTMLInputElement)
                                                .value,
                                        ) || 0,
                                    )
                                "
                                type="number"
                                min="1"
                                required
                                class="pache-input tabular-nums"
                            />
                        </td>
                        <td>
                            <input
                                :value="item.width_cm"
                                @input="
                                    emit(
                                        'updateColliField',
                                        idx,
                                        'width_cm',
                                        Number(
                                            ($event.target as HTMLInputElement)
                                                .value,
                                        ) || 0,
                                    )
                                "
                                type="number"
                                min="1"
                                required
                                class="pache-input tabular-nums"
                            />
                        </td>
                        <td>
                            <input
                                :value="item.height_cm"
                                @input="
                                    emit(
                                        'updateColliField',
                                        idx,
                                        'height_cm',
                                        Number(
                                            ($event.target as HTMLInputElement)
                                                .value,
                                        ) || 0,
                                    )
                                "
                                type="number"
                                min="1"
                                required
                                class="pache-input tabular-nums"
                            />
                        </td>
                        <td>
                            <button
                                type="button"
                                @click="emit('removeColli', idx)"
                                :disabled="items.length === 1"
                                class="rounded-lg p-2 text-[var(--muted)] hover:bg-[#fef2f2] hover:text-[#b42318] disabled:opacity-30"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            class="flex flex-wrap items-center justify-between gap-4 border-t border-[var(--hairline)] pt-4"
        >
            <div class="text-[13px] text-[var(--muted)] tabular-nums">
                Ongkir:
                <strong class="text-[var(--ink)]">{{
                    formatIdr(estimatedShippingCost)
                }}</strong>
                · Asuransi:
                <strong class="text-[var(--ink)]">{{
                    formatIdr(estimatedInsurance)
                }}</strong>
                · Surcharge:
                <strong class="text-[var(--ink)]">{{
                    formatIdr(estimatedSurchargeTotal)
                }}</strong>
                · Add Charge:
                <strong class="text-[var(--ink)]">{{
                    formatIdr(estimatedAddChargeTotal)
                }}</strong>
                · Total:
                <strong class="text-[16px] text-[var(--brand-blue)]">{{
                    formatIdr(estimatedTotal)
                }}</strong>
            </div>
            <button
                type="submit"
                :disabled="processing"
                class="pache-btn-primary px-6"
            >
                {{
                    processing
                        ? 'Menerbitkan AWB...'
                        : 'Terbitkan Resi (AWB Non-COD)'
                }}
            </button>
        </div>
    </section>
</template>
