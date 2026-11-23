<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Barcode,
    CheckCircle2,
    Send,
    ShieldCheck,
    Truck,
} from '@lucide/vue';
import { ref } from 'vue';
import { useBarcodeScanner } from '@/composables/useBarcodeScanner';
import { formatIdr, formatWeight } from '@/lib/format';

interface ManifestItemRow {
    id: number;
    awb_number: string;
    weight_kg: number;
    payment_method: string;
    payment_status: string;
    status: string;
}

interface ManifestRow {
    id: number;
    manifest_number: string;
    origin: string;
    destination: string;
    destination_branch_id: number;
    vehicle: string;
    capacity_kg: number;
    driver_name: string;
    vendor_name: string | null;
    vendor_cost: number;
    seal_number: string | null;
    driver_advance_amount: number;
    status: string;
    departed_at: string | null;
    arrived_at: string | null;
    total_weight_kg: number;
    items: ManifestItemRow[];
}

interface BranchOption {
    id: number;
    code: string;
    name: string;
    city: string;
}

interface VehicleOption {
    id: number;
    plate_number: string;
    type: string;
    capacity_kg: number;
    stnk_expires_at: string;
    kir_expires_at: string;
    is_expired: boolean;
    expiring_soon: boolean;
    status: string;
}

interface DriverOption {
    id: number;
    name: string;
    phone: string;
    sim_number: string;
    sim_expires_at: string;
    is_expired: boolean;
}

interface VendorOption {
    id: number;
    code: string;
    name: string;
    type: string;
}

interface ReadyShipment {
    awb_number: string;
    destination: string;
    payment_method: string;
    payment_status: string;
    cleared: boolean;
}

const props = defineProps<{
    manifests: ManifestRow[];
    branches: BranchOption[];
    vehicles: VehicleOption[];
    drivers: DriverOption[];
    vendors: VendorOption[];
    readyShipments: ReadyShipment[];
}>();

const createForm = useForm({
    origin_branch_id: props.branches[0]?.id ?? 0,
    destination_branch_id: props.branches[2]?.id ?? props.branches[1]?.id ?? 0,
    vehicle_id: props.vehicles[0]?.id ?? 0,
    driver_id: props.drivers[0]?.id ?? 0,
    vendor_id: null as number | null,
    seal_number: '',
    driver_advance_amount: 1500000,
    vendor_cost: 0,
});

const scanTargetManifestId = ref<number>(
    props.manifests.find((m) => m.status === 'open')?.id ?? 0,
);
const scanAwbInput = ref('');

const { playBeep } = useBarcodeScanner((code) => {
    if (!scanTargetManifestId.value) return;
    scanAwbInput.value = code;
    submitScan(scanTargetManifestId.value, code);
});

function createManifest(): void {
    createForm.post('/tms/manifests', { preserveScroll: true });
}

function submitScan(manifestId: number, awb?: string): void {
    const code = (awb ?? scanAwbInput.value).trim();
    if (!code) return;
    router.post(
        `/tms/manifests/${manifestId}/scan`,
        { awb_number: code },
        {
            preserveScroll: true,
            onSuccess: () => {
                scanAwbInput.value = '';
                playBeep(true);
            },
            onError: () => playBeep(false),
        },
    );
}

function departManifest(manifestId: number): void {
    router.post(
        `/tms/manifests/${manifestId}/depart`,
        {},
        { preserveScroll: true },
    );
}

function arriveManifest(manifestId: number): void {
    router.post(
        `/tms/manifests/${manifestId}/arrive`,
        {},
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head title="Manifest & Armada (TMS)" />

    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="pache-eyebrow">
                    TRANSPORTATION &amp; FLEET MANAGEMENT (TMS)
                </p>
                <h1 class="pache-page-title">
                    Konsolidasi Manifest, Scan Bagging &amp; Kepatuhan Armada
                </h1>
            </div>
            <div class="pache-status-pill is-info px-3 py-1.5 text-[12px]">
                <ShieldCheck class="h-4 w-4" />
                <span
                    >Dispatch Gate Aktif: Resi Belum Lunas Ditolak saat
                    Scan</span
                >
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-[0.9fr_1.1fr]">
            <!-- Create Manifest Form -->
            <section class="pache-panel p-5">
                <h2 class="pache-section-title">Buat Manifest Linehaul Baru</h2>
                <p class="mt-1 text-[12.5px] text-[var(--muted)]">
                    Sistem memblokir kendaraan atau pengemudi dengan masa
                    berlaku STNK, Uji KIR, atau SIM yang kedaluwarsa.
                </p>

                <form @submit.prevent="createManifest" class="mt-4 space-y-3.5">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                                >Hub Asal</label
                            >
                            <select
                                v-model="createForm.origin_branch_id"
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
                                >Hub Tujuan</label
                            >
                            <select
                                v-model="createForm.destination_branch_id"
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

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                                >Armada (Cek STNK/KIR)</label
                            >
                            <select
                                v-model="createForm.vehicle_id"
                                class="pache-input"
                            >
                                <option
                                    v-for="v in vehicles"
                                    :key="v.id"
                                    :value="v.id"
                                >
                                    {{ v.plate_number }} ({{ v.type }}){{
                                        v.is_expired ? ' — EXPIRED!' : ''
                                    }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                                >Supir (Cek SIM)</label
                            >
                            <select
                                v-model="createForm.driver_id"
                                class="pache-input"
                            >
                                <option
                                    v-for="d in drivers"
                                    :key="d.id"
                                    :value="d.id"
                                >
                                    {{ d.name
                                    }}{{
                                        d.is_expired ? ' — SIM EXPIRED!' : ''
                                    }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                                >Nomor Segel Pintu Truk</label
                            >
                            <input
                                v-model="createForm.seal_number"
                                type="text"
                                class="pache-input"
                                placeholder="Auto-generate jika kosong"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                                >Uang Jalan Supir (IDR)</label
                            >
                            <input
                                v-model.number="
                                    createForm.driver_advance_amount
                                "
                                type="number"
                                min="0"
                                step="50000"
                                class="pache-input tabular-nums"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                                >Vendor 3PL Linehaul (Opsional)</label
                            >
                            <select
                                v-model="createForm.vendor_id"
                                class="pache-input"
                            >
                                <option :value="null">
                                    — Armada Sendiri —
                                </option>
                                <option
                                    v-for="vnd in vendors"
                                    :key="vnd.id"
                                    :value="vnd.id"
                                >
                                    {{ vnd.code }} · {{ vnd.name }}
                                </option>
                            </select>
                        </div>
                        <div v-if="createForm.vendor_id">
                            <label
                                class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                                >Biaya Sewa Vendor (IDR)</label
                            >
                            <input
                                v-model.number="createForm.vendor_cost"
                                type="number"
                                min="0"
                                step="50000"
                                class="pache-input tabular-nums"
                            />
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="createForm.processing"
                        class="pache-btn-primary w-full"
                    >
                        <Truck class="h-4 w-4" />
                        <span>Terbitkan Manifest Linehaul</span>
                    </button>
                </form>
            </section>

            <!-- Scan Bagging Kiosk Panel -->
            <section class="pache-panel p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="pache-section-title">
                            Scan Bagging ke Manifest
                        </h2>
                        <p class="mt-0.5 text-[12.5px] text-[var(--muted)]">
                            Tembakkan barcode gun ke resi atau klik resi siap
                            muat di bawah.
                        </p>
                    </div>
                    <Barcode class="h-5 w-5 text-[var(--brand-blue)]" />
                </div>

                <div
                    class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-[1fr_1fr_auto]"
                >
                    <select v-model="scanTargetManifestId" class="pache-input">
                        <option :value="0">Pilih Manifest Open</option>
                        <option
                            v-for="m in manifests.filter(
                                (x) => x.status === 'open',
                            )"
                            :key="m.id"
                            :value="m.id"
                        >
                            {{ m.manifest_number }} ({{ m.origin }} →
                            {{ m.destination }})
                        </option>
                    </select>
                    <input
                        v-model="scanAwbInput"
                        type="text"
                        placeholder="Scan / ketik nomor AWB..."
                        class="pache-input tabular-nums"
                        @keydown.enter.prevent="
                            submitScan(scanTargetManifestId)
                        "
                    />
                    <button
                        type="button"
                        :disabled="!scanTargetManifestId"
                        @click="submitScan(scanTargetManifestId)"
                        class="pache-btn-primary disabled:opacity-50"
                    >
                        Scan Muat
                    </button>
                </div>

                <div class="mt-4">
                    <p
                        class="mb-2 text-[11.5px] font-bold tracking-[0.06em] text-[var(--muted)] uppercase"
                    >
                        Resi Menunggu Muat (Klik untuk Uji Scan Bagging)
                    </p>
                    <div class="max-h-[210px] space-y-2 overflow-y-auto pr-1">
                        <div
                            v-for="rs in readyShipments"
                            :key="rs.awb_number"
                            class="flex flex-wrap items-center justify-between gap-2 rounded-[10px] border border-[var(--hairline)] bg-[var(--surface-soft)] px-3 py-2 text-[12.5px]"
                        >
                            <div>
                                <strong class="tabular-nums">{{
                                    rs.awb_number
                                }}</strong>
                                <span class="ml-2 text-[var(--muted)]"
                                    >Tujuan {{ rs.destination }}</span
                                >
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="rounded px-2 py-0.5 text-[10.5px] font-bold"
                                    :class="
                                        rs.cleared
                                            ? 'bg-[#ecfdf3] text-[#15803d]'
                                            : 'bg-[#fef2f2] text-[#991b1b]'
                                    "
                                >
                                    {{ rs.payment_method }} ·
                                    {{
                                        rs.cleared
                                            ? 'CLEARED'
                                            : 'PENDING (BLOKIR)'
                                    }}
                                </span>
                                <button
                                    type="button"
                                    :disabled="!scanTargetManifestId"
                                    @click="
                                        submitScan(
                                            scanTargetManifestId,
                                            rs.awb_number,
                                        )
                                    "
                                    class="rounded-md border border-[var(--hairline)] bg-[var(--surface)] px-2.5 py-1 text-[11.5px] font-semibold hover:border-[var(--brand-blue)] hover:text-[var(--brand-blue)] disabled:opacity-40"
                                >
                                    Scan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Active Manifests List -->
        <section class="space-y-3">
            <h2 class="text-[17px] font-[650] text-[var(--ink)]">
                Daftar Manifest Aktif &amp; Riwayat Trip
            </h2>
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <article
                    v-for="m in manifests"
                    :key="m.id"
                    class="pache-panel p-5"
                >
                    <div
                        class="flex flex-wrap items-start justify-between gap-3 border-b border-[var(--hairline)] pb-3"
                    >
                        <div>
                            <p
                                class="text-[16px] font-bold text-[var(--brand-blue)] tabular-nums"
                            >
                                {{ m.manifest_number }}
                            </p>
                            <p class="text-[12.5px] text-[var(--muted)]">
                                {{ m.origin }} → {{ m.destination }} ·
                                {{ m.vehicle }} · Supir {{ m.driver_name }}
                                <span
                                    v-if="m.vendor_name"
                                    class="font-semibold text-[var(--brand-blue)]"
                                >
                                    · Vendor: {{ m.vendor_name }}</span
                                >
                            </p>
                        </div>
                        <span
                            class="rounded-[6px] border px-2.5 py-0.5 text-[11px] font-bold uppercase"
                            :class="
                                m.status === 'arrived'
                                    ? 'border-[#bbf7d0] bg-[#ecfdf3] text-[#15803d]'
                                    : m.status === 'departed'
                                      ? 'border-[#bfdbfe] bg-[#eff6ff] text-[#1d4ed8]'
                                      : 'border-[#e7cfa8] bg-[#f8eddf] text-[#92400e]'
                            "
                        >
                            {{ m.status }}
                        </span>
                    </div>

                    <div
                        class="mt-3 grid grid-cols-3 gap-2 text-[12px] tabular-nums"
                    >
                        <div class="rounded-lg bg-[var(--surface-soft)] p-2.5">
                            <span class="text-[var(--muted)]">Segel</span>
                            <strong class="block">{{ m.seal_number }}</strong>
                        </div>
                        <div class="rounded-lg bg-[var(--surface-soft)] p-2.5">
                            <span class="text-[var(--muted)]">Muatan</span>
                            <strong class="block"
                                >{{ formatWeight(m.total_weight_kg) }} /
                                {{ m.capacity_kg }} kg</strong
                            >
                        </div>
                        <div class="rounded-lg bg-[var(--surface-soft)] p-2.5">
                            <span class="text-[var(--muted)]">Uang Jalan</span>
                            <strong class="block">{{
                                formatIdr(m.driver_advance_amount)
                            }}</strong>
                        </div>
                    </div>

                    <div class="mt-3">
                        <p class="text-[11.5px] font-bold text-[var(--muted)]">
                            Isi Manifest ({{ m.items.length }} Resi Non-COD):
                        </p>
                        <ul
                            v-if="m.items.length"
                            class="mt-1.5 space-y-1 p-0 text-[12.5px]"
                        >
                            <li
                                v-for="item in m.items"
                                :key="item.id"
                                class="flex items-center justify-between rounded bg-[var(--surface-soft)] px-2.5 py-1.5"
                            >
                                <span class="font-semibold tabular-nums">{{
                                    item.awb_number
                                }}</span>
                                <span class="text-[var(--muted)] tabular-nums"
                                    >{{ formatWeight(item.weight_kg) }} ·
                                    {{ item.payment_method }}</span
                                >
                            </li>
                        </ul>
                        <p v-else class="mt-1 text-[12px] text-[var(--muted)]">
                            Belum ada resi yang di-scan masuk.
                        </p>
                    </div>

                    <div class="mt-4 flex flex-wrap justify-end gap-2">
                        <button
                            v-if="m.status === 'open'"
                            type="button"
                            @click="departManifest(m.id)"
                            class="pache-btn-primary"
                        >
                            <Send class="h-3.5 w-3.5" />
                            <span>Berangkatkan Truk (InTransit)</span>
                        </button>
                        <button
                            v-if="m.status === 'departed'"
                            type="button"
                            @click="arriveManifest(m.id)"
                            class="pache-btn-success"
                        >
                            <CheckCircle2 class="h-3.5 w-3.5" />
                            <span>Konfirmasi Tiba &amp; Unloading Hub</span>
                        </button>
                    </div>
                </article>
            </div>
        </section>

        <!-- Fleet Compliance Table -->
        <section class="pache-panel overflow-hidden">
            <div class="border-b border-[var(--hairline)] px-5 py-4">
                <h2 class="pache-section-title">
                    Kepatuhan Dokumen Armada (STNK &amp; Uji KIR)
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="pache-table">
                    <thead>
                        <tr>
                            <th>Plat Nomor</th>
                            <th>Jenis Armada</th>
                            <th>Kapasitas</th>
                            <th>Masa Berlaku STNK</th>
                            <th>Masa Berlaku KIR</th>
                            <th>Kepatuhan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="v in vehicles" :key="v.id">
                            <td class="font-bold tabular-nums">
                                {{ v.plate_number }}
                            </td>
                            <td>{{ v.type }}</td>
                            <td class="tabular-nums">{{ v.capacity_kg }} kg</td>
                            <td class="tabular-nums">
                                {{ v.stnk_expires_at }}
                            </td>
                            <td class="tabular-nums">{{ v.kir_expires_at }}</td>
                            <td>
                                <span
                                    v-if="v.is_expired"
                                    class="pache-status-pill is-danger"
                                >
                                    <AlertTriangle class="h-3 w-3" />
                                    DIBLOKIR (EXPIRED)
                                </span>
                                <span
                                    v-else-if="v.expiring_soon"
                                    class="pache-status-pill is-warning"
                                >
                                    PERINGATAN H-30
                                </span>
                                <span
                                    v-else
                                    class="pache-status-pill is-success"
                                >
                                    LAIK JALAN
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>
