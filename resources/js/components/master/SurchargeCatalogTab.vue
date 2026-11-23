<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus } from '@lucide/vue';
import { formatIdr } from '@/lib/format';
import type { SurchargeCalcMode, SurchargeRow } from '@/types/erp';

defineProps<{
    surcharges: SurchargeRow[];
}>();

const surchargeForm = useForm({
    code: '',
    name: '',
    calc_mode: 'fixed' as SurchargeCalcMode,
    amount: 25000,
    description: '',
    is_active: true,
});

const calcModeLabels: Record<string, string> = {
    fixed: 'Nominal Tetap (IDR)',
    percentage: 'Persentase Ongkir (%)',
    per_kg: 'Per Kilogram (IDR/kg)',
};

function editSurcharge(s: SurchargeRow): void {
    surchargeForm.code = s.code;
    surchargeForm.name = s.name;
    surchargeForm.calc_mode = s.calc_mode;
    surchargeForm.amount = s.amount;
    surchargeForm.description = s.description ?? '';
    surchargeForm.is_active = s.is_active;
}

function saveSurcharge(): void {
    surchargeForm.post('/master/surcharges', {
        preserveScroll: true,
        onSuccess: () => surchargeForm.reset('code', 'name', 'description'),
    });
}

function toggleSurcharge(id: number): void {
    router.patch(
        `/master/surcharges/${id}/toggle`,
        {},
        { preserveScroll: true },
    );
}
</script>

<template>
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-[0.82fr_1.18fr]">
        <section class="pache-panel p-5">
            <h2 class="text-[16px] font-[650] text-[var(--ink)]">
                Tambah / Edit Master Surcharge
            </h2>
            <p class="mt-1 text-[12px] text-[var(--muted)]">
                Preset biaya tambahan terstandar (FSC, Packing Kayu, ODA, Heavy
                Handling) yang dapat dipilih saat booking atau add charge.
            </p>

            <form @submit.prevent="saveSurcharge" class="mt-4 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >Kode Surcharge</label
                        >
                        <input
                            v-model="surchargeForm.code"
                            type="text"
                            required
                            class="pache-input uppercase tabular-nums"
                            placeholder="PACK-KAYU"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >Mode Perhitungan</label
                        >
                        <select
                            v-model="surchargeForm.calc_mode"
                            class="pache-input"
                        >
                            <option value="fixed">Nominal Tetap (IDR)</option>
                            <option value="percentage">
                                Persentase Ongkir (%)
                            </option>
                            <option value="per_kg">Per Kg (IDR/kg)</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label
                        class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                        >Nama Surcharge</label
                    >
                    <input
                        v-model="surchargeForm.name"
                        type="text"
                        required
                        class="pache-input"
                        placeholder="Packing Kayu Standar"
                    />
                </div>
                <div>
                    <label
                        class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                    >
                        {{
                            surchargeForm.calc_mode === 'percentage'
                                ? 'Persentase (%)'
                                : 'Nominal (IDR)'
                        }}
                    </label>
                    <input
                        v-model.number="surchargeForm.amount"
                        type="number"
                        min="0"
                        step="any"
                        required
                        class="pache-input tabular-nums"
                    />
                </div>
                <div>
                    <label
                        class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                        >Keterangan</label
                    >
                    <textarea
                        v-model="surchargeForm.description"
                        rows="2"
                        class="pache-input"
                        placeholder="Penjelasan penerapan biaya"
                    />
                </div>
                <label
                    class="flex items-center gap-2 text-[13px] font-semibold text-[var(--ink)]"
                >
                    <input
                        v-model="surchargeForm.is_active"
                        type="checkbox"
                        class="h-4 w-4 rounded"
                    />
                    <span>Aktif untuk pemilihan di OMS</span>
                </label>
                <button
                    type="submit"
                    :disabled="surchargeForm.processing"
                    class="pache-btn-primary w-full"
                >
                    <Plus class="h-4 w-4" />
                    <span>Simpan Surcharge</span>
                </button>
            </form>
        </section>

        <section class="pache-panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="pache-table">
                    <thead>
                        <tr>
                            <th>Kode &amp; Nama</th>
                            <th>Skema Hitung</th>
                            <th>Tarif</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="s in surcharges" :key="s.id">
                            <td>
                                <strong class="block text-[var(--ink)]">{{
                                    s.name
                                }}</strong>
                                <span
                                    class="text-[11.5px] font-bold text-[var(--brand-blue)] tabular-nums"
                                    >{{ s.code }}</span
                                >
                                <span
                                    v-if="s.description"
                                    class="block text-[12px] text-[var(--muted)]"
                                    >{{ s.description }}</span
                                >
                            </td>
                            <td class="text-[12.5px]">
                                {{ calcModeLabels[s.calc_mode] ?? s.calc_mode }}
                            </td>
                            <td class="font-bold tabular-nums">
                                {{
                                    s.calc_mode === 'percentage'
                                        ? `${s.amount}%`
                                        : formatIdr(s.amount)
                                }}
                            </td>
                            <td>
                                <button
                                    type="button"
                                    @click="toggleSurcharge(s.id)"
                                    class="pache-status-pill"
                                    :class="
                                        s.is_active ? 'is-success' : 'is-danger'
                                    "
                                >
                                    {{ s.is_active ? 'AKTIF' : 'NONAKTIF' }}
                                </button>
                            </td>
                            <td class="text-right">
                                <button
                                    type="button"
                                    @click="editSurcharge(s)"
                                    class="pache-btn-outline min-h-[30px] px-2.5 text-[12px]"
                                >
                                    <Pencil class="h-3.5 w-3.5" />
                                    <span>Edit</span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>
