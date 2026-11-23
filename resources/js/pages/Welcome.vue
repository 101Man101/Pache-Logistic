<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowRight, Minus, Plus, Search } from '@lucide/vue';
import { ref } from 'vue';
import PublicFooter from '@/components/landing/PublicFooter.vue';
import PublicNavbar from '@/components/landing/PublicNavbar.vue';
import PublicPartnerMarquee from '@/components/landing/PublicPartnerMarquee.vue';
import PublicServicesSection from '@/components/landing/PublicServicesSection.vue';

interface BranchRow {
    id: number;
    code: string;
    name: string;
    type: string;
    city: string;
}

interface SampleShipmentRow {
    awb_number: string;
    origin_code: string;
    destination_code: string;
    service_type: string;
    chargeable_weight_kg: number;
    payment_method_label: string;
    payment_status_label: string;
    status: string;
    status_label: string;
}

interface ClientRow {
    code: string;
    name: string;
    type: string;
}

defineProps<{
    branches: BranchRow[];
    sampleShipments: SampleShipmentRow[];
    clients: ClientRow[];
}>();

const quickAwb = ref('');
const openValueIndex = ref<number>(0);

function submitQuickTrack(): void {
    const code = quickAwb.value.trim();
    if (!code) return;
    router.get('/tracking', { awb: code });
}

function toggleValue(index: number): void {
    openValueIndex.value = openValueIndex.value === index ? -1 : index;
}

const coreValues = [
    {
        title: 'MISI STRICTLY NON-COD',
        heading: 'Distribusi Nasional Tanpa Uang Tunai di Kurir.',
        image: '/images/landing/svc-tms.jpg',
        body: 'Pache Logistic menghilangkan risiko selisih setoran tunai pada kurir pengantar. Setiap pengiriman dibayar aman melalui Online Payment (QRIS/VA), kasir loket asal, atau fasilitas kontrak B2B Term of Payment (TOP).',
        points: [
            'Online Payment QRIS & Virtual Account',
            'Cash Lunas di Cabang Asal',
            'Kredit B2B TOP Net 7 / 14 / 30 / 45',
            'Transaksi Transparan Tanpa Tagihan Kurir',
        ],
    },
    {
        title: 'VISI PRESISI OPERASIONAL',
        heading: 'Proses Terukur Dari Timbangan Hingga Tanda Terima.',
        image: '/images/landing/svc-wms.jpg',
        body: 'Mulai dari penimbangan koli pertama di loket asal, pengelolaan stok gudang berbasis batch FEFO/FIFO, hingga konsolidasi tagihan korporat — seluruh proses tercatat rapi dan dapat dipantau secara real-time.',
        points: [
            'Kalkulasi Berat Aktual & Volumetrik Jelas',
            'Kontrol Stok Gudang Akurat Real-Time',
            'Pemrosesan Bagging Cepat & Terjadwal',
            'Penagihan B2B Rapi dengan PPN 11% & PPh 23',
        ],
    },
    {
        title: 'KEPATUHAN & PRIVASI DATA',
        heading: 'Armada Layak Jalan & Perlindungan Privasi Penerima.',
        image: '/images/landing/svc-oms.jpg',
        body: 'Armada operasional kami dipantau masa berlaku STNK, KIR, dan SIM pengemudinya secara berkala. Pada pelacakan resi publik, nomor telepon dan alamat lengkap penerima dilindungi otomatis.',
        points: [
            'Armada Terverifikasi & Kepatuhan Uji KIR',
            'Bukti Terima Digital (e-POD) & GPS',
            'Privasi Nomor HP & Alamat Terlindungi',
            'Riwayat Perjalanan Paket Real-Time',
        ],
    },
];
</script>

<template>
    <Head title="Fast Move" />

    <div class="pache-landing">
        <PublicNavbar overlay active-path="/" />

        <!-- Hero Section -->
        <section
            class="relative isolate min-h-[540px] overflow-hidden bg-[#172b46] text-white md:min-h-[600px]"
            id="top"
        >
            <img
                src="/images/landing/hero-cargo.jpg"
                alt="Terminal pelabuhan kargo dan armada logistik Pache Logistic"
                class="absolute inset-0 h-full w-full object-cover object-[75%_center]"
            />
            <!-- Directional Cool Navy Scrim for Left Copy Contrast & Unobstructed Right Ship -->
            <div
                class="absolute inset-0"
                style="
                    background:
                        linear-gradient(
                            180deg,
                            rgba(17, 24, 39, 0.62) 0%,
                            rgba(17, 24, 39, 0) 32%,
                            rgba(23, 43, 70, 0.52) 100%
                        ),
                        linear-gradient(
                            95deg,
                            rgba(17, 24, 39, 0.9) 0%,
                            rgba(23, 43, 70, 0.74) 46%,
                            rgba(23, 43, 70, 0.24) 100%
                        );
                "
            />

            <!-- Hero Copy Anchored Left Over Dark Scrim, Keeping Cargo Ship Clear on Right -->
            <div
                class="relative z-10 mx-auto grid max-w-[1200px] grid-cols-1 items-center px-6 pt-28 pb-16 md:grid-cols-12 md:px-10 md:pt-36 md:pb-24"
            >
                <div class="md:col-span-7 lg:col-span-6">
                    <p
                        class="text-[11px] font-bold tracking-[0.18em] text-[#93c5fd] uppercase"
                    >
                        PACHE LOGISTIC · PENGIRIMAN NON-COD TERPERCAYA
                    </p>
                    <h1
                        class="mt-3 text-[clamp(32px,3.8vw,48px)] leading-[1.14] font-bold tracking-[-0.03em] text-white uppercase"
                    >
                        KAMI JARINGAN |<br />
                        PENGIRIMAN TERPADU
                    </h1>
                    <p
                        class="mt-4 max-w-[500px] text-[15px] leading-[1.7] text-[#e2ebf5]"
                    >
                        Layanan logistik dan kargo terpadu untuk pengiriman
                        ritel maupun kontrak B2B antarkota — terjadwal,
                        transparan, dan bebas penagihan uang tunai oleh kurir di
                        tujuan.
                    </p>

                    <div class="mt-7 flex flex-wrap items-center gap-3.5">
                        <a
                            href="#services"
                            class="group inline-flex min-h-[48px] items-center gap-3 rounded-[10px] bg-[#2563eb] px-6 text-[12px] font-bold tracking-[0.08em] text-white uppercase no-underline shadow-[0_12px_26px_-8px_rgba(37,99,235,0.65)] transition duration-200 hover:-translate-y-0.5 hover:bg-[#1d4ed8]"
                        >
                            <span>Lihat Layanan</span>
                            <ArrowRight
                                class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1"
                            />
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Deep Navy Intro Statement Band + Quick AWB Lookup (#172b46) -->
        <section class="bg-[#172b46] text-white" id="cek-resi">
            <div class="mx-auto max-w-[1200px] px-6 py-11 md:px-10">
                <div
                    class="grid grid-cols-1 items-center gap-6 md:grid-cols-[0.36fr_0.64fr] md:gap-10"
                >
                    <div
                        class="md:border-r md:border-[#2c476e] md:pr-8 md:text-right"
                    >
                        <h2
                            class="text-[20px] leading-[1.35] font-bold tracking-[0.03em] text-white uppercase"
                        >
                            KAMI HADIRKAN<br />
                            LAYANAN TERPADU
                        </h2>
                        <p
                            class="mt-2 text-[11.5px] font-semibold tracking-[0.08em] text-[#93c5fd] uppercase"
                        >
                            Reguler · Express · Kargo · Gudang
                        </p>
                    </div>

                    <div class="space-y-4">
                        <p
                            class="text-[14px] leading-[1.8] text-[#cbd5e1] italic"
                        >
                            Seluruh pengiriman Pache Logistic berjalan di atas
                            kebijakan Strictly Non-COD — mendukung pembayaran
                            QRIS/Virtual Account, tunai di loket asal, serta
                            termin kontrak B2B secara aman dan transparan.
                        </p>

                        <!-- Integrated Pill-Bar Public AWB Check -->
                        <form
                            @submit.prevent="submitQuickTrack"
                            class="flex flex-col gap-1.5 rounded-[12px] border border-[#2f4d78] bg-[#0f1f36]/90 p-1.5 shadow-[inset_0_1px_2px_rgba(0,0,0,0.28),0_10px_24px_-12px_rgba(0,0,0,0.45)] transition focus-within:border-[#60a5fa] focus-within:ring-2 focus-within:ring-[#60a5fa]/25 sm:flex-row sm:items-center"
                        >
                            <div class="flex flex-1 items-center gap-2.5 pl-3">
                                <Search
                                    class="h-4 w-4 shrink-0 text-[#93c5fd]"
                                />
                                <input
                                    v-model="quickAwb"
                                    type="text"
                                    placeholder="Masukkan nomor resi / AWB"
                                    aria-label="Nomor Resi AWB"
                                    class="h-[38px] w-full border-0 bg-transparent text-[13.5px] font-medium text-white tabular-nums placeholder-[#94a3b8] outline-none"
                                />
                            </div>
                            <button
                                type="submit"
                                class="inline-flex h-[40px] shrink-0 items-center justify-center gap-2 rounded-[8px] bg-[#2563eb] px-5 text-[12px] font-bold tracking-[0.08em] text-white uppercase shadow-[0_4px_12px_-3px_rgba(37,99,235,0.6)] transition hover:bg-[#1d4ed8]"
                            >
                                <span>Cek Resi</span>
                                <ArrowRight class="h-3.5 w-3.5" />
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- Shipping & Warehousing Services -->
        <PublicServicesSection />

        <!-- Asymmetric Split: Core Values Accordion (Left) + Locations/Hubs (Right) -->
        <section
            class="border-b border-[#dce5f1] bg-white py-14 md:py-16"
            id="values"
        >
            <div
                class="mx-auto grid max-w-[1200px] grid-cols-1 gap-12 px-6 md:px-10 lg:grid-cols-[1.35fr_0.9fr]"
            >
                <!-- Left: Core Values Accordion -->
                <div>
                    <h2
                        class="text-[19px] font-bold tracking-[0.04em] text-[#18202c] uppercase"
                    >
                        NILAI &amp; KEBIJAKAN INTI KAMI
                    </h2>

                    <div
                        class="mt-6 divide-y divide-[#dce5f1] overflow-hidden rounded-[14px] border border-[#dce5f1] shadow-[0_8px_24px_-16px_rgba(23,43,70,0.16)]"
                    >
                        <div
                            v-for="(val, idx) in coreValues"
                            :key="val.title"
                            class="bg-[#f7f9fc]/70"
                        >
                            <button
                                type="button"
                                @click="toggleValue(idx)"
                                class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left transition hover:bg-[#eff6ff]"
                                :aria-expanded="openValueIndex === idx"
                            >
                                <span
                                    class="text-[12.5px] font-bold tracking-[0.06em] text-[#18202c] uppercase"
                                >
                                    {{ val.title }}
                                </span>
                                <span
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full transition"
                                    :class="
                                        openValueIndex === idx
                                            ? 'bg-[#2563eb] text-white'
                                            : 'bg-[#edf2f8] text-[#596579]'
                                    "
                                >
                                    <Minus
                                        v-if="openValueIndex === idx"
                                        class="h-3.5 w-3.5"
                                    />
                                    <Plus v-else class="h-3.5 w-3.5" />
                                </span>
                            </button>

                            <div
                                v-show="openValueIndex === idx"
                                class="grid grid-cols-1 gap-5 border-t border-[#dce5f1] bg-white p-5 sm:grid-cols-[190px_1fr]"
                            >
                                <img
                                    :src="val.image"
                                    :alt="val.heading"
                                    class="h-[140px] w-full rounded-[10px] border border-[#dce5f1] object-cover sm:h-full"
                                />
                                <div>
                                    <h3
                                        class="text-[15px] font-bold text-[#18202c]"
                                    >
                                        {{ val.heading }}
                                    </h3>
                                    <p
                                        class="mt-2 text-[12.5px] leading-[1.75] text-[#596579]"
                                    >
                                        {{ val.body }}
                                    </p>
                                    <ul
                                        class="mt-3 grid grid-cols-1 gap-x-4 gap-y-1.5 p-0 text-[12px] font-semibold text-[#172b46] sm:grid-cols-2"
                                    >
                                        <li
                                            v-for="pt in val.points"
                                            :key="pt"
                                            class="flex list-none items-center gap-1.5"
                                        >
                                            <ArrowRight
                                                class="h-3 w-3 shrink-0 text-[#2563eb]"
                                            />
                                            <span>{{ pt }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Our Locations / Branch Network -->
                <div>
                    <h2
                        class="text-[19px] font-bold tracking-[0.04em] text-[#18202c] uppercase"
                    >
                        LOKASI &amp; CABANG KAMI
                    </h2>

                    <div class="mt-6">
                        <img
                            src="/images/landing/hub-office.jpg"
                            alt="Pusat operasional dan koordinasi cabang Pache Logistic"
                            class="h-[195px] w-full rounded-[14px] border border-[#dce5f1] object-cover shadow-[0_8px_22px_-14px_rgba(23,43,70,0.18)]"
                        />
                        <p
                            class="mt-4 text-[13px] leading-[1.75] text-[#596579]"
                        >
                            Operasional Pache Logistic mencakup jaringan Head
                            Office, Regional Hub, dan Sorting Center aktif yang
                            terhubung langsung dalam satu buku besar rute.
                        </p>

                        <div
                            class="mt-3 grid grid-cols-2 gap-x-4 gap-y-1.5 border-t border-[#dce5f1] pt-3 text-[12px] tabular-nums"
                        >
                            <div
                                v-for="b in branches"
                                :key="b.id"
                                class="flex items-center gap-2"
                            >
                                <span class="font-bold text-[#2563eb]">{{
                                    b.code
                                }}</span>
                                <span class="truncate text-[#18202c]">{{
                                    b.city
                                }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Clients / Mitra Marquee Strip (Inspired by Clean Grayscale-to-Color Ticker) -->
        <PublicPartnerMarquee :clients="clients" />

        <PublicFooter active-path="/" />
    </div>
</template>
