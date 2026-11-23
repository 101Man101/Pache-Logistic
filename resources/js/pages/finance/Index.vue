<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Building2,
    CheckCircle2,
    CreditCard,
    FileSpreadsheet,
    ReceiptText,
} from '@lucide/vue';
import { ref } from 'vue';
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

interface InvoiceRow {
    id: number;
    invoice_number: string;
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

defineProps<{
    summary: {
        cash_drawer_paid: number;
        online_settled: number;
        top_ar_unpaid: number;
        pending_gate_count: number;
    };
    cashShipments: CashOrOnlineRow[];
    onlineShipments: CashOrOnlineRow[];
    topCustomers: TopCustomerRow[];
    invoices: InvoiceRow[];
    ledger: LedgerRow[];
}>();

const activeTab = ref<'top' | 'cash' | 'online' | 'ledger'>('top');

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
</script>

<template>
    <Head title="Finance Non-COD & B2B TOP" />

    <div class="space-y-6">
        <div>
            <p class="pache-eyebrow">
                FINANCE, KASIR LOKET &amp; B2B TOP BILLING
            </p>
            <h1 class="pache-page-title">
                Rekonsiliasi Pembayaran Non-COD &amp; Auto-Invoicing
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
                    >Tunai di muka cabang asal</span
                >
            </section>
            <section class="pache-metric-card">
                <span class="text-[12px] font-semibold text-[var(--muted)]"
                    >Piutang B2B TOP Berjalan</span
                >
                <strong
                    class="mt-2 text-[24px] font-bold text-[var(--ink)] tabular-nums"
                    >{{ formatIdr(summary.top_ar_unpaid) }}</strong
                >
                <span class="mt-2 text-[11.5px] text-[var(--muted)]"
                    >Invoice belum jatuh tempo / overdue</span
                >
            </section>
            <section class="pache-metric-card">
                <span class="text-[12px] font-semibold text-[var(--muted)]"
                    >Tertahan Dispatch Gate</span
                >
                <strong
                    class="mt-2 text-[24px] font-bold text-[#b42318] tabular-nums"
                    >{{ summary.pending_gate_count }} Resi</strong
                >
                <span class="mt-2 text-[11.5px] text-[var(--muted)]"
                    >Menunggu pelunasan sebelum Manifest</span
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
                <span>B2B TOP &amp; Auto-Invoicing</span>
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

        <!-- TAB 1: B2B TOP & AUTO-INVOICING -->
        <div v-if="activeTab === 'top'" class="space-y-5">
            <section class="pache-panel overflow-hidden">
                <div class="border-b border-[var(--hairline)] px-5 py-4">
                    <h2 class="pache-section-title">
                        Auto-Invoicing Klien Kontrak TOP
                    </h2>
                    <p class="text-[12.5px] text-[var(--muted)]">
                        Tarik seluruh resi TOP berstatus Delivered yang belum
                        ditagih menjadi Invoice (otomatis PPN 11% − PPh 23 2%).
                    </p>
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
                                <th class="text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="inv in invoices" :key="inv.id">
                                <td class="font-bold tabular-nums">
                                    {{ inv.invoice_number }}
                                </td>
                                <td>
                                    <strong class="block">{{
                                        inv.customer_name
                                    }}</strong>
                                    <span
                                        class="text-[12px] text-[var(--muted)]"
                                        >{{ inv.period }} ·
                                        {{ inv.shipment_count }} resi</span
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
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- TAB 2: KASIR LOKET (CASH UPFRONT) -->
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

        <!-- TAB 3: ONLINE PAYMENT (QRIS / VA) -->
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

        <!-- TAB 4: IMMUTABLE FINANCIAL LEDGER -->
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
                                class="text-[11.5px] font-bold text-[var(--brand-blue)] uppercase"
                            >
                                {{ l.channel }}
                            </td>
                            <td>{{ l.description }}</td>
                            <td
                                class="text-right font-bold text-[#15803d] tabular-nums"
                            >
                                +{{ formatIdr(l.amount) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>
