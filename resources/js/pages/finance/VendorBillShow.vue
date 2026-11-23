<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import FinanceDocumentDetail from '@/components/finance/FinanceDocumentDetail.vue';
import type { VendorBillDetailPayload } from '@/lib/finance';

const props = defineProps<{
    vendorBill: VendorBillDetailPayload;
}>();

const summaryFields = computed(() => {
    const fields: Array<{
        label: string;
        value: string;
        href?: string | null;
    }> = [
        {
            label: 'No. Invoice Vendor',
            value: props.vendorBill.vendor_invoice_no || '—',
        },
        { label: 'Tanggal Tagihan', value: props.vendorBill.bill_date },
        { label: 'Jatuh Tempo AP', value: props.vendorBill.due_date },
        {
            label: 'Cabang Beban',
            value: props.vendorBill.branch
                ? `${props.vendorBill.branch.code} (${props.vendorBill.branch.city})`
                : 'HO (Kantor Pusat)',
        },
    ];

    if (props.vendorBill.manifest) {
        fields.push({
            label: 'Referensi Manifest',
            value: `${props.vendorBill.manifest.manifest_number} (${props.vendorBill.manifest.route})`,
            href: '/tms/manifests',
        });
    }

    return fields;
});
</script>

<template>
    <Head :title="`Vendor Bill ${vendorBill.bill_number}`" />

    <FinanceDocumentDetail
        eyebrow="HUTANG USAHA & TAGIHAN VENDOR (AP)"
        title="Detail Tagihan Vendor"
        :document-number="vendorBill.bill_number"
        type-badge="VENDOR BILL (AP)"
        :print-href="`/finance/vendor-bills/${vendorBill.id}/print`"
        :mark-paid-action="`/finance/vendor-bills/${vendorBill.id}/mark-paid`"
        mark-paid-button-label="Bayar Tagihan Vendor"
        paid-badge-text="DIBAYAR"
        unpaid-badge-text="BELUM DIBAYAR"
        :status="vendorBill.status"
        :aging-bucket="vendorBill.aging_bucket"
        :paid-at="vendorBill.paid_at"
        :payment-reference="vendorBill.payment_reference"
        party-section-title="VENDOR / MITRA PENERIMA PEMBAYARAN"
        :party-name="vendorBill.vendor.name"
        :party-code="vendorBill.vendor.code"
        :party-meta="vendorBill.vendor.type_label"
        :party-phone="vendorBill.vendor.phone"
        :party-email="vendorBill.vendor.email"
        :party-address="vendorBill.vendor.address"
        :summary-fields="summaryFields"
        :lines="vendorBill.lines"
        :subtotal="vendorBill.subtotal"
        :apply-ppn="vendorBill.apply_ppn"
        :ppn-amount="vendorBill.ppn_amount"
        :apply-pph23="vendorBill.apply_pph23"
        :pph23-amount="vendorBill.pph23_amount"
        :total-amount="vendorBill.total_amount"
        :notes="vendorBill.notes"
    />
</template>
