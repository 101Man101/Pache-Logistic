<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, Printer } from '@lucide/vue';
import type { DocumentLineItem } from '@/lib/finance';
import { formatIdr } from '@/lib/format';

const props = defineProps<{
    eyebrow: string;
    title: string;
    documentNumber: string;
    typeBadge: string;
    printHref: string;
    markPaidAction: string;
    markPaidButtonLabel: string;
    paidBadgeText: string;
    unpaidBadgeText: string;
    status: string;
    agingBucket: string;
    paidAt: string | null;
    paymentReference: string | null;
    partySectionTitle: string;
    partyName: string;
    partyCode: string;
    partyMeta: string | null;
    partyPhone: string;
    partyEmail: string | null;
    partyAddress: string | null;
    summaryFields: Array<{
        label: string;
        value: string;
        href?: string | null;
    }>;
    lines: DocumentLineItem[];
    subtotal: number;
    applyPpn: boolean;
    ppnAmount: number;
    applyPph23: boolean;
    pph23Amount: number;
    totalAmount: number;
    notes: string | null;
}>();

const settleForm = useForm({
    payment_reference: '',
});

function submitPayment(): void {
    settleForm.post(props.markPaidAction, {
        preserveScroll: true,
        onSuccess: () => settleForm.reset(),
    });
}
</script>

<template>
    <div class="space-y-5">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <Link
                    href="/finance"
                    class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-[var(--brand-blue)] no-underline hover:underline"
                >
                    <ArrowLeft class="h-3.5 w-3.5" />
                    <span>Kembali ke Finance &amp; Invoicing</span>
                </Link>
                <p class="pache-eyebrow mt-2">{{ eyebrow }}</p>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="pache-page-title tabular-nums">
                        {{ documentNumber }}
                    </h1>
                    <span
                        class="rounded-[6px] border border-[#bfdbfe] bg-[#eff6ff] px-2.5 py-0.5 text-[11px] font-bold text-[#1d4ed8] uppercase dark:border-[#1d4ed8]/40 dark:bg-[#1e3a8a]/30 dark:text-[#93c5fd]"
                    >
                        {{ typeBadge }}
                    </span>
                    <span
                        class="rounded-[6px] border px-2.5 py-0.5 text-[11px] font-bold uppercase"
                        :class="
                            status === 'paid'
                                ? 'border-[#bbf7d0] bg-[#ecfdf3] text-[#15803d] dark:border-[#15803d]/40 dark:bg-[#14532d]/30 dark:text-[#86efac]'
                                : 'border-[#fecaca] bg-[#fef2f2] text-[#991b1b] dark:border-[#dc2626]/40 dark:bg-[#7f1d1d]/30 dark:text-[#fca5a5]'
                        "
                    >
                        {{
                            status === 'paid'
                                ? paidBadgeText
                                : `${unpaidBadgeText} · ${agingBucket}`
                        }}
                    </span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <Link :href="printHref" class="pache-btn-outline">
                    <Printer class="h-4 w-4" />
                    <span>Cetak / PDF (A4)</span>
                </Link>
            </div>
        </div>

        <!-- Party & Metadata Cards -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1.2fr_1fr]">
            <section class="pache-panel p-5">
                <p
                    class="text-[11px] font-bold tracking-[0.09em] text-[var(--muted)] uppercase"
                >
                    {{ partySectionTitle }}
                </p>
                <h2 class="mt-1.5 text-[18px] font-bold text-[var(--ink)]">
                    {{ partyName }}
                </h2>
                <p
                    class="mt-0.5 text-[13px] font-semibold text-[var(--brand-blue)] tabular-nums"
                >
                    {{ partyCode }}
                    <span v-if="partyMeta" class="text-[var(--muted)]">
                        · {{ partyMeta }}
                    </span>
                </p>
                <p
                    v-if="partyAddress"
                    class="mt-2 text-[13px] leading-relaxed text-[var(--muted)]"
                >
                    {{ partyAddress }}
                </p>
                <p class="mt-1 text-[13px] text-[var(--muted)]">
                    Telp: {{ partyPhone
                    }}<span v-if="partyEmail"> · {{ partyEmail }}</span>
                </p>
                <div
                    v-if="notes"
                    class="mt-3 rounded-[8px] border border-[var(--hairline)] bg-[var(--surface-soft)] px-3 py-2 text-[12.5px] text-[var(--ink)]"
                >
                    <strong class="text-[var(--muted)]">Catatan:</strong>
                    {{ notes }}
                </div>
            </section>

            <section class="pache-panel p-5">
                <dl class="space-y-2.5 text-[13.5px]">
                    <div
                        v-for="field in summaryFields"
                        :key="field.label"
                        class="flex items-center justify-between gap-3"
                    >
                        <dt class="text-[var(--muted)]">{{ field.label }}</dt>
                        <dd
                            class="font-semibold text-[var(--ink)] tabular-nums"
                        >
                            <Link
                                v-if="field.href"
                                :href="field.href"
                                class="text-[var(--brand-blue)] no-underline hover:underline"
                            >
                                {{ field.value }}
                            </Link>
                            <span v-else>{{ field.value }}</span>
                        </dd>
                    </div>
                    <div
                        v-if="paidAt"
                        class="flex items-center justify-between gap-3 border-t border-[var(--hairline)] pt-2.5"
                    >
                        <dt class="text-[var(--muted)]">Tanggal Pelunasan</dt>
                        <dd class="font-bold text-[#15803d] tabular-nums">
                            {{ paidAt }}
                        </dd>
                    </div>
                    <div
                        v-if="paymentReference"
                        class="flex items-center justify-between gap-3"
                    >
                        <dt class="text-[var(--muted)]">Referensi Bayar</dt>
                        <dd class="font-mono text-[12.5px] font-semibold">
                            {{ paymentReference }}
                        </dd>
                    </div>
                </dl>

                <!-- Settlement Action -->
                <form
                    v-if="status !== 'paid'"
                    @submit.prevent="submitPayment"
                    class="mt-4 space-y-2.5 border-t border-[var(--hairline)] pt-4"
                >
                    <label
                        class="block text-[12px] font-semibold text-[var(--muted)]"
                    >
                        Referensi Transfer / Bukti Bayar (opsional)
                    </label>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <input
                            v-model="settleForm.payment_reference"
                            type="text"
                            placeholder="Contoh: TRF-BCA-20261123"
                            class="pache-input flex-1"
                        />
                        <button
                            type="submit"
                            :disabled="settleForm.processing"
                            class="pache-btn-success shrink-0"
                        >
                            <CheckCircle2 class="h-4 w-4" />
                            <span>{{ markPaidButtonLabel }}</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>

        <!-- Lines Table -->
        <section class="pache-panel overflow-hidden">
            <div class="border-b border-[var(--hairline)] px-5 py-4">
                <h2 class="pache-section-title">{{ title }} — Rincian Item</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="pache-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Deskripsi / Referensi AWB</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Harga Satuan</th>
                            <th class="text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(line, index) in lines" :key="line.id">
                            <td class="text-[var(--muted)] tabular-nums">
                                {{ index + 1 }}
                            </td>
                            <td>
                                <span class="block font-semibold">{{
                                    line.description
                                }}</span>
                                <span
                                    v-if="line.awb_number"
                                    class="mt-0.5 inline-flex flex-wrap items-center gap-1.5 text-[12px] text-[var(--muted)] tabular-nums"
                                >
                                    <Link
                                        :href="`/oms/shipments/${line.awb_number}`"
                                        class="font-bold text-[var(--brand-blue)] no-underline hover:underline"
                                    >
                                        {{ line.awb_number }}
                                    </Link>
                                    <span v-if="line.route"
                                        >· {{ line.route }}</span
                                    >
                                    <span v-if="line.chargeable_weight_kg"
                                        >·
                                        {{ line.chargeable_weight_kg }} kg</span
                                    >
                                </span>
                            </td>
                            <td class="text-right tabular-nums">
                                {{ line.quantity }} {{ line.unit }}
                            </td>
                            <td class="text-right tabular-nums">
                                {{ formatIdr(line.unit_price) }}
                            </td>
                            <td class="text-right font-bold tabular-nums">
                                {{ formatIdr(line.amount) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Totals Footer -->
            <div
                class="flex justify-end border-t border-[var(--hairline)] bg-[var(--surface-soft)] px-5 py-4"
            >
                <dl class="w-full max-w-[340px] space-y-2 text-[13.5px]">
                    <div class="flex items-center justify-between">
                        <dt class="text-[var(--muted)]">Subtotal</dt>
                        <dd class="font-semibold tabular-nums">
                            {{ formatIdr(subtotal) }}
                        </dd>
                    </div>
                    <div
                        v-if="applyPpn || ppnAmount > 0"
                        class="flex items-center justify-between"
                    >
                        <dt class="text-[var(--muted)]">PPN (11%)</dt>
                        <dd class="font-semibold tabular-nums">
                            +{{ formatIdr(ppnAmount) }}
                        </dd>
                    </div>
                    <div
                        v-if="applyPph23 || pph23Amount > 0"
                        class="flex items-center justify-between"
                    >
                        <dt class="text-[var(--muted)]">
                            Potongan PPh 23 (2%)
                        </dt>
                        <dd class="font-semibold text-[#b42318] tabular-nums">
                            −{{ formatIdr(pph23Amount) }}
                        </dd>
                    </div>
                    <div
                        class="flex items-center justify-between border-t border-[var(--hairline)] pt-2 text-[16px]"
                    >
                        <dt class="font-bold text-[var(--ink)]">Total</dt>
                        <dd
                            class="font-extrabold text-[var(--brand-blue)] tabular-nums"
                        >
                            {{ formatIdr(totalAmount) }}
                        </dd>
                    </div>
                </dl>
            </div>
        </section>
    </div>
</template>
