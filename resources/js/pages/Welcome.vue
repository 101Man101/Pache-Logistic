<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowRight,
    Clock,
    Mail,
    MapPin,
    Menu,
    Minus,
    Phone,
    Plus,
    Search,
    X,
} from '@lucide/vue';
import { ref } from 'vue';
import PacheLogo from '@/components/PacheLogo.vue';

interface BranchRow {
    id: number;
    code: string;
    name: string;
    type: string;
    city: string;
}

interface TariffRow {
    id: number;
    origin_code: string;
    origin_city: string;
    destination_code: string;
    destination_city: string;
    service_type: string;
    volumetric_divisor: number;
    min_weight_kg: number;
    rate_per_kg: number;
    sla_days: number;
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

const props = defineProps<{
    stats: {
        branches: number;
        routes: number;
        shipments: number;
    };
    sampleAwbs: string[];
    branches: BranchRow[];
    tariffs: TariffRow[];
    sampleShipments: SampleShipmentRow[];
    clients: ClientRow[];
}>();

const mobileMenuOpen = ref(false);
const quickAwb = ref(props.sampleAwbs[0] ?? 'PCH-JKT-261121-00001');
const openValueIndex = ref<number>(0);

function submitQuickTrack(): void {
    const code = quickAwb.value.trim();
    if (!code) return;
    router.get('/tracking', { awb: code });
}

function toggleValue(index: number): void {
    openValueIndex.value = openValueIndex.value === index ? -1 : index;
}

const services = [
    {
        title: 'ORDER & WAYBILL (OMS)',
        image: '/images/landing/svc-oms.jpg',
        alt: 'Sortir paket dan pemindaian barcode pada layanan OMS Pache Logistic',
        desc: 'Booking pengiriman multi-koli dengan kalkulasi otomatis berat aktual vs volumetrik (BR-01), generator nomor AWB atomik, serta cetak label termal A6 bertanda Strictly Non-COD.',
        meta: 'Divisor 6000 / 5000 / 4000',
    },
    {
        title: 'GUDANG & LEDGER (WMS)',
        image: '/images/landing/svc-wms.jpg',
        alt: 'Rak penyimpanan gudang dan staf logistik memeriksa stok WMS Pache Logistic',
        desc: 'Topologi gudang Dry dan Cold Storage hingga level bin dengan aturan alokasi FIFO/FEFO. Setiap mutasi stok dicatat pada buku besar append-only (BR-04) agar saldo tidak pernah minus.',
        meta: 'Immutable Inventory Ledger',
    },
    {
        title: 'KARGO & MANIFEST (TMS)',
        image: '/images/landing/svc-cargo.jpg',
        alt: 'Terminal kontainer kargo antar-pulau dan linehaul Pache Logistic',
        desc: 'Konsolidasi paket ke manifest linehaul lewat scan bagging cepat. Non-COD Dispatch Gate (BR-08) otomatis menahan paket belum lunas sebelum masuk ke armada.',
        meta: 'Blokir Resi Pending Otomatis',
    },
    {
        title: 'ARMADA & FINANCE TOP',
        image: '/images/landing/svc-tms.jpg',
        alt: 'Armada truk kargo Pache Logistic di jalur distribusi ekspres',
        desc: 'Validasi kepatuhan STNK, Uji KIR, dan SIM supir (BR-06), bukti serah terima e-POD wajib koordinat GPS, serta auto-invoicing B2B TOP lengkap dengan perhitungan PPN 11% dan PPh 23.',
        meta: 'QRIS/VA · Cash Loket · B2B TOP',
    },
];

const coreValues = [
    {
        title: 'MISI STRICTLY NON-COD',
        heading: 'Distribusi Nasional Tanpa Uang Tunai di Kurir.',
        image: '/images/landing/svc-tms.jpg',
        body: 'Pache Logistic menghilangkan seluruh risiko kebocoran setoran tunai pada kurir last-mile. Setiap resi wajib lunas via Online Payment (QRIS/VA), Cash di loket asal, atau lolos pagu kredit B2B Term of Payment (TOP).',
        points: [
            'Online Payment QRIS & Virtual Account',
            'Cash Lunas di Cabang Asal',
            'Kredit B2B TOP Net 7 / 14 / 30 / 45',
            'Nol Selisih Rekonsiliasi Kas',
        ],
    },
    {
        title: 'VISI PRESISI OPERASIONAL',
        heading: 'Satu Data Terkunci Dari Timbangan Hingga Faktur Pajak.',
        image: '/images/landing/svc-wms.jpg',
        body: 'Dari penimbangan koli pertama di loket asal, penyimpanan bin gudang berbasis batch FEFO/FIFO, hingga konsolidasi invoice bulanan klien korporat — seluruh modul berbagi satu database PostgreSQL transaksional.',
        points: [
            'Chargeable Weight Transparan (BR-01)',
            'Buku Besar Stok Anti-Minus (BR-04)',
            'Scan Bagging Barcode < 150 ms',
            'Auto-Invoicing PPN 11% & PPh 23 2%',
        ],
    },
    {
        title: 'KEPATUHAN & PRIVASI DATA',
        heading: 'Armada Layak Jalan & Penyamaran PII Pelacakan Publik.',
        image: '/images/landing/svc-oms.jpg',
        body: 'Sistem otomatis menolak penugasan kendaraan dengan STNK atau KIR kedaluwarsa (BR-06). Di halaman cek resi publik, identitas penerima disamarkan hingga 4 digit terakhir nomor HP terverifikasi.',
        points: [
            'Peringatan STNK/KIR/SIM H-30',
            'e-POD Wajib Nama, Relasi & GPS',
            'PII Masking Cek Resi Publik',
            'Audit Trail Financial Ledger',
        ],
    },
];

function clientMonogram(name: string): string {
    return name
        .replace(/^(PT|CV)\s+/i, '')
        .split(/\s+/)
        .slice(0, 2)
        .map((w) => w[0]?.toUpperCase() ?? '')
        .join('');
}
</script>

<template>
    <Head title="Fast Move" />

    <div class="pache-landing">
        <!-- Hero Section with Overlaid Transparent Header (Matching Reference Structure, Pache Colors) -->
        <section
            class="relative isolate min-h-[540px] overflow-hidden bg-[#172b46] text-white md:min-h-[600px]"
            id="top"
        >
            <img
                src="/images/landing/hero-cargo.jpg"
                alt="Terminal pelabuhan kargo dan armada logistik Pache Logistic"
                class="absolute inset-0 h-full w-full object-cover object-center"
            />
            <!-- Cool Navy & Dark Gradient Scrim for WCAG Contrast -->
            <div
                class="absolute inset-0"
                style="
                    background: linear-gradient(
                        180deg,
                        rgba(17, 24, 39, 0.72) 0%,
                        rgba(23, 43, 70, 0.45) 34%,
                        rgba(23, 43, 70, 0.78) 100%
                    );
                "
            />

            <!-- Overlaid Top Header -->
            <header class="relative z-20 border-b border-white/15">
                <div
                    class="mx-auto flex max-w-[1200px] items-center justify-between gap-6 px-6 py-4 md:px-10 md:py-5"
                >
                    <Link
                        href="/"
                        class="no-underline"
                        aria-label="Beranda Pache Logistic"
                    >
                        <PacheLogo :inverted="true" />
                    </Link>

                    <nav
                        class="hidden items-center gap-7 md:flex"
                        aria-label="Navigasi utama"
                    >
                        <a
                            href="#top"
                            class="text-[11.5px] font-bold tracking-[0.1em] text-white uppercase no-underline transition hover:text-[#93c5fd]"
                        >
                            Beranda
                        </a>
                        <a
                            href="#services"
                            class="text-[11.5px] font-bold tracking-[0.1em] text-white/85 uppercase no-underline transition hover:text-white"
                        >
                            Layanan
                        </a>
                        <a
                            href="#values"
                            class="text-[11.5px] font-bold tracking-[0.1em] text-white/85 uppercase no-underline transition hover:text-white"
                        >
                            Nilai &amp; Cabang
                        </a>
                        <a
                            href="#clients"
                            class="text-[11.5px] font-bold tracking-[0.1em] text-white/85 uppercase no-underline transition hover:text-white"
                        >
                            Mitra
                        </a>
                        <Link
                            href="/tracking"
                            class="text-[11.5px] font-bold tracking-[0.1em] text-white/85 uppercase no-underline transition hover:text-white"
                        >
                            Cek Resi
                        </Link>
                        <Link
                            href="/login"
                            class="rounded-[8px] border border-white/25 bg-[#2563eb] px-4 py-2 text-[11.5px] font-bold tracking-[0.1em] text-white uppercase no-underline shadow-[0_4px_14px_-4px_rgba(37,99,235,0.65)] transition hover:-translate-y-px hover:bg-[#1d4ed8]"
                        >
                            Masuk ERP
                        </Link>
                    </nav>

                    <button
                        type="button"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="flex h-10 w-10 items-center justify-center rounded-[8px] border border-white/30 text-white hover:bg-white/10 md:hidden"
                        aria-label="Toggle navigasi"
                    >
                        <X v-if="mobileMenuOpen" class="h-5 w-5" />
                        <Menu v-else class="h-5 w-5" />
                    </button>
                </div>

                <div
                    v-if="mobileMenuOpen"
                    class="border-t border-white/15 bg-[#172b46]/95 px-6 py-4 md:hidden"
                >
                    <div class="flex flex-col gap-3">
                        <a
                            href="#services"
                            class="text-[13px] font-semibold text-white uppercase no-underline"
                            @click="mobileMenuOpen = false"
                        >
                            Layanan
                        </a>
                        <a
                            href="#values"
                            class="text-[13px] font-semibold text-white uppercase no-underline"
                            @click="mobileMenuOpen = false"
                        >
                            Nilai &amp; Cabang
                        </a>
                        <a
                            href="#clients"
                            class="text-[13px] font-semibold text-white uppercase no-underline"
                            @click="mobileMenuOpen = false"
                        >
                            Mitra
                        </a>
                        <Link
                            href="/tracking"
                            class="text-[13px] font-semibold text-white uppercase no-underline"
                            @click="mobileMenuOpen = false"
                        >
                            Cek Resi Publik
                        </Link>
                        <Link
                            href="/login"
                            class="text-[13px] font-bold text-[#93c5fd] uppercase no-underline"
                            @click="mobileMenuOpen = false"
                        >
                            Masuk Workspace ERP
                        </Link>
                    </div>
                </div>
            </header>

            <!-- Hero Copy Positioned Right-Aligned Like Reference -->
            <div
                class="relative z-10 mx-auto grid max-w-[1200px] grid-cols-1 px-6 py-20 md:grid-cols-12 md:px-10 md:py-28"
            >
                <div class="md:col-span-6 md:col-start-7">
                    <p
                        class="text-[11px] font-bold tracking-[0.18em] text-[#93c5fd] uppercase"
                    >
                        PACHE LOGISTIC · STRICTLY NON-COD ERP
                    </p>
                    <h1
                        class="mt-3 text-[clamp(32px,3.8vw,48px)] leading-[1.14] font-bold tracking-[-0.03em] text-white uppercase"
                    >
                        KAMI JARINGAN |<br />
                        PENGIRIMAN TERPADU
                    </h1>
                    <p
                        class="mt-4 max-w-[490px] text-[15px] leading-[1.7] text-[#e2ebf5]"
                    >
                        Platform ERP logistik terpadu untuk pemesanan
                        multi-koli, kontrol stok gudang anti-minus, manifest
                        linehaul, dan penagihan B2B TOP tanpa uang tunai di
                        tangan kurir.
                    </p>

                    <div class="mt-7 flex flex-wrap items-center gap-3.5">
                        <Link
                            href="/tracking"
                            class="group inline-flex min-h-[48px] items-center gap-3 rounded-[10px] bg-[#2563eb] px-6 text-[12px] font-bold tracking-[0.08em] text-white uppercase no-underline shadow-[0_12px_26px_-8px_rgba(37,99,235,0.65)] transition duration-200 hover:-translate-y-0.5 hover:bg-[#1d4ed8]"
                        >
                            <span>Lacak Multi-AWB</span>
                            <ArrowRight
                                class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1"
                            />
                        </Link>
                        <Link
                            href="/login"
                            class="group inline-flex min-h-[48px] items-center gap-3 rounded-[10px] border border-white/80 bg-white px-6 text-[12px] font-bold tracking-[0.08em] text-[#172b46] uppercase no-underline shadow-[0_12px_26px_-10px_rgba(15,23,42,0.45)] transition duration-200 hover:-translate-y-0.5 hover:bg-[#dbeafe]"
                        >
                            <span>Masuk Workspace</span>
                            <ArrowRight
                                class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1"
                            />
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Deep Navy Intro Statement Band + Quick AWB Lookup (#172b46) -->
        <section class="bg-[#172b46] text-white">
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
                            EMPAT MODUL TERPADU
                        </h2>
                        <p
                            class="mt-2 text-[11.5px] font-semibold tracking-[0.08em] text-[#93c5fd] uppercase tabular-nums"
                        >
                            {{ stats.branches }} Cabang ·
                            {{ stats.routes }} Rute · {{ stats.shipments }} Resi
                        </p>
                    </div>

                    <div class="space-y-4">
                        <p
                            class="text-[14px] leading-[1.8] text-[#cbd5e1] italic"
                        >
                            Di Pache Logistic, seluruh operasional bergerak di
                            atas kebijakan Strictly Non-COD — hanya mendukung
                            Online Payment QRIS/VA, Cash di loket asal, dan B2B
                            Term of Payment (TOP) dengan gerbang validasi
                            otomatis sebelum pengiriman diberangkatkan.
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
                                    placeholder="Masukkan nomor resi / AWB (contoh: PCH-JKT-261121-00001)"
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
                        <div
                            v-if="sampleAwbs.length"
                            class="flex flex-wrap items-center gap-2 text-[11px]"
                        >
                            <span class="text-[#93c5fd]">Contoh AWB:</span>
                            <button
                                v-for="code in sampleAwbs"
                                :key="code"
                                type="button"
                                @click="
                                    quickAwb = code;
                                    submitQuickTrack();
                                "
                                class="rounded-full border border-[#345482] bg-[#0f1f36]/75 px-3 py-1 font-semibold text-[#e2ebf5] tabular-nums transition hover:border-[#60a5fa] hover:bg-[#2563eb] hover:text-white"
                            >
                                {{ code }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2x2 Diagonal-Slant Service Cards Grid (Cool-Paper #f7f9fc) -->
        <section
            class="border-b border-[#dce5f1] bg-[#f7f9fc] py-14 md:py-16"
            id="services"
        >
            <div class="mx-auto max-w-[1200px] px-6 md:px-10">
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
                                <div
                                    class="flex items-center justify-between gap-2"
                                >
                                    <span
                                        class="inline-flex rounded-full bg-[#eff6ff] px-2.5 py-0.5 text-[10.5px] font-bold tracking-[0.08em] text-[#2563eb] uppercase tabular-nums"
                                    >
                                        0{{ idx + 1 }} · MODUL
                                    </span>
                                </div>
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
                                <ArrowRight
                                    class="h-3.5 w-3.5 transition-transform duration-200 group-hover:translate-x-1"
                                />
                            </div>
                        </div>
                    </article>
                </div>

                <p
                    class="mx-auto mt-10 max-w-[780px] text-center text-[13px] leading-[1.85] text-[#596579] italic"
                >
                    Kepuasan mitra korporat dan keamanan arus kas adalah
                    prioritas utama. Seluruh modul OMS, WMS, TMS, dan Finance
                    Non-COD bekerja dalam satu ekosistem transaksional yang
                    teruji dari loket asal hingga bukti terima digital e-POD.
                </p>
            </div>
        </section>

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

                        <Link
                            href="/tracking"
                            class="mt-4 inline-flex items-center gap-2 text-[11.5px] font-bold tracking-[0.08em] text-[#2563eb] uppercase no-underline hover:text-[#1d4ed8]"
                        >
                            <span>Lacak Pengiriman Antar Cabang</span>
                            <ArrowRight class="h-3.5 w-3.5" />
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Clients / Mitra Strip (Cool-Paper #f7f9fc) -->
        <section class="bg-[#f7f9fc] py-14" id="clients">
            <div class="mx-auto max-w-[1200px] px-6 md:px-10">
                <h2
                    class="text-center text-[18px] font-bold tracking-[0.06em] text-[#18202c] uppercase"
                >
                    KLIEN &amp; MITRA B2B KAMI
                </h2>

                <div
                    class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6"
                >
                    <div
                        v-for="client in clients"
                        :key="client.code"
                        class="group flex flex-col items-center justify-between rounded-[16px] border border-[#dce5f1] bg-white px-4 py-5 text-center shadow-[0_8px_20px_-14px_rgba(23,43,70,0.16)] transition duration-200 hover:-translate-y-0.5 hover:border-[#93c5fd] hover:shadow-[0_14px_28px_-16px_rgba(37,99,235,0.22)]"
                    >
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-full border border-[#dbeafe] bg-[#eff6ff] text-[12px] font-bold tracking-[0.05em] text-[#1d4ed8] tabular-nums transition duration-200 group-hover:border-[#2563eb] group-hover:bg-[#2563eb] group-hover:text-white"
                        >
                            {{ clientMonogram(client.name) }}
                        </span>
                        <p
                            class="mt-3 line-clamp-2 text-[12.5px] leading-[1.35] font-bold text-[#18202c]"
                        >
                            {{ client.name }}
                        </p>
                        <span
                            class="mt-2.5 inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold tracking-[0.07em] uppercase"
                            :class="
                                client.type === 'b2b_contract'
                                    ? 'bg-[#eff6ff] text-[#1d4ed8]'
                                    : 'bg-[#f3f7fc] text-[#596579]'
                            "
                        >
                            {{
                                client.type === 'b2b_contract'
                                    ? 'B2B TOP'
                                    : 'RETAIL'
                            }}
                        </span>
                    </div>
                </div>

                <p class="mt-7 text-center text-[13px] text-[#596579] italic">
                    Terdaftar aktif pada Master Data Pelanggan Pache Logistic —
                    mendukung kontrak kredit B2B maupun pengiriman ritel loket.
                </p>
            </div>
        </section>

        <!-- Diagonal Split CTA Band (Pache Brand Blue #2563eb + Cargo Photo, Full-Bleed Left) -->
        <section class="relative overflow-hidden bg-[#172b46]">
            <div
                class="absolute inset-y-0 left-0 hidden w-1/2 bg-[#2563eb] lg:block"
                aria-hidden="true"
            />
            <img
                src="/images/landing/svc-cargo.jpg"
                alt="Armada kontainer laut dan kargo terintegrasi"
                class="absolute inset-y-0 right-0 hidden h-full w-1/2 object-cover lg:block"
            />
            <div class="relative z-10 mx-auto max-w-[1200px]">
                <div
                    class="pache-diag-banner bg-[#2563eb] px-6 py-10 text-white md:px-10 lg:w-[64%] lg:pr-24"
                >
                    <div
                        class="flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-center"
                    >
                        <div>
                            <h2
                                class="text-[20px] font-bold tracking-[0.03em] uppercase"
                            >
                                LOGISTIK DAN KARGO NON-COD
                            </h2>
                            <p
                                class="mt-1.5 max-w-[440px] text-[13.5px] leading-[1.65] text-[#dbeafe] italic"
                            >
                                Masuk ke Workspace ERP untuk booking resi A6,
                                scan bagging manifest, atau cek status kiriman
                                Anda sekarang.
                            </p>
                        </div>
                        <Link
                            href="/login"
                            class="group inline-flex min-h-[46px] shrink-0 items-center gap-3 rounded-[10px] bg-white px-6 text-[12px] font-bold tracking-[0.08em] text-[#172b46] uppercase no-underline shadow-[0_10px_24px_-10px_rgba(15,23,42,0.35)] transition duration-200 hover:-translate-y-0.5 hover:bg-[#dbeafe]"
                        >
                            <span>Masuk ERP</span>
                            <ArrowRight
                                class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1"
                            />
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Dark Structural Footer (#111111) -->
        <footer class="bg-[#111111] text-[#94a3b8]">
            <div class="mx-auto max-w-[1200px] px-6 py-12 md:px-10">
                <div
                    class="grid grid-cols-1 gap-10 border-b border-[#262626] pb-10 md:grid-cols-[1.05fr_1fr_1fr]"
                >
                    <!-- Brand Column -->
                    <div>
                        <PacheLogo :inverted="true" />
                        <p
                            class="mt-4 max-w-[34ch] text-[12.5px] leading-[1.8] text-[#cbd5e1]"
                        >
                            Platform ERP Logistik terpadu Strictly Non-COD untuk
                            pengelolaan OMS, WMS, TMS, Finance TOP, dan Live
                            Public Tracking.
                        </p>
                        <div class="mt-4 flex items-center gap-3">
                            <Link
                                href="/tracking"
                                class="text-[12px] font-semibold text-[#93c5fd] no-underline hover:underline"
                            >
                                Cek Resi Publik &raquo;
                            </Link>
                        </div>
                    </div>

                    <!-- Quick Links 2-Column List -->
                    <div>
                        <h3
                            class="text-[12px] font-bold tracking-[0.1em] text-white uppercase"
                        >
                            Tautan Cepat
                        </h3>
                        <div
                            class="mt-4 grid grid-cols-2 gap-x-6 gap-y-2 text-[12.5px]"
                        >
                            <a
                                href="#top"
                                class="text-[#cbd5e1] no-underline hover:text-[#93c5fd]"
                                >Beranda</a
                            >
                            <Link
                                href="/oms/shipments"
                                class="text-[#cbd5e1] no-underline hover:text-[#93c5fd]"
                                >OMS Resi</Link
                            >
                            <a
                                href="#services"
                                class="text-[#cbd5e1] no-underline hover:text-[#93c5fd]"
                                >Layanan</a
                            >
                            <Link
                                href="/wms/inventory"
                                class="text-[#cbd5e1] no-underline hover:text-[#93c5fd]"
                                >WMS Gudang</Link
                            >
                            <a
                                href="#values"
                                class="text-[#cbd5e1] no-underline hover:text-[#93c5fd]"
                                >Nilai &amp; Cabang</a
                            >
                            <Link
                                href="/tms/manifests"
                                class="text-[#cbd5e1] no-underline hover:text-[#93c5fd]"
                                >TMS Manifest</Link
                            >
                            <Link
                                href="/tracking"
                                class="text-[#cbd5e1] no-underline hover:text-[#93c5fd]"
                                >Cek Resi</Link
                            >
                            <Link
                                href="/finance"
                                class="text-[#cbd5e1] no-underline hover:text-[#93c5fd]"
                                >Finance TOP</Link
                            >
                        </div>
                    </div>

                    <!-- Reach Us -->
                    <div>
                        <h3
                            class="text-[12px] font-bold tracking-[0.1em] text-white uppercase"
                        >
                            Kantor Pusat
                        </h3>
                        <ul
                            class="mt-4 space-y-2.5 p-0 text-[12.5px] text-[#cbd5e1]"
                        >
                            <li class="flex list-none items-start gap-2.5">
                                <MapPin
                                    class="mt-0.5 h-4 w-4 shrink-0 text-[#93c5fd]"
                                />
                                <span
                                    >Jl. Jend. Sudirman Kav. 52, Jakarta
                                    Selatan</span
                                >
                            </li>
                            <li
                                class="flex list-none items-center gap-2.5 tabular-nums"
                            >
                                <Phone
                                    class="h-4 w-4 shrink-0 text-[#93c5fd]"
                                />
                                <span>021-5501-8800</span>
                            </li>
                            <li class="flex list-none items-center gap-2.5">
                                <Mail class="h-4 w-4 shrink-0 text-[#93c5fd]" />
                                <span>ops@pachelogistic.id</span>
                            </li>
                            <li
                                class="flex list-none items-center gap-2.5 tabular-nums"
                            >
                                <Clock
                                    class="h-4 w-4 shrink-0 text-[#93c5fd]"
                                />
                                <span>Senin - Sabtu : 08:00 - 18:00</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div
                    class="flex flex-col items-center justify-between gap-3 pt-6 text-[11.5px] text-[#94a3b8] sm:flex-row"
                >
                    <span
                        >&copy; 2026 Pache Logistic. Strictly Non-COD Logistics
                        ERP.</span
                    >
                    <a
                        href="#top"
                        class="font-semibold text-[#cbd5e1] no-underline hover:text-[#93c5fd]"
                    >
                        Kembali ke atas
                    </a>
                </div>
            </div>
        </footer>
    </div>
</template>
