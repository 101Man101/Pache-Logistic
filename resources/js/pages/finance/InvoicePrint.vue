<script setup lang="ts">
import { computed } from 'vue';
import FinancePrintDocument from '@/components/finance/FinancePrintDocument.vue';
import type { CompanyProfile, InvoiceDetailPayload } from '@/lib/finance';

const props = defineProps<{
    company: CompanyProfile;
    invoice: InvoiceDetailPayload;
}>();

const metaRows = computed(() => [
    { label: 'Jenis Invoice', value: props.invoice.invoice_type_label },
    { label: 'Periode Penagihan', value: props.invoice.period },
    { label: 'Jatuh Tempo', value: props.invoice.due_date },
    { label: 'Status Aging', value: props.invoice.aging_bucket },
]);
</script>

<template>
    <FinancePrintDocument
        :page-title="`Cetak Invoice ${invoice.invoice_number}`"
        document-title="INVOICE"
        :document-badge="invoice.invoice_type_label"
        :document-number="invoice.invoice_number"
        :back-href="`/finance/invoices/${invoice.id}`"
        :company="company"
        party-heading="Ditagihkan Kepada (Bill To)"
        :party-name="invoice.customer.name"
        :party-code="invoice.customer.code"
        :party-meta="
            invoice.customer.npwp ? `NPWP: ${invoice.customer.npwp}` : null
        "
        :party-phone="invoice.customer.phone"
        :party-email="invoice.customer.email"
        :party-address="invoice.customer.address"
        :meta-rows="metaRows"
        :lines="invoice.lines"
        :subtotal="invoice.subtotal"
        :apply-ppn="invoice.apply_ppn"
        :ppn-amount="invoice.ppn_amount"
        :apply-pph23="invoice.apply_pph23"
        :pph23-amount="invoice.pph23_amount"
        :total-amount="invoice.total_amount"
        :status="invoice.status"
        paid-label="LUNAS"
        unpaid-label="BELUM LUNAS"
        :paid-at="invoice.paid_at"
        :payment-reference="invoice.payment_reference"
        :notes="invoice.notes"
        footer-note="Harap cantumkan nomor invoice pada berita transfer. Pache Logistic beroperasi 100% Non-COD."
        prepared-by-label="Diterbitkan Oleh"
        counterparty-label="Diterima Oleh"
    />
</template>
