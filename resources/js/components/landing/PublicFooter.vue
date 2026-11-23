<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Clock, Mail, MapPin, Phone } from '@lucide/vue';
import PacheLogo from '@/components/PacheLogo.vue';

const props = withDefaults(
    defineProps<{
        activePath?: string;
    }>(),
    {
        activePath: '/',
    },
);

function sectionHref(hash: string): string {
    return props.activePath === '/' ? hash : `/${hash}`;
}
</script>

<template>
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
                        Jaringan pengiriman kargo, ekspres, reguler, dan
                        pergudangan terintegrasi dengan pelacakan real-time dan
                        pembayaran Non-COD yang aman.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3
                        class="text-[12px] font-bold tracking-[0.1em] text-white uppercase"
                    >
                        Tautan Cepat
                    </h3>
                    <div
                        class="mt-4 grid grid-cols-2 gap-x-6 gap-y-2 text-[12.5px]"
                    >
                        <Link
                            href="/"
                            class="text-[#cbd5e1] no-underline hover:text-[#93c5fd]"
                            >Beranda</Link
                        >
                        <a
                            :href="sectionHref('#services')"
                            class="text-[#cbd5e1] no-underline hover:text-[#93c5fd]"
                            >Layanan</a
                        >
                        <a
                            :href="sectionHref('#values')"
                            class="text-[#cbd5e1] no-underline hover:text-[#93c5fd]"
                            >Nilai &amp; Cabang</a
                        >
                        <a
                            :href="sectionHref('#clients')"
                            class="text-[#cbd5e1] no-underline hover:text-[#93c5fd]"
                            >Mitra</a
                        >
                        <Link
                            href="/tracking"
                            class="text-[#cbd5e1] no-underline hover:text-[#93c5fd]"
                            >Cek Resi</Link
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
                            <Phone class="h-4 w-4 shrink-0 text-[#93c5fd]" />
                            <span>021-5501-8800</span>
                        </li>
                        <li class="flex list-none items-center gap-2.5">
                            <Mail class="h-4 w-4 shrink-0 text-[#93c5fd]" />
                            <span>ops@pachelogistic.id</span>
                        </li>
                        <li
                            class="flex list-none items-center gap-2.5 tabular-nums"
                        >
                            <Clock class="h-4 w-4 shrink-0 text-[#93c5fd]" />
                            <span>Senin - Sabtu : 08:00 - 18:00</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="flex flex-col items-center justify-between gap-3 pt-6 text-[11.5px] text-[#94a3b8] sm:flex-row"
            >
                <span
                    >&copy; 2026 Pache Logistic. Integrated Non-COD Logistics
                    &amp; Cargo.</span
                >
            </div>
        </div>
    </footer>
</template>
