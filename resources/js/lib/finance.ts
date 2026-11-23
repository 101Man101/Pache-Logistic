export interface CompanyProfile {
    name: string;
    tagline: string;
    address: string;
    phone: string;
    email: string;
    npwp: string;
    bank_account: string;
}

export interface DocumentLineItem {
    id: number;
    shipment_id: number | null;
    awb_number: string | null;
    route?: string | null;
    service_type?: string | null;
    chargeable_weight_kg?: number | null;
    description: string;
    quantity: number;
    unit: string;
    unit_price: number;
    amount: number;
}

export interface InvoiceDetailPayload {
    id: number;
    invoice_number: string;
    invoice_type: string;
    invoice_type_label: string;
    period: string;
    period_start: string;
    period_end: string;
    due_date: string;
    days_overdue: number;
    aging_bucket: string;
    apply_ppn: boolean;
    apply_pph23: boolean;
    subtotal: number;
    ppn_amount: number;
    pph23_amount: number;
    total_amount: number;
    notes: string | null;
    status: string;
    paid_at: string | null;
    payment_reference: string | null;
    customer: {
        id: number;
        code: string;
        name: string;
        npwp: string | null;
        phone: string;
        email: string | null;
        address: string;
        is_top_enabled: boolean;
        top_days: number;
    };
    lines: DocumentLineItem[];
}

export interface VendorBillDetailPayload {
    id: number;
    bill_number: string;
    vendor_invoice_no: string | null;
    bill_date: string;
    due_date: string;
    days_overdue: number;
    aging_bucket: string;
    apply_ppn: boolean;
    apply_pph23: boolean;
    subtotal: number;
    ppn_amount: number;
    pph23_amount: number;
    total_amount: number;
    notes: string | null;
    status: string;
    paid_at: string | null;
    payment_reference: string | null;
    branch: {
        code: string;
        name: string;
        city: string;
    } | null;
    manifest: {
        id: number;
        manifest_number: string;
        route: string;
    } | null;
    vendor: {
        id: number;
        code: string;
        name: string;
        type_label: string;
        contact_person: string | null;
        phone: string;
        email: string | null;
        address: string | null;
        payment_terms_days: number;
    };
    lines: DocumentLineItem[];
}

export interface DraftLineRow {
    shipment_id?: number | null;
    description: string;
    quantity: number;
    unit: string;
    unit_price: number;
}

export function calculateDraftTotals(
    lines: DraftLineRow[],
    applyPpn: boolean,
    applyPph23: boolean,
): {
    subtotal: number;
    ppn: number;
    pph23: number;
    total: number;
} {
    const subtotal = lines.reduce(
        (sum, line) =>
            sum + Number(line.quantity || 0) * Number(line.unit_price || 0),
        0,
    );
    const ppn = applyPpn ? Math.round(subtotal * 0.11) : 0;
    const pph23 = applyPph23 ? Math.round(subtotal * 0.02) : 0;
    const total = subtotal + ppn - pph23;

    return { subtotal, ppn, pph23, total };
}

export function addDaysIsoDate(days: number): string {
    const d = new Date();
    d.setDate(d.getDate() + Math.max(0, days));
    return d.toISOString().slice(0, 10);
}

export function todayIsoDate(): string {
    return new Date().toISOString().slice(0, 10);
}
