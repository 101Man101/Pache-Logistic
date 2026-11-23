<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Pencil } from '@lucide/vue';
import { formatIdr } from '@/lib/format';
import type { BranchRow, ServiceType, TariffRow } from '@/types/erp';

const props = defineProps<{
    branches: BranchRow[];
    tariffs: TariffRow[];
}>();

const tariffForm = useForm({
    origin_branch_id: props.branches[0]?.id ?? 0,
    destination_branch_id: props.branches[1]?.id ?? 0,
    service_type: 'REG' as ServiceType,
    volumetric_divisor: 6000,
    min_weight_kg: 1,
    rate_per_kg: 12000,
    insurance_rate_pct: 0.25,
    fuel_surcharge_pct: 0,
    handling_fee: 0,
    heavy_weight_threshold_kg: null as number | null,
    heavy_weight_surcharge_per_kg: 0,
    sla_days: 2,
    is_active: true,
});

function editTariff(t: TariffRow): void {
    tariffForm.origin_branch_id = t.origin_branch_id;
    tariffForm.destination_branch_id = t.destination_branch_id;
    tariffForm.service_type = t.service_type;
    tariffForm.volumetric_divisor = t.volumetric_divisor;
    tariffForm.min_weight_kg = t.min_weight_kg;
    tariffForm.rate_per_kg = t.rate_per_kg;
    tariffForm.insurance_rate_pct = t.insurance_rate_pct;
    tariffForm.fuel_surcharge_pct = t.fuel_surcharge_pct;
    tariffForm.handling_fee = t.handling_fee;
    tariffForm.heavy_weight_threshold_kg = t.heavy_weight_threshold_kg;
    tariffForm.heavy_weight_surcharge_per_kg = t.heavy_weight_surcharge_per_kg;
    tariffForm.sla_days = t.sla_days;
    tariffForm.is_active = t.is_active;
}

function saveTariff(): void {
    tariffForm.post('/master/tariffs', { preserveScroll: true });
}

function toggleTariff(id: number): void {
    router.patch(`/master/tariffs/${id}/toggle`, {}, { preserveScroll: true });
}
</script>

<template>
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-[0.88fr_1.12fr]">
        <section class="pache-panel p-5">
            <h2 class="text-[16px] font-[650] text-[var(--ink)]">
                Atur Tarif &amp; Surcharge Rute
            </h2>
            <p class="mt-1 text-[12px] text-[var(--muted)]">
                Klik <strong>Edit</strong> pada tabel di kanan untuk memuat dan
                mengubah tarif rute yang sudah ada.
            </p>

            <form @submit.prevent="saveTariff" class="mt-4 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >Asal</label
                        >
                        <select
                            v-model="tariffForm.origin_branch_id"
                            class="pache-input"
                        >
                            <option
                                v-for="b in branches"
                                :key="b.id"
                                :value="b.id"
                            >
                                {{ b.code }} — {{ b.city }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >Tujuan</label
                        >
                        <select
                            v-model="tariffForm.destination_branch_id"
                            class="pache-input"
                        >
                            <option
                                v-for="b in branches"
                                :key="b.id"
                                :value="b.id"
                            >
                                {{ b.code }} — {{ b.city }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >Layanan</label
                        >
                        <select
                            v-model="tariffForm.service_type"
                            class="pache-input"
                        >
                            <option value="REG">REG (Reguler)</option>
                            <option value="EXP">EXP (Express)</option>
                            <option value="CRG">CRG (Cargo)</option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >Pembagi Volumetrik</label
                        >
                        <select
                            v-model.number="tariffForm.volumetric_divisor"
                            class="pache-input tabular-nums"
                        >
                            <option :value="6000">6000 (Darat)</option>
                            <option :value="5000">5000 (Udara)</option>
                            <option :value="4000">4000 (Cargo Truk)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >Tarif / kg (IDR)</label
                        >
                        <input
                            v-model.number="tariffForm.rate_per_kg"
                            type="number"
                            min="1000"
                            step="500"
                            class="pache-input tabular-nums"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >Min Berat (kg)</label
                        >
                        <input
                            v-model.number="tariffForm.min_weight_kg"
                            type="number"
                            min="1"
                            step="0.5"
                            class="pache-input tabular-nums"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >SLA (Hari)</label
                        >
                        <input
                            v-model.number="tariffForm.sla_days"
                            type="number"
                            min="1"
                            max="30"
                            class="pache-input tabular-nums"
                        />
                    </div>
                </div>

                <div
                    class="space-y-3 rounded-[12px] border border-[var(--hairline)] bg-[var(--surface-soft)] p-3.5"
                >
                    <p
                        class="text-[11px] font-bold tracking-[0.08em] text-[var(--brand-blue)] uppercase"
                    >
                        Pengaturan Asuransi &amp; Surcharge Otomatis Rute
                    </p>
                    <div class="grid grid-cols-3 gap-2.5">
                        <div>
                            <label
                                class="mb-1 block text-[11px] font-semibold text-[var(--muted)]"
                                >Asuransi (%)</label
                            >
                            <input
                                v-model.number="tariffForm.insurance_rate_pct"
                                type="number"
                                min="0"
                                max="10"
                                step="0.05"
                                class="pache-input tabular-nums"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-[11px] font-semibold text-[var(--muted)]"
                                >Fuel Surcharge (%)</label
                            >
                            <input
                                v-model.number="tariffForm.fuel_surcharge_pct"
                                type="number"
                                min="0"
                                max="50"
                                step="0.5"
                                class="pache-input tabular-nums"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-[11px] font-semibold text-[var(--muted)]"
                                >Handling Fee (Rp)</label
                            >
                            <input
                                v-model.number="tariffForm.handling_fee"
                                type="number"
                                min="0"
                                step="1000"
                                class="pache-input tabular-nums"
                            />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label
                                class="mb-1 block text-[11px] font-semibold text-[var(--muted)]"
                                >Ambang Heavy Weight (kg)</label
                            >
                            <input
                                v-model.number="
                                    tariffForm.heavy_weight_threshold_kg
                                "
                                type="number"
                                min="1"
                                step="5"
                                class="pache-input tabular-nums"
                                placeholder="Opsional, misal 100"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-[11px] font-semibold text-[var(--muted)]"
                                >Surcharge Berat/kg (Rp)</label
                            >
                            <input
                                v-model.number="
                                    tariffForm.heavy_weight_surcharge_per_kg
                                "
                                type="number"
                                min="0"
                                step="500"
                                class="pache-input tabular-nums"
                            />
                        </div>
                    </div>
                </div>

                <label
                    class="flex items-center gap-2 text-[13px] font-semibold text-[var(--ink)]"
                >
                    <input
                        v-model="tariffForm.is_active"
                        type="checkbox"
                        class="h-4 w-4 rounded"
                    />
                    <span>Tarif aktif digunakan pada Booking OMS</span>
                </label>

                <button
                    type="submit"
                    :disabled="tariffForm.processing"
                    class="pache-btn-primary w-full"
                >
                    Simpan Pengaturan Tarif Rute
                </button>
            </form>
        </section>

        <section class="pache-panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="pache-table">
                    <thead>
                        <tr>
                            <th>Rute &amp; Layanan</th>
                            <th>Tarif / kg</th>
                            <th>Surcharge Rute</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="t in tariffs" :key="t.id">
                            <td>
                                <strong class="block"
                                    >{{ t.origin }} →
                                    {{ t.destination }}</strong
                                >
                                <span
                                    class="text-[11.5px] text-[var(--muted)] tabular-nums"
                                >
                                    {{ t.service_type }} · ÷{{
                                        t.volumetric_divisor
                                    }}
                                    · Min {{ t.min_weight_kg }} kg · SLA
                                    {{ t.sla_days }}h
                                </span>
                            </td>
                            <td class="tabular-nums">
                                <strong class="block">{{
                                    formatIdr(t.rate_per_kg)
                                }}</strong>
                                <span class="text-[11px] text-[var(--muted)]"
                                    >Asuransi {{ t.insurance_rate_pct }}%</span
                                >
                            </td>
                            <td class="text-[12px] tabular-nums">
                                <div v-if="t.fuel_surcharge_pct > 0">
                                    FSC {{ t.fuel_surcharge_pct }}%
                                </div>
                                <div v-if="t.handling_fee > 0">
                                    Handling {{ formatIdr(t.handling_fee) }}
                                </div>
                                <div
                                    v-if="
                                        t.heavy_weight_threshold_kg &&
                                        t.heavy_weight_surcharge_per_kg > 0
                                    "
                                >
                                    &gt;{{ t.heavy_weight_threshold_kg }}kg: +{{
                                        formatIdr(
                                            t.heavy_weight_surcharge_per_kg,
                                        )
                                    }}/kg
                                </div>
                                <span
                                    v-if="
                                        t.fuel_surcharge_pct === 0 &&
                                        t.handling_fee === 0 &&
                                        !t.heavy_weight_threshold_kg
                                    "
                                    class="text-[var(--muted)]"
                                >
                                    Standar (0)
                                </span>
                            </td>
                            <td>
                                <button
                                    type="button"
                                    @click="toggleTariff(t.id)"
                                    class="pache-status-pill"
                                    :class="
                                        t.is_active ? 'is-success' : 'is-danger'
                                    "
                                >
                                    {{ t.is_active ? 'AKTIF' : 'NONAKTIF' }}
                                </button>
                            </td>
                            <td class="text-right">
                                <button
                                    type="button"
                                    @click="editTariff(t)"
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
