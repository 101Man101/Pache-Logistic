<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Building2,
    CheckCircle2,
    CreditCard,
    Eye,
    FileSpreadsheet,
    FileText,
    Plus,
    Printer,
    ReceiptText,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import DocumentLinesEditor from '@/components/finance/DocumentLinesEditor.vue';
import { addDaysIsoDate, type DraftLineRow, todayIsoDate } from '@/lib/finance';
import { formatIdr, paymentStatusBadgeClass } from '@/lib/format';

interface CashOrOnlineRow {
    id: number;
    awb_number: string;
    customer_name: string;
    branch: string;
    total_amount: number;
    payment_status: string;
    payment_reference: string | null;
    paid_at: string | null;
    created_at: string;
}

interface TopCustomerRow {
    id: number;
    code: string;
    name: string;
    top_days: number;
    credit_limit: number;
    credit_exposure: number;
    unbilled_delivered_count: number;
    unbilled_delivered_amount: number;
}

interface ActiveCustomerOption {
    id: number;
    code: string;
    name: string;
    type: string;
    is_top_enabled: boolean;
    top_days: number;
}

interface ActiveVendorOption {
    id: number;
    code: string;
    name: string;
    type: string;
    type_label: string;
    payment_terms_days: number;
}

interface BranchOption {
    id: number;
    code: string;
    name: string;
    city: string;
}

interface ManifestOption {
    id: number;
    manifest_number: string;
    vendor_id: number | null;
    vendor_cost: number;
    route: string;
}

interface UnbilledVendorShipment {
    id: number;
    awb_number: string;
    vendor_id: number;
    vendor_awb_no: string | null;
    vendor_cost: number;
    route: string;
    service_type: string;
}

interface InvoiceRow {
    id: number;
    invoice_number: string;
    invoice_type: string;
    invoice_type_label: string;
    customer_name: string;
    period: string;
    shipment_count: number;
    subtotal: number;
    ppn_amount: number;
    pph23_amount: number;
    total_amount: number;
    due_date: string;
    days_overdue: number;
    aging_bucket: string;
    status: string;
    paid_at: string | null;
    payment_reference: string | null;
}

interface VendorBillRow {
    id: number;
    bill_number: string;
    vendor_invoice_no: string | null;
    vendor_name: string;
    vendor_code: string;
    branch_code: string;
    line_count: number;
    bill_date: string;
    due_date: string;
    subtotal: number;
    ppn_amount: number;
    pph23_amount: number;
    total_amount: number;
    days_overdue: number;
    aging_bucket: string;
    status: string;
    paid_at: string | null;
    payment_reference: string | null;
}

interface LedgerRow {
    id: number;
    entry_code: string;
    branch_code: string;
    channel: string;
    direction: string;
    amount: number;
    description: string;
    created_at: string;
}

const props = defineProps<{
    summary: {
        cash_drawer_paid: number;
        online_settled: number;
        top_ar_unpaid: number;
        ap_unpaid: number;
        ap_unpaid_count: number;
        pending_gate_count: number;
    };
    cashShipments: CashOrOnlineRow[];
    onlineShipments: CashOrOnlineRow[];
    topCustomers: TopCustomerRow[];
    customers: ActiveCustomerOption[];
    vendors: ActiveVendorOption[];
    branches: BranchOption[];
    manifests: ManifestOption[];
    unbilledVendorShipments: UnbilledVendorShipment[];
    invoices: InvoiceRow[];
    vendorBills: VendorBillRow[];
    ledger: LedgerRow[];
}>();

const allowedTabs = ['top', 'ap', 'cash', 'online', 'ledger'] as const;
type TabKey = (typeof allowedTabs)[number];

const initialTabParam =
    typeof window !== 'undefined'
        ? new URLSearchParams(window.location.search).get('tab')
        : null;

const activeTab = ref<TabKey>(
    allowedTabs.includes(initialTabParam as TabKey)
        ? (initialTabParam as TabKey)
        : 'top',
);

const showManualInvoiceForm = ref(false);
const showVendorBillForm = ref(false);

const manualInvoiceForm = useForm({
    customer_id: props.customers[0]?.id ?? ('' as number | ''),
    period_start: todayIsoDate(),
    period_end: todayIsoDate(),
    due_date: addDaysIsoDate(props.customers[0]?.top_days || 14),
    apply_ppn: true,
    apply_pph23: true,
    notes: '',
    lines: [
        {
            shipment_id: null,
            description: '',
            quantity: 1,
            unit: 'jasa',
            unit_price: 0,
        },
    ] as DraftLineRow[],
});

watch(
    () => manualInvoiceForm.customer_id,
    (newCustomerId) => {
        const cust = props.customers.find(
            (c) => c.id === Number(newCustomerId),
        );
        if (cust) {
            manualInvoiceForm.due_date = addDaysIsoDate(
                cust.is_top_enabled ? cust.top_days : 14,
            );
        }
    },
);

const vendorBillForm = useForm({
    vendor_id: props.vendors[0]?.id ?? ('' as number | ''),
    branch_id: '' as number | '',
    manifest_id: '' as number | '',
    vendor_invoice_no: '',
    bill_date: todayIsoDate(),
    due_date: addDaysIsoDate(props.vendors[0]?.payment_terms_days || 14),
    apply_ppn: false,
    apply_pph23: false,
    notes: '',
    lines: [
        {
            shipment_id: null,
            description: '',
            quantity: 1,
            unit: 'trip',
            unit_price: 0,
        },
    ] as DraftLineRow[],
});

watch(
    () => vendorBillForm.vendor_id,
    (newVendorId) => {
        const vendor = props.vendors.find((v) => v.id === Number(newVendorId));
        if (vendor) {
            vendorBillForm.due_date = addDaysIsoDate(
                vendor.payment_terms_days || 14,
            );
        }
        vendorBillForm.manifest_id = '';
    },
);

const filteredManifests = computed(() =>
    props.manifests.filter(
        (m) =>
            !vendorBillForm.vendor_id ||
            m.vendor_id === Number(vendorBillForm.vendor_id),
    ),
);

const matchingVendorShipments = computed(() =>
    props.unbilledVendorShipments.filter(
        (s) => s.vendor_id === Number(vendorBillForm.vendor_id),
    ),
);

function pullVendorShipmentsToLines(): void {
    if (matchingVendorShipments.value.length === 0) {
        return;
    }

    vendorBillForm.lines = matchingVendorShipments.value.map((s) => ({
        shipment_id: s.id,
        description: `Biaya Vendor AWB ${s.awb_number} (${s.route}${s.vendor_awb_no ? ` · Ref ${s.vendor_awb_no}` : ''})`,
        quantity: 1,
        unit: 'resi',
        unit_price: s.vendor_cost,
    }));
}

function submitManualInvoice(): void {
    manualInvoiceForm.post('/finance/invoices/manual', {
        preserveScroll: true,
        onSuccess: () => {
            showManualInvoiceForm.value = false;
            manualInvoiceForm.reset('notes');
            manualInvoiceForm.lines = [
                {
                    shipment_id: null,
                    description: '',
                    quantity: 1,
                    unit: 'jasa',
                    unit_price: 0,
                },
            ];
        },
    });
}

function submitVendorBill(): void {
    vendorBillForm.post('/finance/vendor-bills', {
        preserveScroll: true,
        onSuccess: () => {
            showVendorBillForm.value = false;
            vendorBillForm.reset('vendor_invoice_no', 'notes', 'manifest_id');
            vendorBillForm.lines = [
                {
                    shipment_id: null,
                    description: '',
                    quantity: 1,
                    unit: 'trip',
                    unit_price: 0,
                },
            ];
        },
    });
}

function confirmShipmentPayment(awb: string): void {
    router.post(
        `/oms/shipments/${awb}/confirm-payment`,
        {},
        { preserveScroll: true },
    );
}

function generateInvoice(customerId: number): void {
    router.post(
        '/finance/invoices/generate',
        { customer_id: customerId },
        { preserveScroll: true },
    );
}

function markInvoicePaid(invoiceId: number): void {
    router.post(
        `/finance/invoices/${invoiceId}/mark-paid`,
        {},
        { preserveScroll: true },
    );
}

function markVendorBillPaid(vendorBillId: number): void {
    router.post(
        `/finance/vendor-bills/${vendorBillId}/mark-paid`,
        {},
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head title="Finance Non-COD, Invoice & AP Vendor" />

    <div class="space-y-6">
        <div>
            <p class="pache-eyebrow">
                FINANCE, INVOICING (AR), VENDOR BILL (AP) &amp; KASIR NON-COD
            </p>
            <h1 class="pache-page-title">
                Rekonsiliasi Pembayaran, Invoice Lengkap &amp; Tagihan Vendor
            </h1>
        </div>

        <!-- Bento Summary Cards -->
        <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 xl:grid-cols-4">
            <section class="pache-metric-card variant-primary">
                <span class="text-[12px] font-semibold opacity-90"
                    >Online Payment Settled</span
                >
                <strong class="mt-2 text-[24px] font-bold tabular-nums">{{
                    formatIdr(summary.online_settled)
                }}</strong>
                <span class="mt-2 text-[11.5px] opacity-85"
                    >QRIS &amp; Virtual Account</span
                >
            </section>
            <section class="pache-metric-card variant-soft">
                <span class="text-[12px] font-semibold"
                    >Cash Drawer Loket (Lunas)</span
                >
                <strong class="mt-2 text-[24px] font-bold tabular-nums">{{
                    formatIdr(summary.cash_drawer_paid)
                }}</strong>
                <span class="metric-sub mt-2 text-[11.5px]"
                    >Tunai di muka · {{ summary.pending_gate_count }} resi
                    tertahan gate</span
                >
            </section>
            <section class="pache-metric-card">
                <span class="text-[12px] font-semibold text-[var(--muted)]"
                    >Piutang Invoice AR (Unpaid)</span
                >
                <strong
                    class="mt-2 text-[24px] font-bold text-[var(--ink)] tabular-nums"
                    >{{ formatIdr(summary.top_ar_unpaid) }}</strong
                >
                <span class="mt-2 text-[11.5px] text-[var(--muted)]"
                    >B2B TOP &amp; Invoice Manual belum lunas</span
                >
            </section>
            <section class="pache-metric-card">
                <span class="text-[12px] font-semibold text-[var(--muted)]"
                    >Hutang Vendor AP (Unpaid)</span
                >
                <strong
                    class="mt-2 text-[24px] font-bold text-[#b42318] tabular-nums"
                    >{{ formatIdr(summary.ap_unpaid) }}</strong
                >
                <span class="mt-2 text-[11.5px] text-[var(--muted)]"
                    >{{ summary.ap_unpaid_count }} Tagihan Vendor menunggu
                    pembayaran</span
                >
            </section>
        </div>

        <!-- Segmented Workspace Tabs -->
        <div class="pache-tab-bar">
            <button
                type="button"
                @click="activeTab = 'top'"
                class="pache-tab-btn"
                :class="{ 'is-active': activeTab === 'top' }"
            >
                <ReceiptText class="h-4 w-4" />
                <span>Invoice AR (TOP &amp; Manual)</span>
            </button>
            <button
                type="button"
                @click="activeTab = 'ap'"
                class="pache-tab-btn"
                :class="{ 'is-active': activeTab === 'ap' }"
            >
                <FileText class="h-4 w-4" />
                <span>Vendor Bill (AP)</span>
            </button>
            <button
                type="button"
                @click="activeTab = 'cash'"
                class="pache-tab-btn"
                :class="{ 'is-active': activeTab === 'cash' }"
            >
                <Building2 class="h-4 w-4" />
                <span>Kasir Loket (Cash)</span>
            </button>
            <button
                type="button"
                @click="activeTab = 'online'"
                class="pache-tab-btn"
                :class="{ 'is-active': activeTab === 'online' }"
            >
                <CreditCard class="h-4 w-4" />
                <span>Online Payment (QRIS/VA)</span>
            </button>
            <button
                type="button"
                @click="activeTab = 'ledger'"
                class="pache-tab-btn"
                :class="{ 'is-active': activeTab === 'ledger' }"
            >
                <FileSpreadsheet class="h-4 w-4" />
                <span>Buku Besar Keuangan</span>
            </button>
        </div>

        <!-- TAB 1: INVOICE AR (AUTO TOP & MANUAL) -->
        <div v-if="activeTab === 'top'" class="space-y-5">
            <!-- B2B TOP Auto-Invoicing -->
            <section class="pache-panel overflow-hidden">
                <div
                    class="flex flex-col gap-3 border-b border-[var(--hairline)] px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2 class="pache-section-title">
                            Auto-Invoicing Klien Kontrak TOP
                        </h2>
                        <p class="text-[12.5px] text-[var(--muted)]">
                            Tarik seluruh resi TOP berstatus Delivered yang
                            belum ditagih menjadi Invoice (otomatis PPN 11% −
                            PPh 23 2%).
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="showManualInvoiceForm = !showManualInvoiceForm"
                        class="pache-btn-outline shrink-0"
                    >
                        <Plus class="h-4 w-4" />
                        <span>{{
                            showManualInvoiceForm
                                ? 'Tutup Form Manual'
                                : 'Buat Invoice Manual'
                        }}</span>
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="pache-table">
                        <thead>
                            <tr>
                                <th>Pelanggan Korporat</th>
                                <th>Termin</th>
                                <th>Eksposur / Plafon Kredit</th>
                                <th>Siap Ditagih (Delivered)</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="c in topCustomers" :key="c.id">
                                <td>
                                    <strong class="block">{{ c.name }}</strong>
                                    <span
                                        class="text-[12px] text-[var(--muted)]"
                                        >{{ c.code }}</span
                                    >
                                </td>
                                <td class="font-semibold tabular-nums">
                                    Net {{ c.top_days }} hari
                                </td>
                                <td class="tabular-nums">
                                    <span class="font-bold">{{
                                        formatIdr(c.credit_exposure)
                                    }}</span>
                                    <span class="text-[var(--muted)]">
                                        / {{ formatIdr(c.credit_limit) }}</span
                                    >
                                </td>
                                <td class="tabular-nums">
                                    <strong class="text-[var(--brand-blue)]"
                                        >{{
                                            c.unbilled_delivered_count
                                        }}
                                        resi</strong
                                    >
                                    <span class="ml-1.5 text-[var(--muted)]"
                                        >({{
                                            formatIdr(
                                                c.unbilled_delivered_amount,
                                            )
                                        }})</span
                                    >
                                </td>
                                <td class="text-right">
                                    <button
                                        type="button"
                                        :disabled="
                                            c.unbilled_delivered_count === 0
                                        "
                                        @click="generateInvoice(c.id)"
                                        class="pache-btn-primary min-h-[34px] px-3 text-[12px] disabled:opacity-40"
                                    >
                                        Terbitkan Invoice TOP
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Create Manual AR Invoice -->
            <section v-if="showManualInvoiceForm" class="pache-panel p-5">
                <div class="mb-4 border-b border-[var(--hairline)] pb-3">
                    <h2 class="pache-section-title">
                        Buat Invoice Manual (Non-AWB / Jasa Khusus / Sewa
                        Gudang)
                    </h2>
                    <p class="text-[12.5px] text-[var(--muted)]">
                        Terbitkan tagihan AR langsung ke pelanggan untuk sewa
                        gudang WMS, charter armada, handling khusus, atau biaya
                        tambahan lainnya.
                    </p>
                </div>

                <form @submit.prevent="submitManualInvoice" class="space-y-4">
                    <div class="grid grid-cols-1 gap-3.5 md:grid-cols-4">
                        <div class="md:col-span-2">
                            <label
                                class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]"
                                >Pelanggan</label
                            >
                            <select
                                v-model="manualInvoiceForm.customer_id"
                                required
                                class="pache-input"
                            >
                                <option
                                    v-for="cust in customers"
                                    :key="cust.id"
                                    :value="cust.id"
                                >
                                    {{ cust.name }} ({{ cust.code }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]"
                                >Periode Mulai</label
                            >
                            <input
                                v-model="manualInvoiceForm.period_start"
                                type="date"
                                required
                                class="pache-input"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]"
                                >Periode Selesai</label
                            >
                            <input
                                v-model="manualInvoiceForm.period_end"
                                type="date"
                                required
                                class="pache-input"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3.5 md:grid-cols-3">
                        <div>
                            <label
                                class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]"
                                >Jatuh Tempo</label
                            >
                            <input
                                v-model="manualInvoiceForm.due_date"
                                type="date"
                                required
                                class="pache-input"
                            />
                        </div>
                        <div class="md:col-span-2">
                            <label
                                class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]"
                                >Catatan Invoice (opsional)</label
                            >
                            <input
                                v-model="manualInvoiceForm.notes"
                                type="text"
                                placeholder="Misal: Sewa Pallet Gudang JKT bulan November"
                                class="pache-input"
                            />
                        </div>
                    </div>

                    <DocumentLinesEditor
                        v-model:lines="manualInvoiceForm.lines"
                        v-model:apply-ppn="manualInvoiceForm.apply_ppn"
                        v-model:apply-pph23="manualInvoiceForm.apply_pph23"
                        default-unit="jasa"
                        description-placeholder="Sewa Gudang / Charter Truk / Jasa Handling..."
                    />

                    <div class="flex justify-end gap-2.5 pt-1">
                        <button
                            type="button"
                            @click="showManualInvoiceForm = false"
                            class="pache-btn-outline"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="manualInvoiceForm.processing"
                            class="pache-btn-primary"
                        >
                            Terbitkan Invoice Manual
                        </button>
                    </div>
                </form>
            </section>

            <!-- AR Invoices List & Aging -->
            <section class="pache-panel overflow-hidden">
                <div class="border-b border-[var(--hairline)] px-5 py-4">
                    <h2 class="pache-section-title">
                        Daftar Invoice &amp; Umur Piutang (AR Aging)
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="pache-table">
                        <thead>
                            <tr>
                                <th>No. Invoice</th>
                                <th>Pelanggan &amp; Periode</th>
                                <th>Subtotal</th>
                                <th>PPN 11% / PPh23 2%</th>
                                <th>Total Tagihan</th>
                                <th>Jatuh Tempo &amp; Aging</th>
                                <th class="text-right">Status &amp; Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="inv in invoices" :key="inv.id">
                                <td class="tabular-nums">
                                    <Link
                                        :href="`/finance/invoices/${inv.id}`"
                                        class="block font-bold text-[var(--brand-blue)] no-underline hover:underline"
                                    >
                                        {{ inv.invoice_number }}
                                    </Link>
                                    <span
                                        class="mt-0.5 inline-block rounded-[5px] border border-[var(--hairline)] bg-[var(--surface-soft)] px-1.5 py-0.5 text-[10px] font-bold text-[var(--muted)] uppercase"
                                    >
                                        {{ inv.invoice_type_label }}
                                    </span>
                                </td>
                                <td>
                                    <strong class="block">{{
                                        inv.customer_name
                                    }}</strong>
                                    <span
                                        class="text-[12px] text-[var(--muted)]"
                                        >{{ inv.period }} ·
                                        {{ inv.shipment_count }} baris</span
                                    >
                                </td>
                                <td class="tabular-nums">
                                    {{ formatIdr(inv.subtotal) }}
                                </td>
                                <td class="text-[12px] tabular-nums">
                                    +{{ formatIdr(inv.ppn_amount) }} / −{{
                                        formatIdr(inv.pph23_amount)
                                    }}
                                </td>
                                <td class="font-bold tabular-nums">
                                    {{ formatIdr(inv.total_amount) }}
                                </td>
                                <td class="tabular-nums">
                                    <span class="block font-semibold">{{
                                        inv.due_date
                                    }}</span>
                                    <span
                                        class="text-[11.5px] text-[var(--muted)]"
                                        >{{ inv.aging_bucket }}</span
                                    >
                                </td>
                                <td class="text-right">
                                    <div
                                        class="inline-flex items-center justify-end gap-1.5"
                                    >
                                        <Link
                                            :href="`/finance/invoices/${inv.id}`"
                                            class="pache-btn-outline min-h-[32px] px-2.5 text-[12px]"
                                            title="Lihat Detail Invoice"
                                        >
                                            <Eye class="h-3.5 w-3.5" />
                                        </Link>
                                        <Link
                                            :href="`/finance/invoices/${inv.id}/print`"
                                            class="pache-btn-outline min-h-[32px] px-2.5 text-[12px]"
                                            title="Cetak Invoice A4"
                                        >
                                            <Printer class="h-3.5 w-3.5" />
                                        </Link>
                                        <button
                                            v-if="inv.status !== 'paid'"
                                            type="button"
                                            @click="markInvoicePaid(inv.id)"
                                            class="pache-btn-success min-h-[32px] px-3 text-[12px]"
                                        >
                                            <CheckCircle2 class="h-3.5 w-3.5" />
                                            <span>Tandai Lunas</span>
                                        </button>
                                        <span
                                            v-else
                                            class="pache-status-pill is-success px-2.5 py-1"
                                        >
                                            LUNAS ({{ inv.paid_at }})
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- TAB 2: VENDOR BILL (AP / PURCHASE INVOICE) -->
        <div v-if="activeTab === 'ap'" class="space-y-5">
            <section class="pache-panel overflow-hidden">
                <div
                    class="flex flex-col gap-3 border-b border-[var(--hairline)] px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2 class="pache-section-title">
                            Tagihan Vendor &amp; Mitra (Accounts Payable)
                        </h2>
                        <p class="text-[12.5px] text-[var(--muted)]">
                            Catat tagihan masuk dari vendor linehaul, SMU udara,
                            kargo laut, atau supplier operasional beserta
                            pelunasannya ke Buku Besar.
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="showVendorBillForm = !showVendorBillForm"
                        class="pache-btn-primary shrink-0"
                    >
                        <Plus class="h-4 w-4" />
                        <span>{{
                            showVendorBillForm
                                ? 'Tutup Form Vendor Bill'
                                : 'Catat Vendor Bill (AP)'
                        }}</span>
                    </button>
                </div>

                <!-- Create AP Vendor Bill Form -->
                <form
                    v-if="showVendorBillForm"
                    @submit.prevent="submitVendorBill"
                    class="space-y-4 border-b border-[var(--hairline)] bg-[var(--surface-soft)]/50 p-5"
                >
                    <div class="grid grid-cols-1 gap-3.5 md:grid-cols-3">
                        <div>
                            <label
                                class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]"
                                >Vendor / Mitra</label
                            >
                            <select
                                v-model="vendorBillForm.vendor_id"
                                required
                                class="pache-input"
                            >
                                <option
                                    v-for="v in vendors"
                                    :key="v.id"
                                    :value="v.id"
                                >
                                    {{ v.name }} ({{ v.code }} ·
                                    {{ v.type_label }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]"
                                >No. Invoice Vendor (opsional)</label
                            >
                            <input
                                v-model="vendorBillForm.vendor_invoice_no"
                                type="text"
                                placeholder="Contoh: INV-MTN-2611-09"
                                class="pache-input"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]"
                                >Cabang Beban (opsional)</label
                            >
                            <select
                                v-model="vendorBillForm.branch_id"
                                class="pache-input"
                            >
                                <option value="">HO (Kantor Pusat)</option>
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

                    <div class="grid grid-cols-1 gap-3.5 md:grid-cols-4">
                        <div>
                            <label
                                class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]"
                                >Referensi Manifest (opsional)</label
                            >
                            <select
                                v-model="vendorBillForm.manifest_id"
                                class="pache-input"
                            >
                                <option value="">
                                    — Tidak Terkait Manifest —
                                </option>
                                <option
                                    v-for="m in filteredManifests"
                                    :key="m.id"
                                    :value="m.id"
                                >
                                    {{ m.manifest_number }} ({{ m.route }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]"
                                >Tanggal Tagihan</label
                            >
                            <input
                                v-model="vendorBillForm.bill_date"
                                type="date"
                                required
                                class="pache-input"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]"
                                >Jatuh Tempo</label
                            >
                            <input
                                v-model="vendorBillForm.due_date"
                                type="date"
                                required
                                class="pache-input"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1.5 block text-[12px] font-semibold text-[var(--muted)]"
                                >Catatan (opsional)</label
                            >
                            <input
                                v-model="vendorBillForm.notes"
                                type="text"
                                placeholder="Catatan biaya linehaul / SMU..."
                                class="pache-input"
                            />
                        </div>
                    </div>

                    <div
                        v-if="matchingVendorShipments.length > 0"
                        class="flex flex-wrap items-center justify-between gap-2 rounded-[10px] border border-[#bfdbfe] bg-[#eff6ff] px-3.5 py-2.5 text-[12.5px] dark:border-[#1d4ed8]/40 dark:bg-[#1e3a8a]/25"
                    >
                        <span
                            class="font-semibold text-[#1d4ed8] dark:text-[#93c5fd]"
                        >
                            Ditemukan {{ matchingVendorShipments.length }} resi
                            OMS dengan biaya vendor belum ditagih ke AP.
                        </span>
                        <button
                            type="button"
                            @click="pullVendorShipmentsToLines"
                            class="pache-btn-primary min-h-[30px] px-3 text-[12px]"
                        >
                            Tarik Biaya Vendor AWB
                        </button>
                    </div>

                    <DocumentLinesEditor
                        v-model:lines="vendorBillForm.lines"
                        v-model:apply-ppn="vendorBillForm.apply_ppn"
                        v-model:apply-pph23="vendorBillForm.apply_pph23"
                        default-unit="trip"
                        description-placeholder="Sewa Linehaul / SMU Udara / Last-Mile..."
                    />

                    <div class="flex justify-end gap-2.5 pt-1">
                        <button
                            type="button"
                            @click="showVendorBillForm = false"
                            class="pache-btn-outline"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="vendorBillForm.processing"
                            class="pache-btn-primary"
                        >
                            Simpan Vendor Bill (AP)
                        </button>
                    </div>
                </form>

                <!-- Vendor Bills Table -->
                <div class="overflow-x-auto">
                    <table class="pache-table">
                        <thead>
                            <tr>
                                <th>No. AP Voucher</th>
                                <th>Vendor &amp; Inv. Vendor</th>
                                <th>Cabang</th>
                                <th>Subtotal &amp; Pajak</th>
                                <th>Total Hutang</th>
                                <th>Jatuh Tempo &amp; Aging</th>
                                <th class="text-right">Status &amp; Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="vendorBills.length === 0">
                                <td
                                    colspan="7"
                                    class="py-8 text-center text-[var(--muted)]"
                                >
                                    Belum ada tagihan vendor (AP) tercatat.
                                </td>
                            </tr>
                            <tr v-for="bill in vendorBills" :key="bill.id">
                                <td class="tabular-nums">
                                    <Link
                                        :href="`/finance/vendor-bills/${bill.id}`"
                                        class="block font-bold text-[var(--brand-blue)] no-underline hover:underline"
                                    >
                                        {{ bill.bill_number }}
                                    </Link>
                                    <span
                                        class="text-[11.5px] text-[var(--muted)]"
                                        >{{ bill.bill_date }} ·
                                        {{ bill.line_count }} baris</span
                                    >
                                </td>
                                <td>
                                    <strong class="block">{{
                                        bill.vendor_name
                                    }}</strong>
                                    <span
                                        class="text-[12px] text-[var(--muted)]"
                                        >{{ bill.vendor_code
                                        }}<span v-if="bill.vendor_invoice_no">
                                            · Ref
                                            {{ bill.vendor_invoice_no }}</span
                                        ></span
                                    >
                                </td>
                                <td class="font-semibold">
                                    {{ bill.branch_code }}
                                </td>
                                <td class="text-[12px] tabular-nums">
                                    <span class="block">{{
                                        formatIdr(bill.subtotal)
                                    }}</span>
                                    <span class="text-[var(--muted)]">
                                        +{{ formatIdr(bill.ppn_amount) }} / −{{
                                            formatIdr(bill.pph23_amount)
                                        }}
                                    </span>
                                </td>
                                <td class="font-bold tabular-nums">
                                    {{ formatIdr(bill.total_amount) }}
                                </td>
                                <td class="tabular-nums">
                                    <span class="block font-semibold">{{
                                        bill.due_date
                                    }}</span>
                                    <span
                                        class="text-[11.5px] text-[var(--muted)]"
                                        >{{ bill.aging_bucket }}</span
                                    >
                                </td>
                                <td class="text-right">
                                    <div
                                        class="inline-flex items-center justify-end gap-1.5"
                                    >
                                        <Link
                                            :href="`/finance/vendor-bills/${bill.id}`"
                                            class="pache-btn-outline min-h-[32px] px-2.5 text-[12px]"
                                            title="Lihat Detail Vendor Bill"
                                        >
                                            <Eye class="h-3.5 w-3.5" />
                                        </Link>
                                        <Link
                                            :href="`/finance/vendor-bills/${bill.id}/print`"
                                            class="pache-btn-outline min-h-[32px] px-2.5 text-[12px]"
                                            title="Cetak Voucher AP"
                                        >
                                            <Printer class="h-3.5 w-3.5" />
                                        </Link>
                                        <button
                                            v-if="bill.status !== 'paid'"
                                            type="button"
                                            @click="markVendorBillPaid(bill.id)"
                                            class="pache-btn-success min-h-[32px] px-3 text-[12px]"
                                        >
                                            <CheckCircle2 class="h-3.5 w-3.5" />
                                            <span>Bayar Tagihan</span>
                                        </button>
                                        <span
                                            v-else
                                            class="pache-status-pill is-success px-2.5 py-1"
                                        >
                                            DIBAYAR ({{ bill.paid_at }})
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- TAB 3: KASIR LOKET (CASH UPFRONT) -->
        <section
            v-if="activeTab === 'cash'"
            class="pache-panel overflow-hidden"
        >
            <div class="border-b border-[var(--hairline)] px-5 py-4">
                <h2 class="pache-section-title">
                    Kasir Penerimaan Tunai di Loket Asal
                </h2>
                <p class="text-[12.5px] text-[var(--muted)]">
                    Pembayaran tunai hanya diterima di loket asal sebelum paket
                    diberangkatkan (bukan COD kurir).
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="pache-table">
                    <thead>
                        <tr>
                            <th>Nomor AWB</th>
                            <th>Pelanggan</th>
                            <th>Cabang</th>
                            <th>Nominal</th>
                            <th>Status</th>
                            <th class="text-right">Aksi Kasir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="s in cashShipments" :key="s.id">
                            <td class="font-bold tabular-nums">
                                <Link
                                    :href="`/oms/shipments/${s.awb_number}`"
                                    class="text-[var(--brand-blue)] no-underline hover:underline"
                                    >{{ s.awb_number }}</Link
                                >
                            </td>
                            <td>{{ s.customer_name }}</td>
                            <td>{{ s.branch }}</td>
                            <td class="font-bold tabular-nums">
                                {{ formatIdr(s.total_amount) }}
                            </td>
                            <td>
                                <span
                                    class="rounded-[6px] border px-2 py-0.5 text-[11px] font-bold"
                                    :class="
                                        paymentStatusBadgeClass(
                                            s.payment_status,
                                        )
                                    "
                                >
                                    {{ s.payment_status.toUpperCase() }}
                                </span>
                            </td>
                            <td class="text-right">
                                <button
                                    v-if="s.payment_status === 'pending'"
                                    type="button"
                                    @click="
                                        confirmShipmentPayment(s.awb_number)
                                    "
                                    class="pache-btn-success min-h-[32px] px-3 text-[12px]"
                                >
                                    Terima Cash &amp; Cetak Kuitansi
                                </button>
                                <span
                                    v-else
                                    class="text-[12px] text-[var(--muted)] tabular-nums"
                                >
                                    {{ s.payment_reference }} · {{ s.paid_at }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- TAB 4: ONLINE PAYMENT (QRIS / VA) -->
        <section
            v-if="activeTab === 'online'"
            class="pache-panel overflow-hidden"
        >
            <div class="border-b border-[var(--hairline)] px-5 py-4">
                <h2 class="pache-section-title">
                    Rekonsiliasi Online Payment Gateway — QRIS &amp; VA
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="pache-table">
                    <thead>
                        <tr>
                            <th>Nomor AWB</th>
                            <th>Pelanggan</th>
                            <th>Kode VA / QRIS Ref</th>
                            <th>Nominal</th>
                            <th>Status</th>
                            <th class="text-right">Simulasi Webhook PG</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="s in onlineShipments" :key="s.id">
                            <td class="font-bold tabular-nums">
                                <Link
                                    :href="`/oms/shipments/${s.awb_number}`"
                                    class="text-[var(--brand-blue)] no-underline hover:underline"
                                    >{{ s.awb_number }}</Link
                                >
                            </td>
                            <td>{{ s.customer_name }}</td>
                            <td class="font-mono text-[12.5px]">
                                {{ s.payment_reference }}
                            </td>
                            <td class="font-bold tabular-nums">
                                {{ formatIdr(s.total_amount) }}
                            </td>
                            <td>
                                <span
                                    class="rounded-[6px] border px-2 py-0.5 text-[11px] font-bold"
                                    :class="
                                        paymentStatusBadgeClass(
                                            s.payment_status,
                                        )
                                    "
                                >
                                    {{ s.payment_status.toUpperCase() }}
                                </span>
                            </td>
                            <td class="text-right">
                                <button
                                    v-if="s.payment_status === 'pending'"
                                    type="button"
                                    @click="
                                        confirmShipmentPayment(s.awb_number)
                                    "
                                    class="pache-btn-primary min-h-[32px] px-3 text-[12px]"
                                >
                                    Simulasi Webhook PAID
                                </button>
                                <span
                                    v-else
                                    class="text-[12px] text-[#15803d] tabular-nums"
                                >
                                    Settled {{ s.paid_at }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- TAB 5: IMMUTABLE FINANCIAL LEDGER -->
        <section
            v-if="activeTab === 'ledger'"
            class="pache-panel overflow-hidden"
        >
            <div class="border-b border-[var(--hairline)] px-5 py-4">
                <h2 class="pache-section-title">
                    Buku Besar Keuangan (Immutable Append-Only Ledger)
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="pache-table">
                    <thead>
                        <tr>
                            <th>Kode Jurnal</th>
                            <th>Waktu</th>
                            <th>Cabang</th>
                            <th>Kanal</th>
                            <th>Deskripsi</th>
                            <th class="text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="l in ledger" :key="l.id">
                            <td class="font-mono text-[12px] font-bold">
                                {{ l.entry_code }}
                            </td>
                            <td
                                class="text-[12.5px] text-[var(--muted)] tabular-nums"
                            >
                                {{ l.created_at }}
                            </td>
                            <td>{{ l.branch_code }}</td>
                            <td
                                class="text-[11.5px] font-bold uppercase"
                                :class="
                                    l.direction === 'debit'
                                        ? 'text-[#b42318] dark:text-[#fca5a5]'
                                        : 'text-[var(--brand-blue)]'
                                "
                            >
                                {{ l.channel }} ({{ l.direction }})
                            </td>
                            <td>{{ l.description }}</td>
                            <td
                                class="text-right font-bold tabular-nums"
                                :class="
                                    l.direction === 'debit'
                                        ? 'text-[#b42318] dark:text-[#fca5a5]'
                                        : 'text-[#15803d]'
                                "
                            >
                                {{ l.direction === 'debit' ? '−' : '+'
                                }}{{ formatIdr(l.amount) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>
