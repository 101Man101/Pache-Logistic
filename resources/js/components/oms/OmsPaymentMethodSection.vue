<script setup lang="ts">
import { AlertTriangle, Building2, CreditCard, ReceiptText } from '@lucide/vue';
import { formatIdr } from '@/lib/format';
import type { CustomerOption, PaymentMethod } from '@/types/erp';

defineProps<{
    selectedCustomer?: CustomerOption;
    estimatedTotal: number;
    topWillExceedLimit: boolean;
}>();

const paymentMethod = defineModel<PaymentMethod>('paymentMethod', {
    required: true,
});
const cashReceived = defineModel<boolean>('cashReceived', { required: true });
</script>

<template>
    <section class="pache-panel space-y-4 p-5">
        <div>
            <h2 class="text-[16px] font-[650] text-[var(--ink)]">
                3. Metode Pembayaran (Strictly Non-COD)
            </h2>
            <p class="text-[12.5px] text-[var(--muted)]">
                Sistem Pache Logistic menolak COD. Pilih salah satu dari 3 kanal
                pembayaran resmi di bawah ini.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <label
                class="flex cursor-pointer flex-col gap-2 rounded-[13px] border p-4 transition"
                :class="
                    paymentMethod === 'online'
                        ? 'border-[#2563eb] bg-[#eff6ff] dark:bg-[#1e3a8a]/25'
                        : 'border-[var(--hairline)] bg-[var(--surface-soft)]'
                "
            >
                <div class="flex items-center justify-between">
                    <div
                        class="flex items-center gap-2 font-bold text-[var(--ink)]"
                    >
                        <CreditCard class="h-4 w-4 text-[#2563eb]" />
                        <span>Online Payment</span>
                    </div>
                    <input
                        type="radio"
                        value="online"
                        v-model="paymentMethod"
                        class="h-4 w-4 text-[#2563eb]"
                    />
                </div>
                <p class="text-[12px] text-[var(--muted)]">
                    QRIS Dinamis / Virtual Account (BCA, Mandiri, BRI, BNI).
                    Resi menunggu webhook lunas sebelum Manifest.
                </p>
            </label>

            <label
                class="flex cursor-pointer flex-col gap-2 rounded-[13px] border p-4 transition"
                :class="
                    paymentMethod === 'cash'
                        ? 'border-[#15803d] bg-[#ecfdf3] dark:bg-[#14532d]/25'
                        : 'border-[var(--hairline)] bg-[var(--surface-soft)]'
                "
            >
                <div class="flex items-center justify-between">
                    <div
                        class="flex items-center gap-2 font-bold text-[var(--ink)]"
                    >
                        <Building2 class="h-4 w-4 text-[#15803d]" />
                        <span>Cash (Loket Asal)</span>
                    </div>
                    <input
                        type="radio"
                        value="cash"
                        v-model="paymentMethod"
                        class="h-4 w-4 text-[#15803d]"
                    />
                </div>
                <p class="text-[12px] text-[var(--muted)]">
                    Tunai di muka di loket cabang asal saat drop-off. Kurir
                    last-mile tidak menagih penerima.
                </p>
            </label>

            <label
                class="flex flex-col gap-2 rounded-[13px] border p-4 transition"
                :class="[
                    !selectedCustomer?.is_top_enabled
                        ? 'cursor-not-allowed opacity-55'
                        : 'cursor-pointer',
                    paymentMethod === 'top'
                        ? 'border-[#d3a56b] bg-[#f8eddf] dark:bg-[#78350f]/25'
                        : 'border-[var(--hairline)] bg-[var(--surface-soft)]',
                ]"
            >
                <div class="flex items-center justify-between">
                    <div
                        class="flex items-center gap-2 font-bold text-[var(--ink)]"
                    >
                        <ReceiptText class="h-4 w-4 text-[#b45309]" />
                        <span>B2B Credit (TOP)</span>
                    </div>
                    <input
                        type="radio"
                        value="top"
                        v-model="paymentMethod"
                        :disabled="!selectedCustomer?.is_top_enabled"
                        class="h-4 w-4 text-[#b45309]"
                    />
                </div>
                <p class="text-[12px] text-[var(--muted)]">
                    <template v-if="selectedCustomer?.is_top_enabled">
                        Kontrak aktif Net {{ selectedCustomer.top_days }} hari.
                        Ditagihkan via Auto-Invoicing.
                    </template>
                    <template v-else>
                        Hanya tersedia untuk pelanggan kontrak korporat B2B.
                    </template>
                </p>
            </label>
        </div>

        <div
            v-if="paymentMethod === 'cash'"
            class="rounded-[11px] border border-[#bbf7d0] bg-[#ecfdf3] p-3.5 dark:border-[#15803d]/40 dark:bg-[#14532d]/20"
        >
            <label
                class="flex items-center gap-2.5 text-[13px] font-semibold text-[#15803d] dark:text-[#86efac]"
            >
                <input
                    v-model="cashReceived"
                    type="checkbox"
                    class="h-4 w-4 rounded"
                />
                <span
                    >Tunai sudah diterima lunas di Kasir Loket saat ini
                    (langsung berstatus PAID &amp; masuk Jurnal Kas)</span
                >
            </label>
        </div>

        <div
            v-if="paymentMethod === 'top' && selectedCustomer"
            class="rounded-[12px] border p-4"
            :class="
                topWillExceedLimit
                    ? 'border-[#fecaca] bg-[#fef2f2] text-[#991b1b]'
                    : 'border-[#e7cfa8] bg-[#f8eddf]/60 text-[#78350f] dark:border-[#b45309]/40 dark:bg-[#78350f]/20 dark:text-[#fcd34d]'
            "
        >
            <div
                class="flex flex-wrap items-center justify-between gap-2 text-[13px]"
            >
                <strong class="flex items-center gap-1.5">
                    <AlertTriangle v-if="topWillExceedLimit" class="h-4 w-4" />
                    <span>Pengecekan Plafon Kredit B2B (BR-02)</span>
                </strong>
                <span class="font-bold tabular-nums">
                    Eksposur Baru:
                    {{
                        formatIdr(
                            selectedCustomer.credit_exposure + estimatedTotal,
                        )
                    }}
                    / Limit: {{ formatIdr(selectedCustomer.credit_limit) }}
                </span>
            </div>
            <p v-if="topWillExceedLimit" class="mt-1 text-[12px] font-semibold">
                Melebihi Credit Limit! Backend akan menolak penerbitan resi ini
                hingga ada pelunasan Invoice TOP.
            </p>
        </div>
    </section>
</template>
