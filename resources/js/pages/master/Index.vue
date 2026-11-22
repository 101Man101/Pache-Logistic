<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Building2, Map, Plus, Users } from '@lucide/vue';
import { ref } from 'vue';
import { formatIdr } from '@/lib/format';

interface BranchRow {
    id: number;
    code: string;
    name: string;
    type: string;
    city: string;
    address: string;
    phone: string | null;
}

interface CustomerRow {
    id: number;
    code: string;
    name: string;
    type: string;
    npwp: string | null;
    phone: string;
    email: string | null;
    address: string;
    is_top_enabled: boolean;
    top_days: number;
    credit_limit: number;
    credit_exposure: number;
}

interface TariffRow {
    id: number;
    origin: string;
    destination: string;
    service_type: string;
    volumetric_divisor: number;
    min_weight_kg: number;
    rate_per_kg: number;
    insurance_rate_pct: number;
    sla_days: number;
}

const props = defineProps<{
    branches: BranchRow[];
    customers: CustomerRow[];
    tariffs: TariffRow[];
}>();

const activeTab = ref<'customers' | 'tariffs' | 'branches'>('customers');

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

const tariffForm = useForm({
    origin_branch_id: props.branches[0]?.id ?? 0,
    destination_branch_id: props.branches[1]?.id ?? 0,
    service_type: 'REG' as 'REG' | 'EXP' | 'CRG',
    volumetric_divisor: 6000,
    min_weight_kg: 1,
    rate_per_kg: 12000,
    sla_days: 2,
});

function saveCustomer(): void {
    custForm.post('/master/customers', {
        preserveScroll: true,
        onSuccess: () => custForm.reset('name', 'npwp', 'phone', 'email', 'address'),
    });
}

function saveTariff(): void {
    tariffForm.post('/master/tariffs', { preserveScroll: true });
}
</script>

<template>
    <Head title="Master Data & Matriks Tarif" />

    <div class="space-y-6">
        <div>
            <p class="text-[10.5px] font-bold tracking-[0.14em] text-[var(--brand-blue)] uppercase">
                MASTER DATA MANAGEMENT (MDM)
            </p>
            <h1 class="mt-1 text-[23px] font-[650] tracking-[-0.03em] text-[var(--ink)]">
                Cabang, Pelanggan B2B TOP &amp; Matriks Tarif Non-COD
            </h1>
        </div>

        <div class="inline-flex flex-wrap gap-1 rounded-[12px] bg-[var(--surface-soft)] p-1">
            <button
                type="button"
                @click="activeTab = 'customers'"
                class="inline-flex items-center gap-2 rounded-[9px] px-4 py-2 text-[13px] font-semibold"
                :class="activeTab === 'customers' ? 'bg-[var(--surface)] text-[var(--brand-blue)] shadow-xs' : 'text-[var(--muted)]'"
            >
                <Users class="h-4 w-4" />
                <span>Pelanggan &amp; Kontrak TOP ({{ customers.length }})</span>
            </button>
            <button
                type="button"
                @click="activeTab = 'tariffs'"
                class="inline-flex items-center gap-2 rounded-[9px] px-4 py-2 text-[13px] font-semibold"
                :class="activeTab === 'tariffs' ? 'bg-[var(--surface)] text-[var(--brand-blue)] shadow-xs' : 'text-[var(--muted)]'"
            >
                <Map class="h-4 w-4" />
                <span>Matriks Tarif ({{ tariffs.length }})</span>
            </button>
            <button
                type="button"
                @click="activeTab = 'branches'"
                class="inline-flex items-center gap-2 rounded-[9px] px-4 py-2 text-[13px] font-semibold"
                :class="activeTab === 'branches' ? 'bg-[var(--surface)] text-[var(--brand-blue)] shadow-xs' : 'text-[var(--muted)]'"
            >
                <Building2 class="h-4 w-4" />
                <span>Cabang &amp; Hub ({{ branches.length }})</span>
            </button>
        </div>

        <!-- TAB 1: CUSTOMERS -->
        <div v-if="activeTab === 'customers'" class="grid grid-cols-1 gap-5 lg:grid-cols-[0.85fr_1.15fr]">
            <section class="pache-panel p-5">
                <h2 class="text-[16px] font-[650] text-[var(--ink)]">Tambah Pelanggan / Kontrak TOP</h2>
                <form @submit.prevent="saveCustomer" class="mt-4 space-y-3">
                    <div>
                        <label class="mb-1 block text-[12px] font-semibold text-[var(--muted)]">Nama Perusahaan / Pelanggan</label>
                        <input v-model="custForm.name" type="text" required class="pache-input" placeholder="PT Contoh Logistik" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-[12px] font-semibold text-[var(--muted)]">Tipe Akun</label>
                            <select v-model="custForm.type" class="pache-input">
                                <option value="b2b_contract">B2B Kontrak (TOP)</option>
                                <option value="retail">Retail (Online / Cash)</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-[12px] font-semibold text-[var(--muted)]">NPWP</label>
                            <input v-model="custForm.npwp" type="text" class="pache-input tabular-nums" placeholder="01.xxx.xxx.x-xxx.000" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-[12px] font-semibold text-[var(--muted)]">Telepon</label>
                            <input v-model="custForm.phone" type="text" required class="pache-input tabular-nums" />
                        </div>
                        <div>
                            <label class="mb-1 block text-[12px] font-semibold text-[var(--muted)]">Email Penagihan</label>
                            <input v-model="custForm.email" type="email" class="pache-input" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-[12px] font-semibold text-[var(--muted)]">Alamat</label>
                        <textarea v-model="custForm.address" rows="2" required class="pache-input" />
                    </div>
                    <div v-if="custForm.type === 'b2b_contract'" class="grid grid-cols-2 gap-3 rounded-[11px] bg-[var(--surface-soft)] p-3">
                        <div>
                            <label class="mb-1 block text-[11.5px] font-semibold text-[var(--muted)]">Termin TOP (Hari)</label>
                            <select v-model.number="custForm.top_days" class="pache-input">
                                <option :value="7">Net 7 Hari</option>
                                <option :value="14">Net 14 Hari</option>
                                <option :value="30">Net 30 Hari</option>
                                <option :value="45">Net 45 Hari</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-[11.5px] font-semibold text-[var(--muted)]">Credit Limit (IDR)</label>
                            <input v-model.number="custForm.credit_limit" type="number" min="0" step="5000000" class="pache-input tabular-nums" />
                        </div>
                    </div>
                    <button type="submit" :disabled="custForm.processing" class="pache-btn-primary w-full">
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
                                <th>Plafon Kredit (BR-02)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="c in customers" :key="c.id">
                                <td>
                                    <strong class="block">{{ c.name }}</strong>
                                    <span class="text-[12px] text-[var(--muted)] tabular-nums">{{ c.code }} · {{ c.phone }}</span>
                                </td>
                                <td>
                                    <span
                                        class="rounded-[6px] border px-2 py-0.5 text-[11px] font-bold"
                                        :class="c.is_top_enabled ? 'border-[#e7cfa8] bg-[#f8eddf] text-[#92400e]' : 'border-[#bfdbfe] bg-[#eff6ff] text-[#1d4ed8]'"
                                    >
                                        {{ c.is_top_enabled ? `TOP Net ${c.top_days}h` : 'Online / Cash' }}
                                    </span>
                                </td>
                                <td class="tabular-nums">
                                    <template v-if="c.is_top_enabled">
                                        <strong>{{ formatIdr(c.credit_exposure) }}</strong>
                                        <span class="text-[var(--muted)]"> / {{ formatIdr(c.credit_limit) }}</span>
                                    </template>
                                    <span v-else class="text-[var(--muted)]">Non-Kredit (Bayar di Muka)</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- TAB 2: TARIFFS -->
        <div v-if="activeTab === 'tariffs'" class="grid grid-cols-1 gap-5 lg:grid-cols-[0.8fr_1.2fr]">
            <section class="pache-panel p-5">
                <h2 class="text-[16px] font-[650] text-[var(--ink)]">Tambah / Perbarui Tarif Rute</h2>
                <p class="mt-1 text-[12px] text-[var(--muted)]">Tarif bersih tanpa komponen biaya admin COD.</p>
                <form @submit.prevent="saveTariff" class="mt-4 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-[12px] font-semibold text-[var(--muted)]">Asal</label>
                            <select v-model="tariffForm.origin_branch_id" class="pache-input">
                                <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.code }} — {{ b.city }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-[12px] font-semibold text-[var(--muted)]">Tujuan</label>
                            <select v-model="tariffForm.destination_branch_id" class="pache-input">
                                <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.code }} — {{ b.city }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-[12px] font-semibold text-[var(--muted)]">Layanan</label>
                            <select v-model="tariffForm.service_type" class="pache-input">
                                <option value="REG">REG (Reguler)</option>
                                <option value="EXP">EXP (Express)</option>
                                <option value="CRG">CRG (Cargo)</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-[12px] font-semibold text-[var(--muted)]">Pembagi Volumetrik</label>
                            <select v-model.number="tariffForm.volumetric_divisor" class="pache-input tabular-nums">
                                <option :value="6000">6000 (Darat)</option>
                                <option :value="5000">5000 (Udara)</option>
                                <option :value="4000">4000 (Cargo Truk)</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="mb-1 block text-[12px] font-semibold text-[var(--muted)]">Tarif / kg</label>
                            <input v-model.number="tariffForm.rate_per_kg" type="number" min="1000" step="500" class="pache-input tabular-nums" />
                        </div>
                        <div>
                            <label class="mb-1 block text-[12px] font-semibold text-[var(--muted)]">Min kg</label>
                            <input v-model.number="tariffForm.min_weight_kg" type="number" min="1" class="pache-input tabular-nums" />
                        </div>
                        <div>
                            <label class="mb-1 block text-[12px] font-semibold text-[var(--muted)]">SLA (Hari)</label>
                            <input v-model.number="tariffForm.sla_days" type="number" min="1" class="pache-input tabular-nums" />
                        </div>
                    </div>
                    <button type="submit" :disabled="tariffForm.processing" class="pache-btn-primary w-full">
                        Simpan Tarif Rute
                    </button>
                </form>
            </section>

            <section class="pache-panel overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="pache-table">
                        <thead>
                            <tr>
                                <th>Rute</th>
                                <th>Layanan</th>
                                <th>Pembagi</th>
                                <th>Min Berat</th>
                                <th>Tarif / kg</th>
                                <th>SLA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="t in tariffs" :key="t.id">
                                <td class="font-semibold">{{ t.origin }} → {{ t.destination }}</td>
                                <td><span class="rounded bg-[var(--surface-soft)] px-2 py-0.5 text-[11px] font-bold">{{ t.service_type }}</span></td>
                                <td class="tabular-nums">÷ {{ t.volumetric_divisor }}</td>
                                <td class="tabular-nums">{{ t.min_weight_kg }} kg</td>
                                <td class="font-bold tabular-nums">{{ formatIdr(t.rate_per_kg) }}</td>
                                <td class="tabular-nums">{{ t.sla_days }} hari</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- TAB 3: BRANCHES -->
        <section v-if="activeTab === 'branches'" class="pache-panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="pache-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Cabang / Hub</th>
                            <th>Tipe</th>
                            <th>Kota</th>
                            <th>Alamat &amp; Telepon</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="b in branches" :key="b.id">
                            <td class="font-bold text-[var(--brand-blue)]">{{ b.code }}</td>
                            <td class="font-semibold">{{ b.name }}</td>
                            <td class="uppercase text-[11.5px] font-bold text-[var(--muted)]">{{ b.type.replace('_', ' ') }}</td>
                            <td>{{ b.city }}</td>
                            <td class="text-[13px] text-[var(--muted)]">
                                {{ b.address }}
                                <span v-if="b.phone" class="block tabular-nums">{{ b.phone }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>
