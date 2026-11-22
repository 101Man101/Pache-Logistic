export function formatIdr(amount: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount || 0);
}

export function formatWeight(kg: number): string {
    return `${Number(kg || 0).toFixed(2)} kg`;
}

export function paymentBadgeClass(method: string): string {
    switch (method) {
        case 'online':
            return 'bg-[#eff6ff] text-[#1d4ed8] border-[#bfdbfe] dark:bg-[#1e3a8a]/30 dark:text-[#93c5fd] dark:border-[#1d4ed8]/40';
        case 'cash':
            return 'bg-[#ecfdf3] text-[#15803d] border-[#bbf7d0] dark:bg-[#14532d]/30 dark:text-[#86efac] dark:border-[#15803d]/40';
        case 'top':
            return 'bg-[#f8eddf] text-[#92400e] border-[#e7cfa8] dark:bg-[#78350f]/30 dark:text-[#fcd34d] dark:border-[#b45309]/40';
        default:
            return 'bg-[#f3f7fc] text-[#64748b] border-[#e2ebf5]';
    }
}

export function paymentStatusBadgeClass(status: string): string {
    switch (status) {
        case 'paid':
            return 'bg-[#ecfdf3] text-[#15803d] border-[#bbf7d0] dark:bg-[#14532d]/30 dark:text-[#86efac] dark:border-[#15803d]/40';
        case 'billed':
            return 'bg-[#eff6ff] text-[#1d4ed8] border-[#bfdbfe] dark:bg-[#1e3a8a]/30 dark:text-[#93c5fd] dark:border-[#1d4ed8]/40';
        case 'pending':
            return 'bg-[#fef2f2] text-[#991b1b] border-[#fecaca] dark:bg-[#7f1d1d]/30 dark:text-[#fca5a5] dark:border-[#dc2626]/40';
        default:
            return 'bg-[#f3f7fc] text-[#64748b] border-[#e2ebf5]';
    }
}

export function shipmentStatusBadgeClass(status: string): string {
    switch (status) {
        case 'delivered':
            return 'bg-[#ecfdf3] text-[#15803d] border-[#bbf7d0] dark:bg-[#14532d]/30 dark:text-[#86efac] dark:border-[#15803d]/40';
        case 'in_transit':
        case 'out_for_delivery':
            return 'bg-[#eff6ff] text-[#1d4ed8] border-[#bfdbfe] dark:bg-[#1e3a8a]/30 dark:text-[#93c5fd] dark:border-[#1d4ed8]/40';
        case 'origin_hub':
        case 'destination_hub':
        case 'picked_up':
            return 'bg-[#f8eddf] text-[#92400e] border-[#e7cfa8] dark:bg-[#78350f]/30 dark:text-[#fcd34d] dark:border-[#b45309]/40';
        case 'cancelled':
            return 'bg-[#fef2f2] text-[#991b1b] border-[#fecaca] dark:bg-[#7f1d1d]/30 dark:text-[#fca5a5] dark:border-[#dc2626]/40';
        default:
            return 'bg-[#f3f7fc] text-[#172b46] border-[#e2ebf5] dark:bg-[#1a1a1a] dark:text-[#e4e4e7] dark:border-[#303030]';
    }
}
