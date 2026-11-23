<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Building2, Handshake, Map, Percent, Users } from '@lucide/vue';
import { ref } from 'vue';
import BranchDirectoryTab from '@/components/master/BranchDirectoryTab.vue';
import CustomerDirectoryTab from '@/components/master/CustomerDirectoryTab.vue';
import SurchargeCatalogTab from '@/components/master/SurchargeCatalogTab.vue';
import TariffSettingsTab from '@/components/master/TariffSettingsTab.vue';
import VendorDirectoryTab from '@/components/master/VendorDirectoryTab.vue';
import type {
    BranchRow,
    CustomerRow,
    SurchargeRow,
    TariffRow,
    VendorRow,
} from '@/types/erp';

defineProps<{
    branches: BranchRow[];
    customers: CustomerRow[];
    tariffs: TariffRow[];
    surcharges: SurchargeRow[];
    vendors: VendorRow[];
}>();

const activeTab = ref<
    'customers' | 'tariffs' | 'surcharges' | 'vendors' | 'branches'
>('tariffs');
</script>

<template>
    <Head title="Master Data, Tarif, Surcharge & Vendor" />

    <div class="space-y-6">
        <div>
            <p
                class="text-[10.5px] font-bold tracking-[0.14em] text-[var(--brand-blue)] uppercase"
            >
                MASTER DATA MANAGEMENT (MDM)
            </p>
            <h1
                class="mt-1 text-[23px] font-[650] tracking-[-0.03em] text-[var(--ink)]"
            >
                Pengaturan Tarif, Surcharge, Vendor 3PL, Pelanggan &amp; Cabang
            </h1>
        </div>

        <div
            class="inline-flex flex-wrap gap-1 rounded-[12px] bg-[var(--surface-soft)] p-1"
        >
            <button
                type="button"
                @click="activeTab = 'tariffs'"
                class="inline-flex items-center gap-2 rounded-[9px] px-4 py-2 text-[13px] font-semibold"
                :class="
                    activeTab === 'tariffs'
                        ? 'bg-[var(--surface)] text-[var(--brand-blue)] shadow-xs'
                        : 'text-[var(--muted)]'
                "
            >
                <Map class="h-4 w-4" />
                <span
                    >Setting Tarif &amp; Surcharge Rute ({{
                        tariffs.length
                    }})</span
                >
            </button>
            <button
                type="button"
                @click="activeTab = 'surcharges'"
                class="inline-flex items-center gap-2 rounded-[9px] px-4 py-2 text-[13px] font-semibold"
                :class="
                    activeTab === 'surcharges'
                        ? 'bg-[var(--surface)] text-[var(--brand-blue)] shadow-xs'
                        : 'text-[var(--muted)]'
                "
            >
                <Percent class="h-4 w-4" />
                <span>Master Surcharge ({{ surcharges.length }})</span>
            </button>
            <button
                type="button"
                @click="activeTab = 'vendors'"
                class="inline-flex items-center gap-2 rounded-[9px] px-4 py-2 text-[13px] font-semibold"
                :class="
                    activeTab === 'vendors'
                        ? 'bg-[var(--surface)] text-[var(--brand-blue)] shadow-xs'
                        : 'text-[var(--muted)]'
                "
            >
                <Handshake class="h-4 w-4" />
                <span>Vendor &amp; Mitra 3PL ({{ vendors.length }})</span>
            </button>
            <button
                type="button"
                @click="activeTab = 'customers'"
                class="inline-flex items-center gap-2 rounded-[9px] px-4 py-2 text-[13px] font-semibold"
                :class="
                    activeTab === 'customers'
                        ? 'bg-[var(--surface)] text-[var(--brand-blue)] shadow-xs'
                        : 'text-[var(--muted)]'
                "
            >
                <Users class="h-4 w-4" />
                <span>Pelanggan &amp; TOP ({{ customers.length }})</span>
            </button>
            <button
                type="button"
                @click="activeTab = 'branches'"
                class="inline-flex items-center gap-2 rounded-[9px] px-4 py-2 text-[13px] font-semibold"
                :class="
                    activeTab === 'branches'
                        ? 'bg-[var(--surface)] text-[var(--brand-blue)] shadow-xs'
                        : 'text-[var(--muted)]'
                "
            >
                <Building2 class="h-4 w-4" />
                <span>Cabang ({{ branches.length }})</span>
            </button>
        </div>

        <TariffSettingsTab
            v-if="activeTab === 'tariffs'"
            :branches="branches"
            :tariffs="tariffs"
        />
        <SurchargeCatalogTab
            v-else-if="activeTab === 'surcharges'"
            :surcharges="surcharges"
        />
        <VendorDirectoryTab
            v-else-if="activeTab === 'vendors'"
            :vendors="vendors"
        />
        <CustomerDirectoryTab
            v-else-if="activeTab === 'customers'"
            :customers="customers"
        />
        <BranchDirectoryTab v-else :branches="branches" />
    </div>
</template>
