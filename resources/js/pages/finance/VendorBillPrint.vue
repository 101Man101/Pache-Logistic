<script setup lang="ts">
import { computed } from 'vue';
import FinancePrintDocument from '@/components/finance/FinancePrintDocument.vue';
import type { CompanyProfile, VendorBillDetailPayload } from '@/lib/finance';

const props = defineProps<{
    company: CompanyProfile;
    vendorBill: VendorBillDetailPayload;
}>();

const metaRows = computed(() => [
    { label: 'Tanggal Tagihan', value: props.vendorBill.bill_date },
    { label: 'Jatuh Tempo', value: props.vendorBill.due_date },
    {
        label: 'Cabang Beban',
        value: props.vendorBill.branch
            ? `${props.vendorBill.branch.code} (${props.vendorBill.branch.city})`
            : 'HO (Pusat)',
    },
    { label: 'Status Aging AP', value: props.vendorBill.aging_bucket },
]);
</script>

<template>
    <FinancePrintDocument
        :page-title="`Voucher AP ${vendorBill.bill_number}`"
        document-title="VOUCHER AP"
        document-badge="VENDOR BILL (AP)"
        :document-number="vendorBill.bill_number"
        secondary-number-label="Inv. Vendor"
        :secondary-number="vendorBill.vendor_invoice_no"
        :back-href="`/finance/vendor-bills/${vendorBill.id}`"
        :company="company"
        party-heading="Vendor / Supplier (Payable To)"
        :party-name="vendorBill.vendor.name"
        :party-code="vendorBill.vendor.code"
        :party-meta="vendorBill.vendor.type_label"
        :party-phone="vendorBill.vendor.phone"
        :party-email="vendorBill.vendor.email"
        :party-address="vendorBill.vendor.address"
        :meta-rows="metaRows"
        :lines="vendorBill.lines"
        :subtotal="vendorBill.subtotal"
        :apply-ppn="vendorBill.apply_ppn"
        :ppn-amount="vendorBill.ppn_amount"
        :apply-pph23="vendorBill.apply_pph23"
        :pph23-amount="vendorBill.pph23_amount"
        :total-amount="vendorBill.total_amount"
        :status="vendorBill.status"
        paid-label="DIBAYAR"
        unpaid-label="BELUM DIBAYAR"
        :paid-at="vendorBill.paid_at"
        :payment-reference="vendorBill.payment_reference"
        :notes="vendorBill.notes"
        footer-note="Voucher pembayaran mitra/vendor disetujui sesuai verifikasi biaya operasional & kontrak."
        prepared-by-label="Disetujui Finance"
        counterparty-label="Mitra / Vendor"
    />
</template>
