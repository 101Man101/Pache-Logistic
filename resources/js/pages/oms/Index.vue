<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Copy, PackagePlus, Printer, Search } from '@lucide/vue';
import { ref } from 'vue';
import {
    formatIdr,
    formatWeight,
    paymentBadgeClass,
    paymentStatusBadgeClass,
    shipmentStatusBadgeClass,
} from '@/lib/format';

interface ShipmentRow {
    id: number;
    awb_number: string;
    reference_no: string | null;
    customer_name: string;
    origin: string;
    destination: string;
    consignee_name: string;
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

const props = defineProps<{
    filters: {
        search: string;
        status: string;
        payment_method: string;
        payment_status: string;
    };
    shipments: {
        data: ShipmentRow[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        total: number;
    };
}>();

const search = ref(props.filters.search);
const status = ref(props.filters.status);
const paymentMethod = ref(props.filters.payment_method);
const paymentStatus = ref(props.filters.payment_status);
const copiedAwb = ref<string | null>(null);

function applyFilter(): void {
    router.get(
        '/oms/shipments',
        {
            search: search.value || undefined,
            status: status.value || undefined,
            payment_method: paymentMethod.value || undefined,
            payment_status: paymentStatus.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}

function copyAwb(awb: string): void {
    navigator.clipboard?.writeText(awb);
    copiedAwb.value = awb;
    setTimeout(() => {
        if (copiedAwb.value === awb) copiedAwb.value = null;
    }, 1500);
}
</script>

<template>
    <Head title="Daftar Pengiriman (OMS)" />

    <div class="space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-[10.5px] font-bold tracking-[0.14em] text-[var(--brand-blue)] uppercase">
                    ORDER MANAGEMENT SYSTEM (OMS)
                </p>
                <h1 class="mt-1 text-[23px] font-[650] tracking-[-0.03em] text-[var(--ink)]">
                    Daftar Resi Pengiriman ({{ shipments.total }})
                </h1>
            </div>
            <Link href="/oms/shipments/create" class="pache-btn-primary">
                <PackagePlus class="h-4 w-4" />
                <span>Booking Resi Baru</span>
            </Link>
        </div>

        <!-- Filter Bar -->
        <form
            @submit.prevent="applyFilter"
            class="pache-panel grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1fr_auto]"
        >
            <div class="relative">
                <Search class="pointer-events-none absolute top-3 left-3 h-4 w-4 text-[var(--muted)]" />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari AWB, Referensi, Penerima, HP..."
                    class="pache-input pl-9"
                />
            </div>

            <select v-model="paymentMethod" @change="applyFilter" class="pache-input">
                <option value="">Semua Kanal Non-COD</option>
                <option value="online">Online Payment (QRIS/VA)</option>
                <option value="cash">Cash (Loket Asal)</option>
                <option value="top">B2B Credit (TOP)</option>
            </select>

            <select v-model="paymentStatus" @change="applyFilter" class="pache-input">
                <option value="">Semua Status Bayar</option>
                <option value="paid">Lunas (PAID)</option>
                <option value="billed">Kredit TOP (BILLED)</option>
                <option value="pending">Pending (Ditahan BR-08)</option>
            </select>

            <select v-model="status" @change="applyFilter" class="pache-input">
                <option value="">Semua Status Resi</option>
                <option value="booked">Booked</option>
                <option value="origin_hub">Origin Hub</option>
                <option value="in_transit">In Transit</option>
                <option value="destination_hub">Destination Hub</option>
                <option value="out_for_delivery">Out for Delivery</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
            </select>

            <button type="submit" class="pache-btn-primary">
                Filter
            </button>
        </form>

        <!-- Data Table -->
        <section class="pache-panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="pache-table">
                    <thead>
                        <tr>
                            <th>Nomor AWB</th>
                            <th>Pelanggan &amp; Penerima</th>
                            <th>Rute</th>
                            <th>Berat Tagih</th>
                            <th>Total Ongkir</th>
                            <th>Pembayaran (Non-COD)</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="s in shipments.data" :key="s.id">
                            <td>
                                <div class="flex items-center gap-1.5">
                                    <Link :href="`/oms/shipments/${s.awb_number}`" class="font-bold text-[var(--brand-blue)] no-underline tabular-nums hover:underline">
                                        {{ s.awb_number }}
                                    </Link>
                                    <button
                                        type="button"
                                        @click="copyAwb(s.awb_number)"
                                        class="rounded p-1 text-[var(--muted)] hover:bg-[var(--surface-soft)] hover:text-[var(--ink)]"
                                        title="Salin AWB"
                                    >
                                        <Copy class="h-3.5 w-3.5" />
                                    </button>
                                    <span v-if="copiedAwb === s.awb_number" class="text-[11px] font-semibold text-[#15803d]">
                                        Disalin
                                    </span>
                                </div>
                                <p v-if="s.reference_no" class="text-[11.5px] text-[var(--muted)]">
                                    Ref: {{ s.reference_no }}
                                </p>
                            </td>
                            <td>
                                <strong class="block text-[13.5px]">{{ s.customer_name }}</strong>
                                <span class="text-[12px] text-[var(--muted)]">Ke: {{ s.consignee_name }}</span>
                            </td>
                            <td>
                                <span class="block font-semibold">{{ s.origin }} → {{ s.destination }}</span>
                                <span class="text-[11.5px] text-[var(--muted)]">Layanan {{ s.service_type }}</span>
                            </td>
                            <td class="tabular-nums">{{ formatWeight(s.chargeable_weight_kg) }}</td>
                            <td class="font-bold tabular-nums">{{ formatIdr(s.total_amount) }}</td>
                            <td>
                                <div class="flex flex-col items-start gap-1">
                                    <span class="rounded-[6px] border px-2 py-0.5 text-[11px] font-bold" :class="paymentBadgeClass(s.payment_method)">
                                        {{ s.payment_method_label }}
                                    </span>
                                    <span class="rounded-[6px] border px-2 py-0.5 text-[10.5px] font-semibold" :class="paymentStatusBadgeClass(s.payment_status)">
                                        {{ s.payment_status_label }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="rounded-[6px] border px-2 py-0.5 text-[11px] font-bold" :class="shipmentStatusBadgeClass(s.status)">
                                    {{ s.status_label }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <Link :href="`/oms/shipments/${s.awb_number}`" class="pache-btn-outline min-h-[32px] px-2.5 text-[12px]">
                                        Detail
                                    </Link>
                                    <Link :href="`/oms/shipments/${s.awb_number}/label`" class="pache-btn-outline min-h-[32px] px-2.5 text-[12px]" title="Cetak Label Termal A6">
                                        <Printer class="h-3.5 w-3.5" />
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="shipments.data.length === 0">
                            <td colspan="8" class="py-10 text-center text-[var(--muted)]">
                                Belum ada resi yang cocok dengan filter pencarian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="shipments.links.length > 3" class="flex flex-wrap items-center justify-between gap-2 border-t border-[var(--hairline)] px-4 py-3 text-[12.5px]">
                <span class="text-[var(--muted)] tabular-nums">Total {{ shipments.total }} pengiriman</span>
                <div class="flex flex-wrap gap-1">
                    <template v-for="(link, idx) in shipments.links" :key="idx">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="rounded-lg border px-3 py-1.5 font-semibold no-underline"
                            :class="
                                link.active
                                    ? 'border-[var(--brand-blue)] bg-[var(--brand-blue)] text-white'
                                    : 'border-[var(--hairline)] bg-[var(--surface)] text-[var(--ink)] hover:bg-[var(--surface-pink)]'
                            "
                        >
                            {{ link.label.replace(/&laquo;|&raquo;/g, '').trim() || (idx === 0 ? 'Sebelumnya' : 'Berikutnya') }}
                        </Link>
                    </template>
                </div>
            </div>
        </section>
    </div>
</template>
