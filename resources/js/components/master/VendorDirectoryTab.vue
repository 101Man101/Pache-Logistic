<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus } from '@lucide/vue';
import { formatIdr } from '@/lib/format';
import type { VendorRow, VendorType } from '@/types/erp';

defineProps<{
    vendors: VendorRow[];
}>();

const vendorForm = useForm({
    code: '',
    name: '',
    type: 'linehaul' as VendorType,
    contact_person: '',
    phone: '',
    email: '',
    address: '',
    payment_terms_days: 14,
    base_cost_per_kg: 5000,
    notes: '',
    is_active: true,
});

const vendorTypeLabels: Record<string, string> = {
    linehaul: 'Linehaul Truk',
    air_freight: 'Kargo Udara (SMU)',
    sea_freight: 'Kargo Laut',
    last_mile: 'Kurir Last-Mile',
    customs: 'Kepabeanan / Forwarding',
};

function editVendor(v: VendorRow): void {
    vendorForm.code = v.code;
    vendorForm.name = v.name;
    vendorForm.type = v.type;
    vendorForm.contact_person = v.contact_person ?? '';
    vendorForm.phone = v.phone ?? '';
    vendorForm.email = v.email ?? '';
    vendorForm.address = v.address ?? '';
    vendorForm.payment_terms_days = v.payment_terms_days;
    vendorForm.base_cost_per_kg = v.base_cost_per_kg;
    vendorForm.notes = v.notes ?? '';
    vendorForm.is_active = v.is_active;
}

function saveVendor(): void {
    vendorForm.post('/master/vendors', {
        preserveScroll: true,
        onSuccess: () =>
            vendorForm.reset(
                'code',
                'name',
                'contact_person',
                'phone',
                'email',
                'address',
                'notes',
            ),
    });
}

function toggleVendor(id: number): void {
    router.patch(`/master/vendors/${id}/toggle`, {}, { preserveScroll: true });
}
</script>

<template>
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-[0.85fr_1.15fr]">
        <section class="pache-panel p-5">
            <h2 class="text-[16px] font-[650] text-[var(--ink)]">
                Tambah / Edit Vendor &amp; Mitra 3PL
            </h2>
            <p class="mt-1 text-[12px] text-[var(--muted)]">
                Daftarkan vendor maskapai, linehaul truk, kapal laut, atau mitra
                last-mile untuk ditugaskan di OMS &amp; TMS.
            </p>

            <form @submit.prevent="saveVendor" class="mt-4 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >Kode Vendor (opsional)</label
                        >
                        <input
                            v-model="vendorForm.code"
                            type="text"
                            class="pache-input uppercase tabular-nums"
                            placeholder="Auto VND-3PL-xxx"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >Jenis Layanan Vendor</label
                        >
                        <select v-model="vendorForm.type" class="pache-input">
                            <option value="linehaul">Linehaul Truk</option>
                            <option value="air_freight">
                                Kargo Udara (SMU)
                            </option>
                            <option value="sea_freight">Kargo Laut</option>
                            <option value="last_mile">Kurir Last-Mile</option>
                            <option value="customs">
                                Kepabeanan / Forwarding
                            </option>
                        </select>
                    </div>
                </div>
                <div>
                    <label
                        class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                        >Nama Vendor</label
                    >
                    <input
                        v-model="vendorForm.name"
                        type="text"
                        required
                        class="pache-input"
                        placeholder="PT Mitra Kargo Ekspres"
                    />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >PIC / Kontak</label
                        >
                        <input
                            v-model="vendorForm.contact_person"
                            type="text"
                            class="pache-input"
                            placeholder="Nama PIC"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >No. Telepon</label
                        >
                        <input
                            v-model="vendorForm.phone"
                            type="text"
                            required
                            class="pache-input tabular-nums"
                            placeholder="021-xxxx-xxxx"
                        />
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >Email</label
                        >
                        <input
                            v-model="vendorForm.email"
                            type="email"
                            class="pache-input"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >TOP Vendor (Hari)</label
                        >
                        <input
                            v-model.number="vendorForm.payment_terms_days"
                            type="number"
                            min="0"
                            max="90"
                            class="pache-input tabular-nums"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >Base Cost / kg (Rp)</label
                        >
                        <input
                            v-model.number="vendorForm.base_cost_per_kg"
                            type="number"
                            min="0"
                            step="500"
                            class="pache-input tabular-nums"
                        />
                    </div>
                </div>
                <div>
                    <label
                        class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                        >Alamat / Pool Vendor</label
                    >
                    <textarea
                        v-model="vendorForm.address"
                        rows="2"
                        class="pache-input"
                    />
                </div>
                <div>
                    <label
                        class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                        >Catatan</label
                    >
                    <input
                        v-model="vendorForm.notes"
                        type="text"
                        class="pache-input"
                        placeholder="Rute spesialis / ketentuan SMU"
                    />
                </div>
                <label
                    class="flex items-center gap-2 text-[13px] font-semibold text-[var(--ink)]"
                >
                    <input
                        v-model="vendorForm.is_active"
                        type="checkbox"
                        class="h-4 w-4 rounded"
                    />
                    <span>Vendor aktif</span>
                </label>
                <button
                    type="submit"
                    :disabled="vendorForm.processing"
                    class="pache-btn-primary w-full"
                >
                    <Plus class="h-4 w-4" />
                    <span>Simpan Vendor</span>
                </button>
            </form>
        </section>

        <section class="pache-panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="pache-table">
                    <thead>
                        <tr>
                            <th>Kode &amp; Vendor</th>
                            <th>Layanan</th>
                            <th>Base Cost / kg</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="v in vendors" :key="v.id">
                            <td>
                                <strong class="block">{{ v.name }}</strong>
                                <span
                                    class="text-[11.5px] font-bold text-[var(--brand-blue)] tabular-nums"
                                    >{{ v.code }}</span
                                >
                                <span
                                    v-if="v.phone"
                                    class="text-[12px] text-[var(--muted)] tabular-nums"
                                >
                                    · {{ v.phone }}</span
                                >
                                <span
                                    v-if="v.contact_person"
                                    class="block text-[11.5px] text-[var(--muted)]"
                                    >PIC: {{ v.contact_person }} · TOP
                                    {{ v.payment_terms_days }}h</span
                                >
                            </td>
                            <td class="text-[12.5px]">
                                {{ vendorTypeLabels[v.type] ?? v.type }}
                            </td>
                            <td class="font-bold tabular-nums">
                                {{ formatIdr(v.base_cost_per_kg) }}
                            </td>
                            <td>
                                <button
                                    type="button"
                                    @click="toggleVendor(v.id)"
                                    class="rounded-[6px] border px-2 py-0.5 text-[11px] font-bold"
                                    :class="
                                        v.is_active
                                            ? 'border-[#bbf7d0] bg-[#ecfdf3] text-[#15803d]'
                                            : 'border-[#fecaca] bg-[#fef2f2] text-[#991b1b]'
                                    "
                                >
                                    {{ v.is_active ? 'AKTIF' : 'NONAKTIF' }}
                                </button>
                            </td>
                            <td class="text-right">
                                <button
                                    type="button"
                                    @click="editVendor(v)"
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
