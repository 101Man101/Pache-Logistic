<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowRight,
    ArrowUpRight,
    Building2,
    CheckCircle2,
    CreditCard,
    LayoutDashboard,
    Menu,
    Package,
    Plus,
    ReceiptText,
    Search,
    ShieldCheck,
    Truck,
    Warehouse,
    X,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import PacheLogo from '@/components/PacheLogo.vue';

const props = defineProps<{
    stats: {
        branches: number;
        routes: number;
        shipments: number;
    };
    sampleAwbs: string[];
}>();

const mobileMenuOpen = ref(false);
const quickAwb = ref(props.sampleAwbs[0] ?? 'PCH-JKT-261121-00001');
const activeTab = ref<'dashboard' | 'oms' | 'wms' | 'tms' | 'finance'>('dashboard');

const showcaseTabs = [
    { id: 'dashboard', label: 'Dashboard KPI', icon: LayoutDashboard },
    { id: 'oms', label: 'OMS Non-COD', icon: Package },
    { id: 'wms', label: 'WMS Bin Ledger', icon: Warehouse },
    { id: 'tms', label: 'TMS & e-POD', icon: Truck },
    { id: 'finance', label: 'Finance TOP', icon: ReceiptText },
] as const;

const showcaseDetails = computed(() => {
    const map = {
        dashboard: {
            eyebrow: 'RINGKASAN EKSEKUTIF REAL-TIME',
            title: 'Seluruh cabang, armada, dan arus kas Non-COD dalam satu kanvas.',
            desc: 'Pantau performa pengiriman, proporsi kanal pembayaran (Online QRIS/VA, Cash Loket, dan B2B TOP), serta peringatan kepatuhan dokumen STNK/KIR tanpa menunggu rekap manual.',
            metrics: [
                { label: 'Waktu Input & Cetak AWB', value: '< 15 detik' },
                { label: 'Selisih Rekonsiliasi Kas', value: '0,0%' },
                { label: 'Kepatuhan Bukti e-POD', value: '100%' },
            ],
            highlights: [
                'Deferred KPI cards memuat cepat (< 300 ms) tanpa memblokir tabel utama',
                'Peringatan otomatis BR-08 (resi pending) & BR-06 (STNK/KIR H-30)',
                'Command Palette Ctrl+K untuk lompat instan ke AWB, Manifest, atau Pelanggan',
            ],
        },
        oms: {
            eyebrow: 'ORDER MANAGEMENT SYSTEM (STRICTLY NON-COD)',
            title: 'Booking multi-koli reaktif dengan validasi tarif & plafon TOP otomatis.',
            desc: 'Hitung berat aktual vs volumetrik (pembagi 6000/5000/4000) secara langsung sebelum AWB terbit. Sistem mengunci metode bayar hanya pada Online Payment, Cash Loket, dan B2B TOP.',
            metrics: [
                { label: 'Pembagi Volumetrik', value: '6000 / 5000 / 4000' },
                { label: 'Metode Pembayaran', value: '3 Kanal Non-COD' },
                { label: 'Format Label Termal', value: 'A6 100×150 mm' },
            ],
            highlights: [
                'Generator nomor AWB atomik bebas bentrok (PCH-CABANG-TANGGAL-URUT)',
                'Cek batas kredit BR-02 otomatis menolak order TOP di atas Credit Limit',
                'Label A6 mencantumkan stempel NON-COD agar kurir tidak pernah menagih uang',
            ],
        },
        wms: {
            eyebrow: 'WAREHOUSE MANAGEMENT SYSTEM',
            title: 'Topologi rak presisi dan buku besar stok anti-minus.',
            desc: 'Kelola gudang multi-zona (Dry, Cold, Staging) sampai level Bin. Setiap inbound, picking FEFO/FIFO, dan adjustment dikunci dengan Pessimistic Lock di PostgreSQL.',
            metrics: [
                { label: 'Akurasi Stok per Bin', value: '>= 99,8%' },
                { label: 'Aturan Alokasi', value: 'FIFO & FEFO' },
                { label: 'Kejadian Stok Minus', value: '0' },
            ],
            highlights: [
                'Immutable Inventory Ledger (BR-04): dilarang menghapus atau menimpa saldo',
                'Mode Scanner-First USB/Bluetooth HID dengan umpan balik audio beep',
                'Pelacakan nomor Batch dan tanggal kedaluwarsa untuk klien farmasi & F&B',
            ],
        },
        tms: {
            eyebrow: 'TRANSPORTATION & FLEET MANAGEMENT',
            title: 'Scan bagging terkunci rute & bukti terima digital wajib GPS.',
            desc: 'Konsolidasi ratusan resi ke Manifest linehaul tanpa menyentuh mouse. Non-COD Dispatch Gate (BR-08) otomatis memblokir paket yang belum lunas atau salah rute Hub.',
            metrics: [
                { label: 'Validasi Scan Barcode', value: '< 150 ms' },
                { label: 'Blokir Resi Pending', value: 'Otomatis (BR-08)' },
                { label: 'Syarat e-POD Kurir', value: 'Nama + GPS + Foto' },
            ],
            highlights: [
                'Blokir penugasan armada bila STNK, Uji KIR, atau SIM supir kedaluwarsa (BR-06)',
                'Pembaruan massal status InTransit saat Manifest diberangkatkan dengan segel',
                'Kurir last-mile fokus 100% pada pengantaran dan e-POD tanpa beban uang tunai',
            ],
        },
        finance: {
            eyebrow: 'FINANCE NON-COD & AUTO-INVOICING B2B',
            title: 'Rekonsiliasi QRIS/VA instan, kas loket terkunci, & invoice TOP dalam 1 klik.',
            desc: 'Tidak ada lagi risiko kebocoran uang di tangan kurir. Kasir cabang menutup kas tunai loket harian, payment gateway melunasi order online otomatis, dan Finance menerbitkan tagihan TOP kolektif.',
            metrics: [
                { label: 'Durasi Terbit Invoice B2B', value: '< 15 menit' },
                { label: 'Pajak Otomatis', value: 'PPN 11% · PPh 23 2%' },
                { label: 'Termin Kredit B2B', value: 'Net 7 / 14 / 30 / 45' },
            ],
            highlights: [
                'Penarikan otomatis seluruh resi TOP berstatus Delivered beserta rekap e-POD',
                'Laporan Umur Piutang (AR Aging: Current, 1–30, 31–60, >60 hari)',
                'Double-entry append-only Financial Ledger untuk audit 5+ tahun',
            ],
        },
    };

    return map[activeTab.value];
});

function submitQuickTrack(): void {
    const code = quickAwb.value.trim();
    if (!code) return;
    router.get('/tracking', { awb: code });
}

const faqs = [
    {
        q: 'Mengapa Pache Logistic menerapkan kebijakan Strictly Non-COD?',
        a: 'Kebijakan Non-COD menghilangkan seluruh risiko kebocoran uang tunai di tangan kurir, mempercepat arus kas perusahaan, dan membuat proses serah terima di lokasi penerima jauh lebih cepat. Pengirim tetap memiliki tiga pilihan pembayaran fleksibel: Online Payment (QRIS/Virtual Account), Cash di loket asal saat drop-off, atau kredit berjangka (TOP) untuk mitra korporat B2B.',
    },
    {
        q: 'Bagaimana cara kerja batas kredit (Credit Limit) pada skema B2B TOP?',
        a: 'Setiap pelanggan korporat terdaftar memiliki plafon kredit dan termin pembayaran (Net 7, 14, 30, atau 45 hari). Saat booking resi TOP dibuat, mesin aturan BR-02 menghitung (Invoice Belum Lunas + Resi TOP Berjalan + Ongkir Baru). Bila melampaui plafon, sistem otomatis menahan penerbitan resi sampai ada pelunasan.',
    },
    {
        q: 'Bagaimana perhitungan berat tagih (Chargeable Weight) di Pache Logistic?',
        a: 'Sistem membandingkan total berat aktual timbangan dengan berat volumetrik menggunakan rumus (P × L × T) / Pembagi, dengan pembagi 6000 untuk Reguler, 5000 untuk Express, dan 4000 untuk Cargo. Nilai tertinggi dibulatkan ke atas secara transparan sebelum AWB dicetak.',
    },
    {
        q: 'Apakah penerima paket dapat melacak resi tanpa membocorkan alamat lengkap ke publik?',
        a: 'Ya. Halaman Cek Resi Publik Pache Logistic menyamarkan (PII Masking) nama, nomor telepon, dan alamat lengkap penerima secara default. Detail lengkap hanya terbuka apabila pemilik paket memasukkan 4 digit terakhir nomor HP penerima yang terdaftar pada resi.',
    },
];
</script>

<template>
    <Head title="ERP Logistik Terpadu Non-COD" />

    <div class="pache-landing">
        <!-- Sticky Frosted Header -->
        <header class="pache-landing-header" id="top">
            <div class="mx-auto flex max-w-[1360px] items-center justify-between gap-6 px-6 py-4 md:px-12 md:py-5">
                <Link href="/" class="no-underline" aria-label="Beranda Pache Logistic">
                    <PacheLogo />
                </Link>

                <nav class="hidden items-center gap-7 md:flex" aria-label="Navigasi utama">
                    <Link href="/tracking" class="text-[13px] font-medium text-[#596579] no-underline transition hover:text-[#2563eb]">
                        Cek Resi
                    </Link>
                    <a href="#showcase" class="text-[13px] font-medium text-[#596579] no-underline transition hover:text-[#2563eb]">
                        Modul ERP
                    </a>
                    <a href="#workflow" class="text-[13px] font-medium text-[#596579] no-underline transition hover:text-[#2563eb]">
                        Kebijakan Non-COD
                    </a>
                    <a href="#faq" class="text-[13px] font-medium text-[#596579] no-underline transition hover:text-[#2563eb]">
                        FAQ
                    </a>
                </nav>

                <div class="flex items-center gap-2.5">
                    <Link href="/login" class="pache-header-cta">
                        <span>Masuk ERP</span>
                        <ArrowUpRight class="h-4 w-4" :stroke-width="2" />
                    </Link>
                    <button
                        type="button"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="flex h-11 w-11 items-center justify-center rounded-lg text-[#18202c] hover:bg-[#2563eb]/8 md:hidden"
                        aria-label="Toggle navigasi"
                    >
                        <X v-if="mobileMenuOpen" class="h-5 w-5" />
                        <Menu v-else class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <div v-if="mobileMenuOpen" class="border-t border-[#dce5f1] bg-[#f7f9fc] px-6 py-4 md:hidden">
                <div class="flex flex-col gap-3">
                    <Link href="/tracking" class="text-[14px] font-medium text-[#18202c] no-underline" @click="mobileMenuOpen = false">Cek Resi Publik</Link>
                    <a href="#showcase" class="text-[14px] font-medium text-[#18202c] no-underline" @click="mobileMenuOpen = false">Modul ERP</a>
                    <a href="#workflow" class="text-[14px] font-medium text-[#18202c] no-underline" @click="mobileMenuOpen = false">Kebijakan Non-COD</a>
                    <a href="#faq" class="text-[14px] font-medium text-[#18202c] no-underline" @click="mobileMenuOpen = false">FAQ</a>
                </div>
            </div>
        </header>

        <!-- Hero Section with #hero-paper-lines SVG Texture -->
        <section class="relative isolate overflow-hidden py-10 md:py-14">
            <svg class="pache-hero-texture" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                <defs>
                    <pattern id="hero-paper-lines" width="224" height="224" patternUnits="userSpaceOnUse">
                        <g fill="none" stroke="currentColor" stroke-width="1">
                            <path d="M-40 48H56a72 72 0 0 1 72 72v144" />
                            <path d="M-40 64H56a56 56 0 0 1 56 56v144" />
                            <path d="M-40 80H56a40 40 0 0 1 40 40v144" />
                            <path d="M184-40v72a40 40 0 0 0 40 40h40" />
                            <path d="M200-40v72a24 24 0 0 0 24 24h40" />
                        </g>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#hero-paper-lines)" />
            </svg>

            <div class="relative z-10 mx-auto grid max-w-[1360px] grid-cols-1 items-center gap-10 px-6 md:px-12 lg:grid-cols-[1fr_1.08fr]">
                <!-- Left Copy -->
                <div>
                    <div class="mb-5 inline-flex items-center gap-2 rounded-lg border border-[#dce5f1] bg-white px-3 py-1.5 text-[12px] font-semibold text-[#233c65]">
                        <ShieldCheck class="h-4 w-4 text-[#2563eb]" :stroke-width="1.9" />
                        <span>Strictly Non-COD · Online Payment · Cash Loket · B2B TOP</span>
                    </div>

                    <h1 class="text-[clamp(38px,4.1vw,56px)] leading-[1.15] font-[650] tracking-[-0.045em] text-[#18202c]">
                        <span class="block">Logistik presisi tanpa celah,</span>
                        <span class="block text-[#2563eb]">100% Non-COD terintegrasi.</span>
                    </h1>

                    <p class="mt-5 max-w-[490px] text-[16px] leading-[1.8] text-[#596579]">
                        <strong>Pache Logistic</strong> menyatukan pemesanan multi-koli (OMS), gudang FIFO/FEFO (WMS), konsolidasi armada &amp; e-POD (TMS), hingga penagihan otomatis B2B TOP dalam satu sistem ERP tanpa kebocoran kas kurir.
                    </p>

                    <div class="mt-7 flex flex-wrap items-center gap-4">
                        <Link href="/login" class="pache-hero-cta">
                            <span>Buka Workspace ERP</span>
                            <ArrowRight class="h-[18px] w-[18px]" :stroke-width="2" />
                        </Link>
                        <Link
                            href="/tracking"
                            class="inline-flex min-h-[50px] items-center gap-2.5 rounded-[9px] border border-[#dce5f1] bg-white px-5 text-[14px] font-semibold text-[#18202c] no-underline transition hover:border-[#2563eb] hover:text-[#2563eb]"
                        >
                            <Search class="h-4 w-4 text-[#2563eb]" />
                            <span>Lacak Multi-AWB</span>
                        </Link>
                    </div>

                    <div class="mt-8 grid max-w-[460px] grid-cols-3 gap-5 border-t border-[#dce5f1] pt-5">
                        <div>
                            <p class="text-[24px] font-bold tracking-[-0.03em] text-[#18202c] tabular-nums">
                                {{ stats.branches }} Hub
                            </p>
                            <p class="text-[12px] text-[#596579]">Cabang &amp; Sorting Center</p>
                        </div>
                        <div>
                            <p class="text-[24px] font-bold tracking-[-0.03em] text-[#18202c] tabular-nums">
                                {{ stats.routes }} Rute
                            </p>
                            <p class="text-[12px] text-[#596579]">Matriks Tarif Aktif</p>
                        </div>
                        <div>
                            <p class="text-[24px] font-bold tracking-[-0.03em] text-[#2563eb] tabular-nums">
                                0% COD
                            </p>
                            <p class="text-[12px] text-[#596579]">Bebas Risiko Kas Kurir</p>
                        </div>
                    </div>
                </div>

                <!-- Right Interactive Quick Tracking + Dispatch Preview Card -->
                <div class="rounded-[18px] border border-[#dce5f1] bg-white p-6 shadow-[0_12px_36px_-14px_rgba(24,32,44,0.14)] md:p-7">
                    <div class="flex items-center justify-between border-b border-[#dce5f1] pb-4">
                        <div>
                            <p class="text-[11px] font-bold tracking-[0.12em] text-[#2563eb] uppercase">
                                PELACAKAN RESI PUBLIK
                            </p>
                            <h2 class="mt-1 text-[20px] font-[650] tracking-[-0.03em] text-[#18202c]">
                                Cek Status Pengiriman Pache Logistic
                            </h2>
                        </div>
                        <span class="rounded-md border border-[#bfdbfe] bg-[#eff6ff] px-2.5 py-1 text-[11px] font-bold text-[#1d4ed8]">
                            LIVE DB
                        </span>
                    </div>

                    <form @submit.prevent="submitQuickTrack" class="mt-5 space-y-3">
                        <label class="block text-[12px] font-semibold text-[#596579]">
                            Nomor Resi / AWB (dukung hingga 10 AWB sekaligus)
                        </label>
                        <div class="flex flex-col gap-2.5 sm:flex-row">
                            <input
                                v-model="quickAwb"
                                type="text"
                                placeholder="Contoh: PCH-JKT-261121-00001"
                                class="h-[46px] flex-1 rounded-[8px] border border-[#d5dfed] bg-[#f7f9fc] px-3.5 text-[14px] font-medium text-[#18202c] outline-none tabular-nums focus:border-[#2563eb] focus:bg-white"
                            />
                            <button
                                type="submit"
                                class="inline-flex h-[46px] items-center justify-center gap-2 rounded-[8px] bg-[#2563eb] px-5 text-[13.5px] font-semibold text-white transition hover:bg-[#1d4ed8]"
                            >
                                <Search class="h-4 w-4" />
                                <span>Lacak</span>
                            </button>
                        </div>
                    </form>

                    <div class="mt-4">
                        <p class="mb-2 text-[11.5px] font-medium text-[#748095]">
                            Coba resi aktif dari database lokal:
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="code in sampleAwbs"
                                :key="code"
                                type="button"
                                @click="quickAwb = code; submitQuickTrack()"
                                class="rounded-md border border-[#dce5f1] bg-[#f7f9fc] px-2.5 py-1.5 text-[12px] font-semibold text-[#18202c] transition tabular-nums hover:border-[#2563eb] hover:bg-[#eff6ff] hover:text-[#1d4ed8]"
                            >
                                {{ code }}
                            </button>
                        </div>
                    </div>

                    <!-- Mini Non-COD Payment Gate Visual -->
                    <div class="mt-6 grid grid-cols-3 gap-2.5 rounded-[12px] bg-[#eef3fb] p-3.5">
                        <div class="rounded-[9px] border border-white bg-white p-3">
                            <CreditCard class="h-4 w-4 text-[#2563eb]" />
                            <p class="mt-2 text-[12px] font-bold text-[#18202c]">Online</p>
                            <p class="text-[11px] text-[#596579]">QRIS &amp; VA Instan</p>
                        </div>
                        <div class="rounded-[9px] border border-white bg-white p-3">
                            <Building2 class="h-4 w-4 text-[#15803d]" />
                            <p class="mt-2 text-[12px] font-bold text-[#18202c]">Cash Loket</p>
                            <p class="text-[11px] text-[#596579]">Lunas di Cabang Asal</p>
                        </div>
                        <div class="rounded-[9px] border border-white bg-white p-3">
                            <ReceiptText class="h-4 w-4 text-[#b45309]" />
                            <p class="mt-2 text-[12px] font-bold text-[#18202c]">B2B TOP</p>
                            <p class="text-[11px] text-[#596579]">Net 7 / 14 / 30 Hari</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Soft Neumorphism Showcase Tabs (Strictly on Landing Showcase) -->
        <section class="py-12 md:py-16" id="showcase">
            <div class="mx-auto max-w-[1180px] px-6 md:px-12">
                <div class="mb-7 max-w-[660px]">
                    <p class="text-[12px] font-bold tracking-[0.12em] text-[#2563eb] uppercase">
                        ARSITEKTUR MODULAR MONOLITH
                    </p>
                    <h2 class="mt-2 text-[clamp(28px,3.1vw,40px)] leading-[1.22] font-[650] tracking-[-0.045em] text-[#18202c]">
                        Lima modul operasional dalam satu alur kerja yang saling mengunci.
                    </h2>
                </div>

                <!-- Restricted Soft Neumorphism Tab Bar -->
                <div class="showcase-tabs" role="tablist" aria-label="Pilih modul ERP Pache Logistic">
                    <button
                        v-for="tab in showcaseTabs"
                        :key="tab.id"
                        type="button"
                        role="tab"
                        :aria-selected="activeTab === tab.id"
                        class="showcase-tab"
                        :class="{ 'is-active': activeTab === tab.id }"
                        @click="activeTab = tab.id"
                    >
                        <component :is="tab.icon" class="h-[18px] w-[18px] shrink-0" :stroke-width="1.6" />
                        <span>{{ tab.label }}</span>
                    </button>
                </div>

                <!-- Active Showcase Stage -->
                <div class="mt-7 grid grid-cols-1 items-center gap-8 rounded-[16px] bg-[#eef3fb] p-6 md:p-10 lg:grid-cols-[0.95fr_1.05fr]">
                    <div>
                        <span class="text-[11px] font-bold tracking-[0.1em] text-[#1d4ed8] uppercase">
                            {{ showcaseDetails.eyebrow }}
                        </span>
                        <h3 class="mt-3 text-[26px] leading-[1.3] font-[650] tracking-[-0.04em] text-[#18202c]">
                            {{ showcaseDetails.title }}
                        </h3>
                        <p class="mt-4 text-[14.5px] leading-[1.85] text-[#596579]">
                            {{ showcaseDetails.desc }}
                        </p>
                        <ul class="mt-5 space-y-2.5 p-0">
                            <li
                                v-for="item in showcaseDetails.highlights"
                                :key="item"
                                class="flex items-start gap-2.5 text-[13.5px] text-[#18202c]"
                            >
                                <CheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-[#2563eb]" />
                                <span>{{ item }}</span>
                            </li>
                        </ul>
                        <Link
                            href="/login"
                            class="mt-6 inline-flex items-center gap-2.5 text-[13.5px] font-bold text-[#2563eb] no-underline hover:text-[#1d4ed8]"
                        >
                            <span>Coba langsung di Workspace Pache</span>
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                    </div>

                    <div class="rounded-[14px] border border-[#dce5f1] bg-white p-5 shadow-[0_10px_28px_-12px_rgba(24,32,44,0.2)]">
                        <div class="flex items-center justify-between border-b border-[#e2ebf5] pb-3">
                            <span class="text-[12px] font-bold text-[#172b46]">
                                Pache Logistic · {{ showcaseTabs.find((t) => t.id === activeTab)?.label }}
                            </span>
                            <span class="rounded bg-[#eff6ff] px-2 py-0.5 text-[11px] font-semibold text-[#1d4ed8]">
                                Laravel 13 + PostgreSQL
                            </span>
                        </div>
                        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <div
                                v-for="(m, idx) in showcaseDetails.metrics"
                                :key="m.label"
                                class="rounded-[12px] p-4"
                                :class="
                                    idx === 0
                                        ? 'bg-[#2563eb] text-white'
                                        : idx === 1
                                          ? 'bg-[#dcecff] text-[#214f8a]'
                                          : 'border border-[#e2ebf5] bg-[#f3f7fc] text-[#172b46]'
                                "
                            >
                                <span
                                    class="block text-[11px] font-medium"
                                    :class="idx === 0 ? 'text-blue-100' : idx === 1 ? 'text-[#617fa6]' : 'text-[#64748b]'"
                                >
                                    {{ m.label }}
                                </span>
                                <strong class="mt-2 block text-[19px] font-bold tracking-[-0.03em] tabular-nums">
                                    {{ m.value }}
                                </strong>
                            </div>
                        </div>
                        <div class="mt-4 rounded-[10px] border border-[#e2ebf5] bg-[#f8fbff] p-4">
                            <div class="flex items-center justify-between text-[12px] font-semibold text-[#172b46]">
                                <span>Validasi Kebijakan Inti (Domain Layer)</span>
                                <span class="text-[#15803d]">Aktif &amp; Teruji</span>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-2 text-[11.5px] text-[#596579]">
                                <div class="rounded-md bg-white px-2.5 py-2 border border-[#e2ebf5]">
                                    <strong class="text-[#18202c]">BR-01</strong> Chargeable Weight
                                </div>
                                <div class="rounded-md bg-white px-2.5 py-2 border border-[#e2ebf5]">
                                    <strong class="text-[#18202c]">BR-02</strong> Credit Limit TOP
                                </div>
                                <div class="rounded-md bg-white px-2.5 py-2 border border-[#e2ebf5]">
                                    <strong class="text-[#18202c]">BR-04</strong> Immutable Ledger
                                </div>
                                <div class="rounded-md bg-white px-2.5 py-2 border border-[#e2ebf5]">
                                    <strong class="text-[#18202c]">BR-08</strong> Non-COD Gate
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Zig-Zag Split Stories with Comic Speech Bubbles (Anti-Slop Layout) -->
        <section class="py-12 md:py-16" id="workflow">
            <div class="mx-auto max-w-[1200px] space-y-16 px-6 md:px-12">
                <div>
                    <p class="text-[12px] font-bold tracking-[0.12em] text-[#2563eb] uppercase">
                        STANDAR OPERASIONAL HARIAN
                    </p>
                    <h2 class="mt-2 text-[clamp(26px,2.8vw,34px)] font-[650] tracking-[-0.04em] text-[#18202c]">
                        Dirancang untuk kecepatan loket, gudang, dan keuangan korporat.
                    </h2>
                </div>

                <!-- Story 1 -->
                <article class="grid grid-cols-1 items-center gap-10 lg:grid-cols-2 lg:gap-16">
                    <div class="relative rounded-[18px] border border-[#dce5f1] bg-[#edf3fc] p-7">
                        <div class="space-y-3">
                            <div class="story-bubble max-w-[260px]">
                                0% kebocoran kas kurir — Strictly Non-COD
                            </div>
                            <div class="story-bubble story-bubble-right ml-auto max-w-[270px]">
                                Webhook QRIS &amp; Virtual Account melunasi resi seketika
                            </div>
                            <div class="story-bubble max-w-[280px]">
                                Klien korporat TOP Net 7/14/30 langsung lolos cek plafon BR-02
                            </div>
                        </div>
                    </div>
                    <div class="max-w-[460px]">
                        <h3 class="text-[clamp(24px,2.5vw,32px)] leading-[1.28] font-[650] tracking-[-0.04em] text-[#18202c]">
                            Tiga kanal pembayaran bersih,<br />tanpa satu rupiah pun di kurir.
                        </h3>
                        <p class="mt-4 text-[15px] leading-[1.85] text-[#596579]">
                            Setiap paket wajib berstatus <strong>PAID</strong> (melalui Online Payment atau Cash di loket asal) atau memiliki pagu kredit <strong>B2B TOP</strong> yang sah sebelum diizinkan masuk ke Manifest linehaul.
                        </p>
                        <Link href="/login" class="mt-5 inline-flex items-center gap-2 text-[14px] font-bold text-[#2563eb] no-underline hover:text-[#1d4ed8]">
                            <span>Lihat alur Finance &amp; Kasir Loket</span>
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                    </div>
                </article>

                <!-- Story 2 (Reversed Zig-Zag) -->
                <article class="grid grid-cols-1 items-center gap-10 lg:grid-cols-2 lg:gap-16">
                    <div class="order-2 max-w-[460px] lg:order-1">
                        <h3 class="text-[clamp(24px,2.5vw,32px)] leading-[1.28] font-[650] tracking-[-0.04em] text-[#18202c]">
                            Scan barcode tanpa mouse,<br />stok gudang terkunci rapat.
                        </h3>
                        <p class="mt-4 text-[15px] leading-[1.85] text-[#596579]">
                            Operator gudang dan sorting hub cukup menembakkan USB/Bluetooth barcode scanner. Umpan balik suara dan penguncian baris database memastikan tidak ada stok negatif maupun paket salah rute Hub.
                        </p>
                        <Link href="/login" class="mt-5 inline-flex items-center gap-2 text-[14px] font-bold text-[#2563eb] no-underline hover:text-[#1d4ed8]">
                            <span>Eksplorasi WMS &amp; TMS Manifest</span>
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                    </div>
                    <div class="relative order-1 rounded-[18px] border border-[#dce5f1] bg-[#edf3fc] p-7 lg:order-2">
                        <div class="space-y-3">
                            <div class="story-bubble max-w-[260px]">
                                Validasi scan &lt; 150 ms dengan audio beep sukses / tolak
                            </div>
                            <div class="story-bubble story-bubble-right ml-auto max-w-[270px]">
                                Blokir armada jika STNK, Uji KIR, atau SIM kedaluwarsa
                            </div>
                            <div class="story-bubble max-w-[280px]">
                                e-POD wajib nama penerima, relasi, dan koordinat GPS
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        <!-- Native <details> FAQ Accordion -->
        <section class="py-12 md:py-16" id="faq">
            <div class="mx-auto grid max-w-[1200px] grid-cols-1 gap-10 px-6 md:px-12 lg:grid-cols-[0.75fr_1.25fr] lg:gap-20">
                <div>
                    <h2 class="text-[32px] leading-[1.22] font-[650] tracking-[-0.04em] text-[#18202c]">
                        Pertanyaan<br />umum
                    </h2>
                    <p class="mt-4 text-[14px] leading-[1.8] text-[#596579]">
                        Ringkasan kebijakan operasional Non-COD, tarif volumetrik, dan privasi pelacakan di Pache Logistic.
                    </p>
                </div>
                <div class="pache-faq">
                    <details v-for="item in faqs" :key="item.q">
                        <summary>
                            <span>{{ item.q }}</span>
                            <Plus class="faq-plus h-4 w-4 shrink-0 text-[#748095] transition-transform duration-200" />
                        </summary>
                        <div class="pt-3 pr-8">
                            <p class="text-[14px] leading-[1.85] text-[#596579]">
                                {{ item.a }}
                            </p>
                        </div>
                    </details>
                </div>
            </div>
        </section>

        <!-- High-Contrast Dark Closing Banner (#111) -->
        <section class="bg-[#111111] text-white">
            <div class="mx-auto flex max-w-[1200px] flex-col items-start justify-between gap-8 px-6 py-16 md:flex-row md:items-center md:px-12">
                <h2 class="text-[clamp(28px,3.2vw,38px)] leading-[1.2] font-[650] tracking-[-0.04em]">
                    Siap mengendalikan pengiriman<br />
                    <span class="text-[#93c5fd]">dengan Pache Logistic?</span>
                </h2>
                <div class="max-w-[360px]">
                    <p class="mb-5 text-[13.5px] leading-[1.8] text-[#cbd5e1]">
                        Masuk ke ERP Workspace untuk membuat resi Non-COD, melakukan scan bagging Manifest, mengelola Bin WMS, atau menerbitkan Invoice B2B TOP.
                    </p>
                    <Link
                        href="/login"
                        class="inline-flex min-h-[48px] items-center gap-4 rounded-[9px] bg-white px-5 text-[14px] font-bold text-[#111111] no-underline transition hover:bg-[#dbeafe]"
                    >
                        <span>Masuk ke ERP Pache</span>
                        <ArrowUpRight class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- Cool-Paper Footer (#f0f4fa) -->
        <footer class="bg-[#f0f4fa] text-[#596579]">
            <div class="mx-auto max-w-[1200px] px-6 py-12 md:px-12">
                <div class="grid grid-cols-1 gap-10 border-b border-[#dce5f1] pb-10 md:grid-cols-[1fr_1.2fr]">
                    <div>
                        <PacheLogo />
                        <p class="mt-4 max-w-[36ch] text-[13px] leading-[1.85]">
                            Platform ERP Logistik terpadu 100% Non-COD untuk operasional multi-cabang, gudang fulfillment, manajemen armada, dan penagihan B2B TOP.
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-8 sm:grid-cols-3">
                        <div class="space-y-2">
                            <h3 class="text-[12px] font-bold text-[#344054]">Layanan Publik</h3>
                            <Link href="/tracking" class="block text-[12.5px] text-[#596579] no-underline hover:text-[#2563eb]">Cek Resi Multi-AWB</Link>
                            <Link href="/login" class="block text-[12.5px] text-[#596579] no-underline hover:text-[#2563eb]">Portal ERP &amp; B2B</Link>
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-[12px] font-bold text-[#344054]">Kanal Pembayaran</h3>
                            <p class="text-[12.5px]">Online Payment (QRIS / VA)</p>
                            <p class="text-[12.5px]">Cash di Loket Asal</p>
                            <p class="text-[12.5px]">B2B Term of Payment (TOP)</p>
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-[12px] font-bold text-[#344054]">Kantor Pusat</h3>
                            <p class="text-[12.5px]">Jl. Jend. Sudirman Kav. 52, Jakarta Selatan</p>
                            <p class="text-[12.5px] tabular-nums">021-5501-8800</p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col items-center justify-between gap-3 pt-6 text-[11.5px] text-[#748095] sm:flex-row">
                    <span>&copy; 2026 Pache Logistic. Strictly Non-COD Logistics ERP.</span>
                    <a href="#top" class="font-semibold text-[#596579] no-underline hover:text-[#2563eb]">
                        Kembali ke atas ↑
                    </a>
                </div>
            </div>
        </footer>
    </div>
</template>
