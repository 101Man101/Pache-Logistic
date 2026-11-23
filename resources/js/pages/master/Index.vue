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
            <p class="pache-eyebrow">MASTER DATA MANAGEMENT (MDM)</p>
            <h1 class="pache-page-title">
                Pengaturan Tarif, Surcharge, Vendor 3PL, Pelanggan &amp; Cabang
            </h1>
        </div>

        <div class="pache-tab-bar">
            <button
                type="button"
                @click="activeTab = 'tariffs'"
                class="pache-tab-btn"
                :class="{ 'is-active': activeTab === 'tariffs' }"
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
                class="pache-tab-btn"
                :class="{ 'is-active': activeTab === 'surcharges' }"
            >
                <Percent class="h-4 w-4" />
                <span>Master Surcharge ({{ surcharges.length }})</span>
            </button>
            <button
                type="button"
                @click="activeTab = 'vendors'"
                class="pache-tab-btn"
                :class="{ 'is-active': activeTab === 'vendors' }"
            >
                <Handshake class="h-4 w-4" />
                <span>Vendor &amp; Mitra 3PL ({{ vendors.length }})</span>
            </button>
            <button
                type="button"
                @click="activeTab = 'customers'"
                class="pache-tab-btn"
                :class="{ 'is-active': activeTab === 'customers' }"
            >
                <Users class="h-4 w-4" />
                <span>Pelanggan &amp; TOP ({{ customers.length }})</span>
            </button>
            <button
                type="button"
                @click="activeTab = 'branches'"
                class="pache-tab-btn"
                :class="{ 'is-active': activeTab === 'branches' }"
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
