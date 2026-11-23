<script setup lang="ts">
import { Package, Plane, ShieldCheck, Truck, Warehouse } from '@lucide/vue';

const services = [
    {
        title: 'PENGIRIMAN REGULER (REG)',
        image: '/images/landing/svc-oms.jpg',
        alt: 'Sortir paket pengiriman reguler dan komersial Pache Logistic',
        desc: 'Layanan distribusi darat antarkota dan antarcabang dengan tarif ekonomis, dukungan pengiriman multi-koli, pelacakan AWB real-time, serta bukti terima digital.',
        meta: 'Divisor ÷ 6000 · Hemat & Terjadwal',
        icon: Package,
    },
    {
        title: 'EXPRESS UDARA PRIORITAS (EXP)',
        image: '/images/landing/svc-tms.jpg',
        alt: 'Armada distribusi ekspres prioritas antarkota Pache Logistic',
        desc: 'Pengiriman prioritas untuk paket bisnis mendesak dan dokumen penting dengan estimasi waktu tiba lebih singkat ke kota hub regional utama Indonesia.',
        meta: 'Divisor ÷ 5000 · Prioritas SLA Cepat',
        icon: Plane,
    },
    {
        title: 'KARGO BERAT & ARMADA (CRG)',
        image: '/images/landing/svc-cargo.jpg',
        alt: 'Terminal kontainer kargo berat dan angkutan antar-pulau Pache Logistic',
        desc: 'Solusi angkutan barang berukuran besar dan berat antarpulau maupun linehaul truk terdedikasi lengkap dengan dukungan penagihan kontrak B2B TOP.',
        meta: 'Divisor ÷ 4000 · Muatan Besar & B2B',
        icon: Truck,
    },
    {
        title: 'PERGUDANGAN & FULFILMENT (WMS)',
        image: '/images/landing/svc-wms.jpg',
        alt: 'Fasilitas gudang terkelola Dry dan Cold Storage Pache Logistic',
        desc: 'Layanan penyimpanan gudang Dry & Cold Storage berstandar FEFO dan FIFO lengkap dengan pencatatan stok real-time serta integrasi langsung ke jaringan pengiriman.',
        meta: 'Dry & Cold Storage · FIFO / FEFO',
        icon: Warehouse,
    },
];
</script>

<template>
    <section
        class="border-b border-[#dce5f1] bg-[#f7f9fc] py-14 md:py-16"
        id="services"
    >
        <div class="mx-auto max-w-[1200px] space-y-10 px-6 md:px-10">
            <!-- Section Header -->
            <div
                class="flex flex-col justify-between gap-3 md:flex-row md:items-end"
            >
                <div>
                    <p
                        class="text-[11px] font-bold tracking-[0.14em] text-[#2563eb] uppercase"
                    >
                        LAYANAN PENGIRIMAN &amp; LOGISTIK
                    </p>
                    <h2
                        class="mt-1.5 text-[22px] font-bold tracking-[-0.02em] text-[#18202c] uppercase"
                    >
                        PILIHAN LAYANAN TERINTEGRASI
                    </h2>
                </div>
                <p
                    class="max-w-[460px] text-[13px] leading-[1.7] text-[#596579]"
                >
                    Tersedia untuk pengiriman ritel loket maupun kontrak B2B
                    korporat di seluruh jaringan hub resmi Pache Logistic.
                </p>
            </div>

            <!-- 2x2 Customer Shipping & Warehousing Services Grid -->
            <div class="grid grid-cols-1 gap-7 md:grid-cols-2">
                <article
                    v-for="(svc, idx) in services"
                    :key="svc.title"
                    class="group grid grid-cols-1 overflow-hidden rounded-[16px] border border-[#dce5f1] bg-white shadow-[0_10px_28px_-16px_rgba(23,43,70,0.18)] transition duration-200 hover:-translate-y-1 hover:border-[#93c5fd] hover:shadow-[0_18px_36px_-18px_rgba(37,99,235,0.22)] sm:grid-cols-[0.92fr_1.08fr]"
                >
                    <div
                        class="pache-diag-media relative min-h-[200px] overflow-hidden bg-[#edf2f8]"
                    >
                        <img
                            :src="svc.image"
                            :alt="svc.alt"
                            class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.04]"
                        />
                    </div>
                    <div class="flex flex-col justify-between p-6">
                        <div>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-[#eff6ff] px-2.5 py-0.5 text-[10.5px] font-bold tracking-[0.08em] text-[#2563eb] uppercase tabular-nums"
                            >
                                <component :is="svc.icon" class="h-3 w-3" />
                                <span>0{{ idx + 1 }} · LAYANAN</span>
                            </span>
                            <h3
                                class="mt-2.5 text-[15px] font-bold tracking-[0.03em] text-[#18202c] uppercase"
                            >
                                {{ svc.title }}
                            </h3>
                            <p
                                class="mt-2 text-[13px] leading-[1.7] text-[#596579]"
                            >
                                {{ svc.desc }}
                            </p>
                        </div>
                        <div
                            class="mt-4 flex items-center justify-between border-t border-[#eef3fb] pt-3 text-[11px] font-bold tracking-[0.06em] text-[#2563eb] uppercase tabular-nums"
                        >
                            <span>{{ svc.meta }}</span>
                            <ShieldCheck class="h-3.5 w-3.5 text-[#2563eb]" />
                        </div>
                    </div>
                </article>
            </div>

            <p
                class="mx-auto max-w-[780px] text-center text-[13px] leading-[1.85] text-[#596579] italic"
            >
                Seluruh layanan pengiriman berjalan dengan kebijakan Strictly
                Non-COD — tarif transparan, bebas penagihan tunai kurir di
                tujuan, serta dukungan asuransi kargo dan bukti serah terima
                e-POD.
            </p>
        </div>
    </section>
</template>
