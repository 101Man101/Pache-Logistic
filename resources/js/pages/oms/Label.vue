<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Printer } from '@lucide/vue';
import { computed } from 'vue';
import PacheLogo from '@/components/PacheLogo.vue';

const props = defineProps<{
    shipment: {
        awb_number: string;
        reference_no: string | null;
        service_type: string;
        origin_code: string;
        origin_city: string;
        dest_code: string;
        dest_city: string;
        shipper_name: string;
        shipper_phone: string;
        shipper_address: string;
        consignee_name: string;
        consignee_phone: string;
        consignee_address: string;
        chargeable_weight_kg: number;
        colli_count: number;
        payment_method: string;
        payment_method_label: string;
        payment_status: string;
        payment_status_label: string;
        created_at: string;
    };
}>();

// Deterministic vector barcode bars from AWB characters (100% offline SVG)
const barcodeBars = computed(() => {
    const bars: Array<{ x: number; w: number }> = [];
    let x = 8;
    const str = props.shipment.awb_number;
    for (let i = 0; i < str.length; i++) {
        const code = str.charCodeAt(i);
        const widths = [(code % 3) + 1.5, ((code >> 2) % 2) + 1.2, ((code >> 4) % 3) + 1.4];
        for (const w of widths) {
            bars.push({ x, w });
            x += w + (((code + i) % 2) + 1.6);
        }
    }
    return { bars, totalWidth: Math.ceil(x + 8) };
});

function printLabel(): void {
    window.print();
}
</script>

<template>
    <Head :title="`Label A6 — ${shipment.awb_number}`" />

    <div class="min-h-screen bg-[#edf3fa] px-4 py-8 text-[#111111]">
        <div class="no-print mx-auto mb-5 flex max-w-[420px] items-center justify-between">
            <Link :href="`/oms/shipments/${shipment.awb_number}`" class="pache-btn-outline">
                <ArrowLeft class="h-4 w-4" />
                <span>Kembali</span>
            </Link>
            <button type="button" @click="printLabel" class="pache-btn-primary">
                <Printer class="h-4 w-4" />
                <span>Cetak Thermal A6</span>
            </button>
        </div>

        <!-- 100mm x 150mm Thermal Label Container -->
        <div class="mx-auto w-[390px] overflow-hidden rounded-[8px] border-2 border-[#111111] bg-white shadow-lg print:m-0 print:w-[100mm] print:rounded-none print:shadow-none">
            <!-- Header -->
            <div class="flex items-center justify-between border-b-2 border-[#111111] px-4 py-3">
                <PacheLogo compact />
                <div class="text-right">
                    <span class="inline-block bg-[#111111] px-2.5 py-1 text-[14px] font-black tracking-wider text-white">
                        {{ shipment.service_type }}
                    </span>
                    <p class="mt-0.5 text-[10px] font-semibold text-[#596579] tabular-nums">
                        {{ shipment.created_at }}
                    </p>
                </div>
            </div>

            <!-- Big Destination Hub Sort Code -->
            <div class="grid grid-cols-[1fr_auto] items-center border-b-2 border-[#111111] px-4 py-3">
                <div>
                    <p class="text-[10px] font-bold tracking-[0.12em] uppercase text-[#596579]">
                        SORTING HUB TUJUAN
                    </p>
                    <p class="text-[36px] leading-none font-black tracking-[-0.03em] text-[#111111]">
                        {{ shipment.dest_code }}
                    </p>
                    <p class="mt-0.5 text-[12px] font-bold uppercase">{{ shipment.dest_city }}</p>
                </div>
                <div class="border-l-2 border-[#111111] pl-4 text-right tabular-nums">
                    <p class="text-[10px] font-bold uppercase text-[#596579]">ASAL</p>
                    <p class="text-[18px] font-black">{{ shipment.origin_code }}</p>
                    <p class="mt-1 text-[12px] font-bold">
                        {{ shipment.chargeable_weight_kg }} kg · {{ shipment.colli_count }} Koli
                    </p>
                </div>
            </div>

            <!-- Vector Barcode -->
            <div class="border-b-2 border-[#111111] px-4 py-3 text-center">
                <svg
                    :viewBox="`0 0 ${barcodeBars.totalWidth} 56`"
                    class="mx-auto h-[54px] w-full"
                    preserveAspectRatio="none"
                    aria-label="Barcode AWB"
                >
                    <rect
                        v-for="(bar, i) in barcodeBars.bars"
                        :key="i"
                        :x="bar.x"
                        y="2"
                        :width="bar.w"
                        height="52"
                        fill="#111111"
                    />
                </svg>
                <p class="mt-1.5 text-[16px] font-black tracking-[0.08em] tabular-nums">
                    {{ shipment.awb_number }}
                </p>
                <p v-if="shipment.reference_no" class="text-[11px] font-semibold text-[#596579]">
                    REF: {{ shipment.reference_no }}
                </p>
            </div>

            <!-- STRICTLY NON-COD BANNER (FR-OMS-03) -->
            <div class="border-b-2 border-[#111111] bg-[#111111] px-4 py-2.5 text-center text-white">
                <p class="text-[13px] font-black tracking-[0.08em] uppercase">
                    NON-COD · {{ shipment.payment_status === 'paid' ? `LUNAS (${shipment.payment_method_label})` : shipment.payment_method_label }}
                </p>
                <p class="text-[10px] font-semibold tracking-wide text-[#cbd5e1]">
                    KURIR DILARANG MENAGIH UANG TUNAI KEPADA PENERIMA
                </p>
            </div>

            <!-- Consignee & Shipper -->
            <div class="divide-y divide-[#111111] text-[12px]">
                <div class="px-4 py-3">
                    <p class="text-[10px] font-bold tracking-[0.1em] uppercase text-[#596579]">PENERIMA (CONSIGNEE)</p>
                    <p class="mt-0.5 text-[14px] font-black">{{ shipment.consignee_name }}</p>
                    <p class="font-bold tabular-nums">{{ shipment.consignee_phone }}</p>
                    <p class="mt-0.5 leading-snug">{{ shipment.consignee_address }}</p>
                </div>
                <div class="px-4 py-3">
                    <p class="text-[10px] font-bold tracking-[0.1em] uppercase text-[#596579]">PENGIRIM (SHIPPER)</p>
                    <p class="mt-0.5 font-bold">{{ shipment.shipper_name }} ({{ shipment.shipper_phone }})</p>
                    <p class="text-[11px] leading-snug text-[#596579]">{{ shipment.shipper_address }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
