<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { formatIdr } from '@/lib/format';
import type { CustomerRow } from '@/types/erp';

defineProps<{
    customers: CustomerRow[];
}>();

const custForm = useForm({
    name: '',
    type: 'b2b_contract' as 'retail' | 'b2b_contract',
    npwp: '',
    phone: '',
    email: '',
    address: '',
    is_top_enabled: true,
    top_days: 30,
    credit_limit: 50000000,
});

function saveCustomer(): void {
    custForm.post('/master/customers', {
        preserveScroll: true,
        onSuccess: () =>
            custForm.reset('name', 'npwp', 'phone', 'email', 'address'),
    });
}
</script>

<template>
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-[0.85fr_1.15fr]">
        <section class="pache-panel p-5">
            <h2 class="text-[16px] font-[650] text-[var(--ink)]">
                Tambah Pelanggan / Kontrak TOP
            </h2>
            <form @submit.prevent="saveCustomer" class="mt-4 space-y-3">
                <div>
                    <label
                        class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                        >Nama Perusahaan / Pelanggan</label
                    >
                    <input
                        v-model="custForm.name"
                        type="text"
                        required
                        class="pache-input"
                        placeholder="PT Contoh Logistik"
                    />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >Tipe Akun</label
                        >
                        <select v-model="custForm.type" class="pache-input">
                            <option value="b2b_contract">
                                B2B Kontrak (TOP)
                            </option>
                            <option value="retail">
                                Retail (Online / Cash)
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >NPWP</label
                        >
                        <input
                            v-model="custForm.npwp"
                            type="text"
                            class="pache-input tabular-nums"
                            placeholder="01.xxx.xxx.x-xxx.000"
                        />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >Telepon</label
                        >
                        <input
                            v-model="custForm.phone"
                            type="text"
                            required
                            class="pache-input tabular-nums"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >Email Penagihan</label
                        >
                        <input
                            v-model="custForm.email"
                            type="email"
                            class="pache-input"
                        />
                    </div>
                </div>
                <div>
                    <label
                        class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                        >Alamat</label
                    >
                    <textarea
                        v-model="custForm.address"
                        rows="2"
                        required
                        class="pache-input"
                    />
                </div>
                <div
                    v-if="custForm.type === 'b2b_contract'"
                    class="grid grid-cols-2 gap-3 rounded-[11px] bg-[var(--surface-soft)] p-3"
                >
                    <div>
                        <label
                            class="mb-1 block text-[11.5px] font-semibold text-[var(--muted)]"
                            >Termin TOP (Hari)</label
                        >
                        <select
                            v-model.number="custForm.top_days"
                            class="pache-input"
                        >
                            <option :value="7">Net 7 Hari</option>
                            <option :value="14">Net 14 Hari</option>
                            <option :value="30">Net 30 Hari</option>
                            <option :value="45">Net 45 Hari</option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[11.5px] font-semibold text-[var(--muted)]"
                            >Credit Limit (IDR)</label
                        >
                        <input
                            v-model.number="custForm.credit_limit"
                            type="number"
                            min="0"
                            step="5000000"
                            class="pache-input tabular-nums"
                        />
                    </div>
                </div>
                <button
                    type="submit"
                    :disabled="custForm.processing"
                    class="pache-btn-primary w-full"
                >
                    <Plus class="h-4 w-4" />
                    <span>Simpan Pelanggan</span>
                </button>
            </form>
        </section>

        <section class="pache-panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="pache-table">
                    <thead>
                        <tr>
                            <th>Kode &amp; Nama</th>
                            <th>Skema Bayar</th>
                            <th>Plafon Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c in customers" :key="c.id">
                            <td>
                                <strong class="block">{{ c.name }}</strong>
                                <span
                                    class="text-[12px] text-[var(--muted)] tabular-nums"
                                    >{{ c.code }} · {{ c.phone }}</span
                                >
                            </td>
                            <td>
                                <span
                                    class="pache-status-pill"
                                    :class="
                                        c.is_top_enabled
                                            ? 'is-warning'
                                            : 'is-info'
                                    "
                                >
                                    {{
                                        c.is_top_enabled
                                            ? `TOP Net ${c.top_days}h`
                                            : 'Online / Cash'
                                    }}
                                </span>
                            </td>
                            <td class="tabular-nums">
                                <template v-if="c.is_top_enabled">
                                    <strong>{{
                                        formatIdr(c.credit_exposure)
                                    }}</strong>
                                    <span class="text-[var(--muted)]">
                                        / {{ formatIdr(c.credit_limit) }}</span
                                    >
                                </template>
                                <span v-else class="text-[var(--muted)]"
                                    >Non-Kredit (Bayar di Muka)</span
                                >
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>
