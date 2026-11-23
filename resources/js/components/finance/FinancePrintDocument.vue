<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Printer } from '@lucide/vue';
import PacheLogo from '@/components/PacheLogo.vue';
import type { CompanyProfile, DocumentLineItem } from '@/lib/finance';
import { formatIdr } from '@/lib/format';

defineProps<{
    pageTitle: string;
    documentTitle: string;
    documentBadge: string;
    documentNumber: string;
    secondaryNumberLabel?: string;
    secondaryNumber?: string | null;
    backHref: string;
    company: CompanyProfile;
    partyHeading: string;
    partyName: string;
    partyCode: string;
    partyMeta?: string | null;
    partyPhone: string;
    partyEmail?: string | null;
    partyAddress?: string | null;
    metaRows: Array<{ label: string; value: string }>;
    lines: DocumentLineItem[];
    subtotal: number;
    applyPpn: boolean;
    ppnAmount: number;
    applyPph23: boolean;
    pph23Amount: number;
    totalAmount: number;
    status: string;
    paidLabel: string;
    unpaidLabel: string;
    paidAt?: string | null;
    paymentReference?: string | null;
    notes?: string | null;
    footerNote: string;
    preparedByLabel: string;
    counterpartyLabel: string;
}>();

function triggerPrint(): void {
    window.print();
}
</script>

<template>
    <Head :title="pageTitle" />

    <div
        class="min-h-screen bg-[#edf3fa] px-4 py-8 text-[#111111] print:bg-white print:p-0"
    >
        <div
            class="no-print mx-auto mb-5 flex max-w-[820px] items-center justify-between"
        >
            <Link :href="backHref" class="pache-btn-outline">
                <ArrowLeft class="h-4 w-4" />
                <span>Kembali ke Detail</span>
            </Link>
            <button
                type="button"
                @click="triggerPrint"
                class="pache-btn-primary"
            >
                <Printer class="h-4 w-4" />
                <span>Cetak / Simpan PDF (A4)</span>
            </button>
        </div>

        <article
            class="mx-auto max-w-[820px] rounded-[12px] border border-[#dce5f1] bg-white p-8 shadow-sm sm:p-10 print:max-w-none print:rounded-none print:border-0 print:p-0 print:shadow-none"
        >
            <!-- Letterhead -->
            <header
                class="flex flex-col justify-between gap-6 border-b-2 border-[#172b46] pb-6 sm:flex-row sm:items-start"
            >
                <div class="space-y-2">
                    <PacheLogo />
                    <div
                        class="pt-1 text-[12px] leading-relaxed text-[#475569]"
                    >
                        <p class="font-bold text-[#172b46]">
                            {{ company.name }}
                        </p>
                        <p>{{ company.address }}</p>
                        <p>
                            Telp: {{ company.phone }} · Email:
                            {{ company.email }}
                        </p>
                        <p class="font-semibold text-[#172b46] tabular-nums">
                            NPWP: {{ company.npwp }}
                        </p>
                    </div>
                </div>

                <div class="text-left sm:text-right">
                    <span
                        class="inline-block rounded-[6px] bg-[#172b46] px-2.5 py-1 text-[10.5px] font-bold tracking-[0.14em] text-white uppercase"
                    >
                        {{ documentBadge }}
                    </span>
                    <h1
                        class="mt-2 text-[28px] leading-none font-extrabold tracking-[-0.03em] text-[#172b46]"
                    >
                        {{ documentTitle }}
                    </h1>
                    <p
                        class="mt-1.5 font-mono text-[15px] font-bold text-[#2563eb] tabular-nums"
                    >
                        {{ documentNumber }}
                    </p>
                    <p
                        v-if="secondaryNumber"
                        class="mt-0.5 text-[12px] font-semibold text-[#475569]"
                    >
                        {{ secondaryNumberLabel }}: {{ secondaryNumber }}
                    </p>
                    <div class="mt-3">
                        <span
                            class="inline-flex items-center rounded-[6px] border px-3 py-1 text-[11px] font-extrabold tracking-[0.08em] uppercase"
                            :class="
                                status === 'paid'
                                    ? 'border-[#15803d] bg-[#ecfdf3] text-[#15803d]'
                                    : 'border-[#b42318] bg-[#fef2f2] text-[#b42318]'
                            "
                        >
                            {{ status === 'paid' ? paidLabel : unpaidLabel }}
                        </span>
                    </div>
                </div>
            </header>

            <!-- Party & Metadata -->
            <section
                class="mt-6 grid grid-cols-1 gap-6 border-b border-[#dce5f1] pb-6 sm:grid-cols-2"
            >
                <div>
                    <p
                        class="text-[11px] font-bold tracking-[0.1em] text-[#64748b] uppercase"
                    >
                        {{ partyHeading }}
                    </p>
                    <p class="mt-1.5 text-[16px] font-bold text-[#172b46]">
                        {{ partyName }}
                    </p>
                    <p
                        class="text-[12.5px] font-semibold text-[#2563eb] tabular-nums"
                    >
                        {{ partyCode }}
                        <span v-if="partyMeta" class="text-[#475569]">
                            · {{ partyMeta }}
                        </span>
                    </p>
                    <p
                        v-if="partyAddress"
                        class="mt-1 text-[12.5px] leading-relaxed text-[#475569]"
                    >
                        {{ partyAddress }}
                    </p>
                    <p class="mt-1 text-[12.5px] text-[#475569]">
                        Telp: {{ partyPhone
                        }}<span v-if="partyEmail"> · {{ partyEmail }}</span>
                    </p>
                </div>

                <div
                    class="rounded-[10px] border border-[#dce5f1] bg-[#f8fafc] p-4"
                >
                    <dl class="space-y-2 text-[13px]">
                        <div
                            v-for="row in metaRows"
                            :key="row.label"
                            class="flex items-center justify-between gap-4"
                        >
                            <dt class="text-[#64748b]">{{ row.label }}</dt>
                            <dd
                                class="text-right font-semibold text-[#172b46] tabular-nums"
                            >
                                {{ row.value }}
                            </dd>
                        </div>
                        <div
                            v-if="paidAt"
                            class="flex items-center justify-between gap-4 border-t border-[#dce5f1] pt-2"
                        >
                            <dt class="text-[#64748b]">Waktu Lunas</dt>
                            <dd
                                class="text-right font-bold text-[#15803d] tabular-nums"
                            >
                                {{ paidAt }}
                            </dd>
                        </div>
                        <div
                            v-if="paymentReference"
                            class="flex items-center justify-between gap-4"
                        >
                            <dt class="text-[#64748b]">Ref. Pembayaran</dt>
                            <dd
                                class="text-right font-mono text-[12px] font-semibold text-[#172b46]"
                            >
                                {{ paymentReference }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </section>

            <!-- Line Items -->
            <section class="mt-6">
                <table class="w-full border-collapse text-[13px]">
                    <thead>
                        <tr class="border-b-2 border-[#172b46] bg-[#f3f7fc]">
                            <th
                                class="px-3 py-2.5 text-left text-[11px] font-bold tracking-[0.06em] text-[#475569] uppercase"
                            >
                                No
                            </th>
                            <th
                                class="px-3 py-2.5 text-left text-[11px] font-bold tracking-[0.06em] text-[#475569] uppercase"
                            >
                                Deskripsi / Referensi AWB
                            </th>
                            <th
                                class="px-3 py-2.5 text-right text-[11px] font-bold tracking-[0.06em] text-[#475569] uppercase"
                            >
                                Qty
                            </th>
                            <th
                                class="px-3 py-2.5 text-right text-[11px] font-bold tracking-[0.06em] text-[#475569] uppercase"
                            >
                                Harga Satuan
                            </th>
                            <th
                                class="px-3 py-2.5 text-right text-[11px] font-bold tracking-[0.06em] text-[#475569] uppercase"
                            >
                                Jumlah
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(line, index) in lines"
                            :key="line.id"
                            class="border-b border-[#e2ebf5]"
                        >
                            <td class="px-3 py-3 text-[#64748b] tabular-nums">
                                {{ index + 1 }}
                            </td>
                            <td class="px-3 py-3">
                                <p class="font-semibold text-[#172b46]">
                                    {{ line.description }}
                                </p>
                                <p
                                    v-if="line.awb_number"
                                    class="mt-0.5 text-[11.5px] text-[#64748b] tabular-nums"
                                >
                                    AWB: {{ line.awb_number
                                    }}<span v-if="line.route">
                                        · {{ line.route }}</span
                                    ><span v-if="line.chargeable_weight_kg">
                                        ·
                                        {{ line.chargeable_weight_kg }} kg</span
                                    >
                                </p>
                            </td>
                            <td class="px-3 py-3 text-right tabular-nums">
                                {{ line.quantity }} {{ line.unit }}
                            </td>
                            <td class="px-3 py-3 text-right tabular-nums">
                                {{ formatIdr(line.unit_price) }}
                            </td>
                            <td
                                class="px-3 py-3 text-right font-bold text-[#172b46] tabular-nums"
                            >
                                {{ formatIdr(line.amount) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Summary & Bank Info -->
            <section
                class="mt-6 grid grid-cols-1 gap-6 border-t border-[#dce5f1] pt-6 sm:grid-cols-[1.2fr_0.9fr]"
            >
                <div class="space-y-3 text-[12.5px] text-[#475569]">
                    <div
                        class="rounded-[10px] border border-[#dce5f1] bg-[#f8fafc] p-3.5"
                    >
                        <p
                            class="text-[11px] font-bold tracking-[0.08em] text-[#172b46] uppercase"
                        >
                            Informasi Pembayaran (Non-COD)
                        </p>
                        <p class="mt-1 font-medium text-[#172b46]">
                            {{ company.bank_account }}
                        </p>
                        <p class="mt-1 text-[11.5px] text-[#64748b]">
                            {{ footerNote }}
                        </p>
                    </div>

                    <div
                        v-if="notes"
                        class="rounded-[10px] border border-[#dce5f1] p-3.5"
                    >
                        <p
                            class="text-[11px] font-bold tracking-[0.08em] text-[#64748b] uppercase"
                        >
                            Catatan Dokumen
                        </p>
                        <p class="mt-1 text-[#172b46]">{{ notes }}</p>
                    </div>
                </div>

                <div
                    class="rounded-[10px] border border-[#dce5f1] bg-[#f8fafc] p-4 text-[13px]"
                >
                    <dl class="space-y-2">
                        <div class="flex items-center justify-between">
                            <dt class="text-[#64748b]">Subtotal</dt>
                            <dd
                                class="font-semibold text-[#172b46] tabular-nums"
                            >
                                {{ formatIdr(subtotal) }}
                            </dd>
                        </div>
                        <div
                            v-if="applyPpn || ppnAmount > 0"
                            class="flex items-center justify-between"
                        >
                            <dt class="text-[#64748b]">PPN (11%)</dt>
                            <dd
                                class="font-semibold text-[#172b46] tabular-nums"
                            >
                                +{{ formatIdr(ppnAmount) }}
                            </dd>
                        </div>
                        <div
                            v-if="applyPph23 || pph23Amount > 0"
                            class="flex items-center justify-between"
                        >
                            <dt class="text-[#64748b]">Potongan PPh 23 (2%)</dt>
                            <dd
                                class="font-semibold text-[#b42318] tabular-nums"
                            >
                                −{{ formatIdr(pph23Amount) }}
                            </dd>
                        </div>
                        <div
                            class="flex items-center justify-between border-t-2 border-[#172b46] pt-2.5 text-[16px]"
                        >
                            <dt class="font-bold text-[#172b46]">
                                Grand Total
                            </dt>
                            <dd
                                class="font-extrabold text-[#2563eb] tabular-nums"
                            >
                                {{ formatIdr(totalAmount) }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </section>

            <!-- Signatures -->
            <footer
                class="mt-10 grid grid-cols-2 gap-8 border-t border-[#e2ebf5] pt-6 text-center text-[12px] text-[#475569]"
            >
                <div>
                    <p class="font-semibold text-[#172b46]">
                        {{ preparedByLabel }}
                    </p>
                    <div class="mt-14 border-t border-[#cbd5e1] pt-1.5">
                        Finance &amp; Accounting PT Pache Logistic
                    </div>
                </div>
                <div>
                    <p class="font-semibold text-[#172b46]">
                        {{ counterpartyLabel }}
                    </p>
                    <div class="mt-14 border-t border-[#cbd5e1] pt-1.5">
                        {{ partyName }}
                    </div>
                </div>
            </footer>
        </article>
    </div>
</template>
