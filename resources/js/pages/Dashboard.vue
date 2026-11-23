<script setup lang="ts">
import { Deferred, Head, Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowUpRight,
    CheckCircle2,
    Package,
    PackagePlus,
    Printer,
    ReceiptText,
    ShieldAlert,
    Truck,
} from '@lucide/vue';
import {
    formatIdr,
    formatWeight,
    paymentBadgeClass,
    paymentStatusBadgeClass,
    shipmentStatusBadgeClass,
} from '@/lib/format';

interface SummaryMetrics {
    total_shipments: number;
    in_transit_count: number;
    delivered_count: number;
    total_revenue: number;
    paid_ledger_total: number;
    pending_payment_count: number;
    unpaid_invoices_total: number;
    epod_compliance_pct: number;
}

interface ChannelRow {
    key: string;
    label: string;
    count: number;
    amount: number;
    share_pct: number;
}

interface AlertRow {
    severity: 'danger' | 'warning' | 'info';
    code: string;
    title: string;
    detail: string;
    href: string;
}

interface RecentShipment {
    id: number;
    awb_number: string;
    customer_name: string;
    route: string;
    service_type: string;
    chargeable_weight_kg: number;
    total_amount: number;
    payment_method: string;
    payment_method_label: string;
    payment_status: string;
    payment_status_label: string;
    status: string;
    status_label: string;
    created_at: string;
}

defineProps<{
    period: string;
    recentShipments: RecentShipment[];
    summaryMetrics?: SummaryMetrics;
    channelBreakdown?: ChannelRow[];
    alerts?: AlertRow[];
}>();
</script>

<template>
    <Head title="Dashboard Operasional" />

    <div class="space-y-6">
        <!-- Page Heading -->
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="pache-eyebrow">PACHE LOGISTIC · CONTROL TOWER</p>
                <h1 class="pache-page-title">
                    Ringkasan Operasional &amp; Arus Pembayaran Non-COD
                </h1>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <Link href="/oms/shipments" class="pache-btn-outline">
                    <Package class="h-4 w-4 text-[var(--brand-blue)]" />
                    <span>Semua Resi</span>
                </Link>
                <Link href="/oms/shipments/create" class="pache-btn-primary">
                    <PackagePlus class="h-4 w-4" />
                    <span>Buat Resi Non-COD</span>
                </Link>
            </div>
        </div>

        <!-- Bento KPI Metric Cards (Deferred via Inertia v3) -->
        <Deferred data="summaryMetrics">
            <template #fallback>
                <div
                    class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 xl:grid-cols-4"
                >
                    <div
                        v-for="n in 4"
                        :key="n"
                        class="h-[146px] animate-pulse rounded-[15px] bg-[var(--surface-soft)]"
                    />
                </div>
            </template>

            <div
                v-if="summaryMetrics"
                class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 xl:grid-cols-4"
            >
                <!-- Card 1: Solid Accent #2563eb + .metric-motif SVG -->
                <Link href="/oms/shipments" class="no-underline">
                    <section class="pache-metric-card variant-primary">
                        <svg
                            class="metric-motif"
                            viewBox="0 0 260 160"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.25"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                            preserveAspectRatio="xMaxYMid slice"
                        >
                            <path
                                d="M18 172c58 0 47-70 104-70h30c61 0 39-82 112-82"
                            />
                            <path
                                d="M34 180c58 0 47-70 104-70h30c61 0 39-82 112-82"
                            />
                            <path
                                d="M50 188c58 0 47-70 104-70h30c61 0 39-82 112-82"
                            />
                            <path
                                d="M66 196c58 0 47-70 104-70h30c61 0 39-82 112-82"
                            />
                            <path
                                d="M82 204c58 0 47-70 104-70h30c61 0 39-82 112-82"
                            />
                        </svg>
                        <div
                            class="relative z-10 flex items-center justify-between"
                        >
                            <span class="text-[12px] font-semibold opacity-90"
                                >Total Nilai Pengiriman</span
                            >
                            <ReceiptText class="h-4 w-4 opacity-80" />
                        </div>
                        <strong
                            class="relative z-10 mt-2 text-[26px] leading-tight font-[650] tracking-[-0.035em] tabular-nums"
                        >
                            {{ formatIdr(summaryMetrics.total_revenue) }}
                        </strong>
                        <footer
                            class="relative z-10 mt-3 flex items-center gap-2 text-[11.5px]"
                        >
                            <b
                                class="rounded bg-white/20 px-1.5 py-0.5 text-[10.5px] font-bold tabular-nums"
                            >
                                {{ summaryMetrics.total_shipments }} AWB
                            </b>
                            <span class="opacity-85">Online · Cash · TOP</span>
                        </footer>
                    </section>
                </Link>

                <!-- Card 2: Soft Accent #dcecff -->
                <Link href="/tms/manifests" class="no-underline">
                    <section class="pache-metric-card variant-soft">
                        <div
                            class="relative z-10 flex items-center justify-between"
                        >
                            <span class="text-[12px] font-semibold"
                                >Paket Dalam Perjalanan</span
                            >
                            <Truck class="h-4 w-4" />
                        </div>
                        <strong
                            class="relative z-10 mt-2 text-[26px] leading-tight font-[650] tracking-[-0.035em] tabular-nums"
                        >
                            {{ summaryMetrics.in_transit_count }} Resi
                        </strong>
                        <footer
                            class="relative z-10 mt-3 flex items-center gap-2 text-[11.5px]"
                        >
                            <b
                                class="rounded border border-[#bfdbfe] bg-white/75 px-1.5 py-0.5 text-[10.5px] font-bold text-[#1d4ed8] tabular-nums dark:border-[#303030] dark:bg-[#242424] dark:text-[#93c5fd]"
                            >
                                {{ summaryMetrics.delivered_count }} Delivered
                            </b>
                            <span class="metric-sub"
                                >e-POD
                                {{ summaryMetrics.epod_compliance_pct }}%</span
                            >
                        </footer>
                    </section>
                </Link>

                <!-- Card 3: Surface Soft -->
                <Link href="/finance" class="no-underline">
                    <section class="pache-metric-card">
                        <svg
                            class="metric-motif"
                            viewBox="0 0 260 160"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.25"
                            aria-hidden="true"
                            preserveAspectRatio="xMaxYMid slice"
                        >
                            <path
                                d="M50 188c58 0 47-70 104-70h30c61 0 39-82 112-82"
                            />
                            <path
                                d="M82 204c58 0 47-70 104-70h30c61 0 39-82 112-82"
                            />
                        </svg>
                        <div
                            class="relative z-10 flex items-center justify-between"
                        >
                            <span
                                class="text-[12px] font-semibold text-[var(--muted)]"
                                >Kas Masuk Terverifikasi</span
                            >
                            <CheckCircle2 class="h-4 w-4 text-[#15803d]" />
                        </div>
                        <strong
                            class="relative z-10 mt-2 text-[25px] leading-tight font-[650] tracking-[-0.035em] text-[var(--ink)] tabular-nums"
                        >
                            {{ formatIdr(summaryMetrics.paid_ledger_total) }}
                        </strong>
                        <footer
                            class="relative z-10 mt-3 flex items-center gap-2 text-[11.5px] text-[var(--muted)]"
                        >
                            <b
                                class="rounded border border-[var(--hairline)] bg-[var(--surface)] px-1.5 py-0.5 text-[10.5px] font-bold text-[var(--ink)] tabular-nums"
                            >
                                {{ summaryMetrics.pending_payment_count }}
                                Pending
                            </b>
                            <span>Tertahan Dispatch Gate</span>
                        </footer>
                    </section>
                </Link>

                <!-- Card 4: Surface Soft -->
                <Link href="/finance" class="no-underline">
                    <section class="pache-metric-card">
                        <div
                            class="relative z-10 flex items-center justify-between"
                        >
                            <span
                                class="text-[12px] font-semibold text-[var(--muted)]"
                                >Piutang Invoice B2B TOP</span
                            >
                            <ShieldAlert class="h-4 w-4 text-[#b45309]" />
                        </div>
                        <strong
                            class="relative z-10 mt-2 text-[25px] leading-tight font-[650] tracking-[-0.035em] text-[var(--ink)] tabular-nums"
                        >
                            {{
                                formatIdr(summaryMetrics.unpaid_invoices_total)
                            }}
                        </strong>
                        <footer
                            class="relative z-10 mt-3 flex items-center gap-2 text-[11.5px] text-[var(--muted)]"
                        >
                            <b
                                class="rounded border border-[var(--hairline)] bg-[var(--surface)] px-1.5 py-0.5 text-[10.5px] font-bold text-[#15803d]"
                            >
                                0% COD
                            </b>
                            <span>Kontrol Plafon Kredit</span>
                        </footer>
                    </section>
                </Link>
            </div>
        </Deferred>

        <!-- Asymmetric Bento Row: Channel Breakdown (Left) + Operational Alerts (Right) -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1.35fr_0.85fr]">
            <!-- Non-COD Channel Breakdown -->
            <section class="pache-panel p-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2
                            class="text-[17px] font-[650] tracking-[-0.015em] text-[var(--ink)]"
                        >
                            Distribusi Kanal Pembayaran (Strictly Non-COD)
                        </h2>
                        <p class="mt-0.5 text-[12.5px] text-[var(--muted)]">
                            Komposisi nilai pengiriman berdasarkan 3 kanal resmi
                            Pache Logistic.
                        </p>
                    </div>
                    <Link
                        href="/finance"
                        class="inline-flex items-center gap-1 text-[12.5px] font-bold text-[var(--brand-blue)] no-underline hover:underline"
                    >
                        <span>Buka Finance</span>
                        <ArrowUpRight class="h-3.5 w-3.5" />
                    </Link>
                </div>

                <Deferred data="channelBreakdown">
                    <template #fallback>
                        <div class="mt-5 space-y-4">
                            <div
                                v-for="n in 3"
                                :key="n"
                                class="h-14 animate-pulse rounded-xl bg-[var(--surface-soft)]"
                            />
                        </div>
                    </template>

                    <div v-if="channelBreakdown" class="mt-5 space-y-4">
                        <div
                            v-for="row in channelBreakdown"
                            :key="row.key"
                            class="rounded-[12px] border border-[var(--hairline)] bg-[var(--surface-soft)] p-3.5"
                        >
                            <div
                                class="flex flex-wrap items-center justify-between gap-2 text-[13.5px]"
                            >
                                <div class="flex items-center gap-2">
                                    <span
                                        class="rounded-[6px] border px-2 py-0.5 text-[11px] font-bold"
                                        :class="paymentBadgeClass(row.key)"
                                    >
                                        {{ row.key.toUpperCase() }}
                                    </span>
                                    <strong
                                        class="font-semibold text-[var(--ink)]"
                                        >{{ row.label }}</strong
                                    >
                                    <span
                                        class="text-[12px] text-[var(--muted)] tabular-nums"
                                        >({{ row.count }} resi)</span
                                    >
                                </div>
                                <div class="text-right tabular-nums">
                                    <strong
                                        class="font-bold text-[var(--ink)]"
                                        >{{ formatIdr(row.amount) }}</strong
                                    >
                                    <span
                                        class="ml-2 text-[12px] font-semibold text-[var(--brand-blue)]"
                                        >{{ row.share_pct }}%</span
                                    >
                                </div>
                            </div>
                            <div
                                class="mt-2.5 h-2 w-full overflow-hidden rounded-full bg-[var(--surface)]"
                            >
                                <div
                                    class="h-full rounded-full transition-all duration-500"
                                    :class="
                                        row.key === 'online'
                                            ? 'bg-[#2563eb]'
                                            : row.key === 'cash'
                                              ? 'bg-[#15803d]'
                                              : 'bg-[#d3a56b]'
                                    "
                                    :style="{
                                        width: `${Math.max(4, row.share_pct)}%`,
                                    }"
                                />
                            </div>
                        </div>
                    </div>
                </Deferred>
            </section>

            <!-- Operational Alerts Panel -->
            <section class="pache-panel p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2
                            class="text-[17px] font-[650] tracking-[-0.015em] text-[var(--ink)]"
                        >
                            Perlu Perhatian Operasional
                        </h2>
                        <p class="mt-0.5 text-[12.5px] text-[var(--muted)]">
                            Peringatan otomatis plafon kredit, kepatuhan armada,
                            dan validasi bayar.
                        </p>
                    </div>
                </div>

                <Deferred data="alerts">
                    <template #fallback>
                        <div class="mt-4 space-y-2.5">
                            <div
                                v-for="n in 3"
                                :key="n"
                                class="h-16 animate-pulse rounded-xl bg-[var(--surface-soft)]"
                            />
                        </div>
                    </template>

                    <div
                        v-if="alerts && alerts.length"
                        class="mt-4 space-y-2.5"
                    >
                        <Link
                            v-for="(alert, i) in alerts"
                            :key="i"
                            :href="alert.href"
                            class="flex items-start gap-3 rounded-[12px] border border-[var(--hairline)] bg-[var(--surface-soft)] p-3.5 no-underline transition hover:border-[var(--brand-blue)]"
                        >
                            <AlertTriangle
                                class="mt-0.5 h-4 w-4 shrink-0"
                                :class="
                                    alert.severity === 'danger'
                                        ? 'text-[#dc2626]'
                                        : alert.severity === 'warning'
                                          ? 'text-[#d97706]'
                                          : 'text-[var(--brand-blue)]'
                                "
                            />
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="rounded bg-[var(--surface)] px-1.5 py-0.5 text-[10px] font-bold text-[var(--brand-blue)]"
                                    >
                                        {{ alert.code }}
                                    </span>
                                    <strong
                                        class="truncate text-[13px] font-bold text-[var(--ink)]"
                                    >
                                        {{ alert.title }}
                                    </strong>
                                </div>
                                <p
                                    class="mt-1 text-[12px] leading-snug text-[var(--muted)]"
                                >
                                    {{ alert.detail }}
                                </p>
                            </div>
                        </Link>
                    </div>
                    <p
                        v-else
                        class="mt-6 text-center text-[13px] text-[var(--muted)]"
                    >
                        Seluruh operasional berjalan bersih tanpa anomali.
                    </p>
                </Deferred>
            </section>
        </div>

        <!-- Recent Shipments Table -->
        <section class="pache-panel overflow-hidden">
            <div
                class="flex flex-wrap items-center justify-between gap-3 border-b border-[var(--hairline)] px-5 py-4"
            >
                <div>
                    <h2
                        class="text-[17px] font-[650] tracking-[-0.015em] text-[var(--ink)]"
                    >
                        Pengiriman Terbaru
                    </h2>
                    <p class="text-[12.5px] text-[var(--muted)]">
                        Klik nomor AWB untuk mengelola transisi status,
                        verifikasi pembayaran, atau e-POD.
                    </p>
                </div>
                <Link href="/oms/shipments" class="pache-btn-outline">
                    Lihat Semua Resi
                </Link>
            </div>
            <div class="overflow-x-auto">
                <table class="pache-table">
                    <thead>
                        <tr>
                            <th>Nomor AWB</th>
                            <th>Pelanggan</th>
                            <th>Rute &amp; Layanan</th>
                            <th>Berat Tagih</th>
                            <th>Total Ongkir</th>
                            <th>Kanal Non-COD</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="s in recentShipments" :key="s.id">
                            <td class="font-bold tabular-nums">
                                <Link
                                    :href="`/oms/shipments/${s.awb_number}`"
                                    class="text-[var(--brand-blue)] no-underline hover:underline"
                                >
                                    {{ s.awb_number }}
                                </Link>
                            </td>
                            <td>
                                <span class="font-semibold">{{
                                    s.customer_name
                                }}</span>
                            </td>
                            <td>
                                <span class="font-semibold">{{ s.route }}</span>
                                <span
                                    class="ml-1.5 rounded bg-[var(--surface-soft)] px-1.5 py-0.5 text-[11px] font-bold text-[var(--muted)]"
                                    >{{ s.service_type }}</span
                                >
                            </td>
                            <td class="tabular-nums">
                                {{ formatWeight(s.chargeable_weight_kg) }}
                            </td>
                            <td class="font-semibold tabular-nums">
                                {{ formatIdr(s.total_amount) }}
                            </td>
                            <td>
                                <div
                                    class="flex flex-wrap items-center gap-1.5"
                                >
                                    <span
                                        class="rounded-[6px] border px-2 py-0.5 text-[11px] font-bold"
                                        :class="
                                            paymentBadgeClass(s.payment_method)
                                        "
                                    >
                                        {{ s.payment_method_label }}
                                    </span>
                                    <span
                                        class="rounded-[6px] border px-2 py-0.5 text-[10.5px] font-semibold"
                                        :class="
                                            paymentStatusBadgeClass(
                                                s.payment_status,
                                            )
                                        "
                                    >
                                        {{ s.payment_status.toUpperCase() }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="rounded-[6px] border px-2 py-0.5 text-[11px] font-bold"
                                    :class="shipmentStatusBadgeClass(s.status)"
                                >
                                    {{ s.status_label }}
                                </span>
                            </td>
                            <td class="text-right">
                                <Link
                                    :href="`/oms/shipments/${s.awb_number}/label`"
                                    class="inline-flex items-center gap-1 rounded-lg border border-[var(--hairline)] px-2.5 py-1 text-[12px] font-semibold text-[var(--ink)] no-underline hover:border-[var(--brand-blue)] hover:text-[var(--brand-blue)]"
                                >
                                    <Printer class="h-3.5 w-3.5" />
                                    <span>A6</span>
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>
