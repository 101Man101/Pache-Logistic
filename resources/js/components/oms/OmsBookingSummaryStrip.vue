<script setup lang="ts">
import { formatIdr } from '@/lib/format';

defineProps<{
    divisor: number;
    actualFormatted: string;
    volumetricFormatted: string;
    chargeableKg: number;
    surchargeAndAddChargeTotal: number;
    ratePerKg: number;
    estimatedTotal: number;
}>();
</script>

<template>
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div
            class="rounded-[14px] border border-[var(--hairline)] bg-[var(--surface-soft)] p-4"
        >
            <span class="text-[11.5px] font-semibold text-[var(--muted)]"
                >Berat Aktual / Volumetrik (÷{{ divisor }})</span
            >
            <p
                class="mt-1 text-[18px] font-bold text-[var(--ink)] tabular-nums"
            >
                {{ actualFormatted }} / {{ volumetricFormatted }} kg
            </p>
        </div>
        <div
            class="rounded-[14px] border border-[#d3e5ff] bg-[#dcecff] p-4 text-[#214f8a] dark:border-[#303030] dark:bg-[#1a1a1a] dark:text-[var(--ink)]"
        >
            <span class="text-[11.5px] font-semibold"
                >Chargeable Weight (BR-01)</span
            >
            <p class="mt-1 text-[22px] font-bold tabular-nums">
                {{ chargeableKg }} kg
            </p>
        </div>
        <div
            class="rounded-[14px] border border-[var(--hairline)] bg-[var(--surface-soft)] p-4"
        >
            <span class="text-[11.5px] font-semibold text-[var(--muted)]"
                >Surcharge &amp; Add Charge</span
            >
            <p
                class="mt-1 text-[20px] font-bold text-[var(--ink)] tabular-nums"
            >
                {{ formatIdr(surchargeAndAddChargeTotal) }}
            </p>
        </div>
        <div class="rounded-[14px] bg-[#2563eb] p-4 text-white">
            <span class="text-[11.5px] font-semibold text-blue-100">
                Estimasi Total ({{ formatIdr(ratePerKg) }}/kg)
            </span>
            <p class="mt-1 text-[22px] font-bold tabular-nums">
                {{ formatIdr(estimatedTotal) }}
            </p>
        </div>
    </div>
</template>
