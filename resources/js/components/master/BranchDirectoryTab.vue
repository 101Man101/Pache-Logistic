<script setup lang="ts">
import type { BranchRow } from '@/types/erp';

defineProps<{
    branches: BranchRow[];
}>();
</script>

<template>
    <section class="pache-panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="pache-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Cabang / Hub</th>
                        <th>Tipe</th>
                        <th>Kota</th>
                        <th>Alamat &amp; Telepon</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="b in branches" :key="b.id">
                        <td class="font-bold text-[var(--brand-blue)]">
                            {{ b.code }}
                        </td>
                        <td class="font-semibold">{{ b.name }}</td>
                        <td
                            class="text-[11.5px] font-bold text-[var(--muted)] uppercase"
                        >
                            {{ b.type.replace('_', ' ') }}
                        </td>
                        <td>{{ b.city }}</td>
                        <td class="text-[13px] text-[var(--muted)]">
                            {{ b.address }}
                            <span v-if="b.phone" class="block tabular-nums">{{
                                b.phone
                            }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
