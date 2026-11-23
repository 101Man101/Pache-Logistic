<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    ArrowDownLeft,
    ArrowUpRight,
    Barcode,
    Lock,
    Warehouse as WarehouseIcon,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import { useBarcodeScanner } from '@/composables/useBarcodeScanner';

interface WarehouseItem {
    id: number;
    code: string;
    name: string;
    zone_type: string;
    branch?: { code: string; city: string };
}

interface BinItem {
    id: number;
    warehouse_id: number;
    code: string;
    zone: string;
    aisle: string;
    rack: string;
    bin: string;
    max_capacity: number;
}

interface ProductItem {
    id: number;
    sku: string;
    name: string;
    allocation_rule: string;
    unit: string;
}

interface StockPosition {
    warehouse_code: string;
    bin_code: string;
    zone: string;
    sku: string;
    product_name: string;
    allocation_rule: string;
    unit: string;
    balance: number;
    batch_number: string | null;
    expires_at: string | null;
}

interface LedgerRow {
    id: number;
    movement_type: string;
    reference_code: string;
    bin_code: string;
    sku: string;
    product_name: string;
    quantity_change: number;
    balance_after: number;
    batch_number: string | null;
    reason: string | null;
    created_at: string;
}

const props = defineProps<{
    warehouses: WarehouseItem[];
    bins: BinItem[];
    products: ProductItem[];
    stockPositions: StockPosition[];
    recentLedger: LedgerRow[];
}>();

const lastScanned = ref<string | null>(null);
const scanStatus = ref<'idle' | 'ok' | 'err'>('idle');

const form = useForm({
    warehouse_id: props.warehouses[0]?.id ?? 0,
    storage_bin_id: props.bins[0]?.id ?? 0,
    product_id: props.products[0]?.id ?? 0,
    movement_type: 'inbound' as 'inbound' | 'outbound' | 'adjustment',
    quantity: 10,
    reference_code: 'ASN-2611-NEW',
    batch_number: 'BATCH-2611',
    expires_at: '',
    reason: 'Scan receiving gudang',
});

const filteredBins = computed(() =>
    props.bins.filter((b) => b.warehouse_id === form.warehouse_id),
);

const { playBeep } = useBarcodeScanner((code) => {
    lastScanned.value = code;
    const matchedBin = props.bins.find(
        (b) => b.code.toUpperCase() === code.toUpperCase(),
    );
    if (matchedBin) {
        form.warehouse_id = matchedBin.warehouse_id;
        form.storage_bin_id = matchedBin.id;
        scanStatus.value = 'ok';
        playBeep(true);
        return;
    }
    const matchedProd = props.products.find(
        (p) => p.sku.toUpperCase() === code.toUpperCase(),
    );
    if (matchedProd) {
        form.product_id = matchedProd.id;
        scanStatus.value = 'ok';
        playBeep(true);
        return;
    }
    form.reference_code = code;
    scanStatus.value = 'ok';
    playBeep(true);
});

function submitMutation(): void {
    form.post('/wms/inventory/mutate', {
        preserveScroll: true,
        onSuccess: () => playBeep(true),
        onError: () => playBeep(false),
    });
}
</script>

<template>
    <Head title="Gudang & Bin Ledger (WMS)" />

    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="pache-eyebrow">WAREHOUSE MANAGEMENT SYSTEM (WMS)</p>
                <h1 class="pache-page-title">
                    Topologi Rak Bin &amp; Immutable Stock Ledger
                </h1>
            </div>
            <div
                class="pache-status-pill px-3 py-1.5 text-[12px]"
                :class="scanStatus === 'ok' ? 'is-success' : 'is-info'"
            >
                <Barcode class="h-4 w-4" />
                <span>
                    {{
                        lastScanned
                            ? `Terpindai: ${lastScanned}`
                            : 'Scanner HID Aktif (Scan Bin / SKU)'
                    }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-[0.9fr_1.1fr]">
            <!-- Mutation Form (Locked Pessimistic Transaction BR-04) -->
            <section class="pache-panel p-5">
                <div class="flex items-center justify-between">
                    <h2 class="pache-section-title">
                        Catat Mutasi Stok (Anti-Minus Lock)
                    </h2>
                    <span
                        class="inline-flex items-center gap-1 text-[11.5px] font-semibold text-[var(--muted)]"
                    >
                        <Lock class="h-3.5 w-3.5 text-[var(--brand-blue)]" />
                        Anti-Minus
                    </span>
                </div>
                <p class="mt-1 text-[12.5px] text-[var(--muted)]">
                    Setiap pergerakan barang dicatat append-only. Sistem menolak
                    pengeluaran yang menyebabkan stok Bin &lt; 0.
                </p>

                <form @submit.prevent="submitMutation" class="mt-4 space-y-3.5">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                                >Gudang</label
                            >
                            <select
                                v-model="form.warehouse_id"
                                @change="
                                    form.storage_bin_id =
                                        filteredBins[0]?.id ?? 0
                                "
                                class="pache-input"
                            >
                                <option
                                    v-for="w in warehouses"
                                    :key="w.id"
                                    :value="w.id"
                                >
                                    {{ w.code }} — {{ w.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                                >Lokasi Bin</label
                            >
                            <select
                                v-model="form.storage_bin_id"
                                class="pache-input"
                            >
                                <option
                                    v-for="b in filteredBins"
                                    :key="b.id"
                                    :value="b.id"
                                >
                                    {{ b.code }} (Zona {{ b.zone }})
                                </option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >Produk / SKU (FIFO / FEFO)</label
                        >
                        <select v-model="form.product_id" class="pache-input">
                            <option
                                v-for="p in products"
                                :key="p.id"
                                :value="p.id"
                            >
                                [{{ p.allocation_rule }}] {{ p.sku }} —
                                {{ p.name }}
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div>
                            <label
                                class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                                >Jenis Mutasi</label
                            >
                            <select
                                v-model="form.movement_type"
                                class="pache-input"
                            >
                                <option value="inbound">Inbound (+)</option>
                                <option value="outbound">Outbound (-)</option>
                                <option value="adjustment">
                                    Adjustment (+/-)
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                                >Kuantitas</label
                            >
                            <input
                                v-model.number="form.quantity"
                                type="number"
                                required
                                class="pache-input tabular-nums"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                                >No. Referensi / ASN</label
                            >
                            <input
                                v-model="form.reference_code"
                                type="text"
                                required
                                class="pache-input tabular-nums"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                                >Batch / Lot Number</label
                            >
                            <input
                                v-model="form.batch_number"
                                type="text"
                                class="pache-input"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                                >Kedaluwarsa (FEFO)</label
                            >
                            <input
                                v-model="form.expires_at"
                                type="date"
                                class="pache-input"
                            />
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-[12px] font-semibold text-[var(--muted)]"
                            >Catatan / Alasan Audit</label
                        >
                        <input
                            v-model="form.reason"
                            type="text"
                            class="pache-input"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="pache-btn-primary w-full"
                    >
                        <WarehouseIcon class="h-4 w-4" />
                        <span>{{
                            form.processing
                                ? 'Mengunci & Mencatat...'
                                : 'Simpan ke Inventory Ledger'
                        }}</span>
                    </button>
                </form>
            </section>

            <!-- Live Stock Positions per Bin -->
            <section class="pache-panel overflow-hidden">
                <div class="border-b border-[var(--hairline)] px-5 py-4">
                    <h2 class="text-[16px] font-[650] text-[var(--ink)]">
                        Posisi Stok Aktual per Storage Bin
                    </h2>
                    <p class="text-[12px] text-[var(--muted)]">
                        Dihitung dari baris terbaru Immutable Inventory Ledger.
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="pache-table">
                        <thead>
                            <tr>
                                <th>Lokasi Bin</th>
                                <th>SKU &amp; Produk</th>
                                <th>Aturan</th>
                                <th>Batch / Exp</th>
                                <th class="text-right">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="pos in stockPositions"
                                :key="`${pos.bin_code}-${pos.sku}`"
                            >
                                <td>
                                    <strong class="block tabular-nums">{{
                                        pos.bin_code
                                    }}</strong>
                                    <span
                                        class="text-[11.5px] text-[var(--muted)]"
                                        >{{ pos.warehouse_code }} ·
                                        {{ pos.zone }}</span
                                    >
                                </td>
                                <td>
                                    <strong class="block">{{ pos.sku }}</strong>
                                    <span
                                        class="text-[12px] text-[var(--muted)]"
                                        >{{ pos.product_name }}</span
                                    >
                                </td>
                                <td>
                                    <span
                                        class="rounded-[6px] border px-2 py-0.5 text-[11px] font-bold"
                                        :class="
                                            pos.allocation_rule === 'FEFO'
                                                ? 'border-[#e7cfa8] bg-[#f8eddf] text-[#92400e]'
                                                : 'border-[#bfdbfe] bg-[#eff6ff] text-[#1d4ed8]'
                                        "
                                    >
                                        {{ pos.allocation_rule }}
                                    </span>
                                </td>
                                <td class="text-[12.5px] tabular-nums">
                                    <span class="block font-semibold">{{
                                        pos.batch_number || '-'
                                    }}</span>
                                    <span
                                        class="text-[11.5px] text-[var(--muted)]"
                                        >{{
                                            pos.expires_at
                                                ? `Exp ${pos.expires_at}`
                                                : 'Non-Expiry'
                                        }}</span
                                    >
                                </td>
                                <td class="text-right font-bold tabular-nums">
                                    {{ pos.balance }} {{ pos.unit }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- Immutable Ledger Audit Trail -->
        <section class="pache-panel overflow-hidden">
            <div class="border-b border-[var(--hairline)] px-5 py-4">
                <h2 class="text-[16px] font-[650] text-[var(--ink)]">
                    Riwayat Buku Besar Stok (Append-Only Audit Trail)
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="pache-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Mutasi</th>
                            <th>Referensi</th>
                            <th>Bin &amp; SKU</th>
                            <th class="text-right">Perubahan</th>
                            <th class="text-right">Saldo Akhir</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in recentLedger" :key="row.id">
                            <td
                                class="text-[12.5px] text-[var(--muted)] tabular-nums"
                            >
                                {{ row.created_at }}
                            </td>
                            <td>
                                <span
                                    class="inline-flex items-center gap-1 rounded-[6px] border px-2 py-0.5 text-[11px] font-bold uppercase"
                                    :class="
                                        row.quantity_change > 0
                                            ? 'border-[#bbf7d0] bg-[#ecfdf3] text-[#15803d]'
                                            : 'border-[#fecaca] bg-[#fef2f2] text-[#991b1b]'
                                    "
                                >
                                    <ArrowDownLeft
                                        v-if="row.quantity_change > 0"
                                        class="h-3 w-3"
                                    />
                                    <ArrowUpRight v-else class="h-3 w-3" />
                                    {{ row.movement_type }}
                                </span>
                            </td>
                            <td class="font-semibold tabular-nums">
                                {{ row.reference_code }}
                            </td>
                            <td>
                                <strong class="tabular-nums">{{
                                    row.bin_code
                                }}</strong>
                                <span
                                    class="ml-1.5 text-[12px] text-[var(--muted)]"
                                    >{{ row.sku }}</span
                                >
                            </td>
                            <td
                                class="text-right font-bold tabular-nums"
                                :class="
                                    row.quantity_change > 0
                                        ? 'text-[#15803d]'
                                        : 'text-[#b42318]'
                                "
                            >
                                {{
                                    row.quantity_change > 0
                                        ? `+${row.quantity_change}`
                                        : row.quantity_change
                                }}
                            </td>
                            <td class="text-right font-bold tabular-nums">
                                {{ row.balance_after }}
                            </td>
                            <td class="text-[12.5px] text-[var(--muted)]">
                                {{ row.reason || '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>
