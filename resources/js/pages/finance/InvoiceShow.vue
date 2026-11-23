<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import FinanceDocumentDetail from '@/components/finance/FinanceDocumentDetail.vue';
import type { InvoiceDetailPayload } from '@/lib/finance';

const props = defineProps<{
    invoice: InvoiceDetailPayload;
}>();

const summaryFields = computed(() => [
    { label: 'Periode Penagihan', value: props.invoice.period },
    { label: 'Jatuh Tempo', value: props.invoice.due_date },
    {
        label: 'Jumlah Item / Resi',
        value: `${props.invoice.lines.length} baris`,
    },
]);
</script>

<template>
    <Head :title="`Invoice ${invoice.invoice_number}`" />

    <FinanceDocumentDetail
        eyebrow="INVOICE PIUTANG USAHA (AR)"
        title="Detail Tagihan Pelanggan"
        :document-number="invoice.invoice_number"
        :type-badge="invoice.invoice_type_label"
        :print-href="`/finance/invoices/${invoice.id}/print`"
        :mark-paid-action="`/finance/invoices/${invoice.id}/mark-paid`"
        mark-paid-button-label="Tandai Lunas"
        paid-badge-text="LUNAS"
        unpaid-badge-text="BELUM LUNAS"
        :status="invoice.status"
        :aging-bucket="invoice.aging_bucket"
        :paid-at="invoice.paid_at"
        :payment-reference="invoice.payment_reference"
        party-section-title="DITAGIHKAN KEPADA (BILL TO)"
        :party-name="invoice.customer.name"
        :party-code="invoice.customer.code"
        :party-meta="
            invoice.customer.npwp ? `NPWP: ${invoice.customer.npwp}` : null
        "
        :party-phone="invoice.customer.phone"
        :party-email="invoice.customer.email"
        :party-address="invoice.customer.address"
        :summary-fields="summaryFields"
        :lines="invoice.lines"
        :subtotal="invoice.subtotal"
        :apply-ppn="invoice.apply_ppn"
        :ppn-amount="invoice.ppn_amount"
        :apply-pph23="invoice.apply_pph23"
        :pph23-amount="invoice.pph23_amount"
        :total-amount="invoice.total_amount"
        :notes="invoice.notes"
    />
</template>
