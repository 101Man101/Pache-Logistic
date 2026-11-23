import { computed, type Ref } from 'vue';

export interface PackageColli {
    description: string;
    qty: number;
    actual_weight_kg: number;
    length_cm: number;
    width_cm: number;
    height_cm: number;
}

export function useChargeableWeight(
    items: Ref<PackageColli[]>,
    divisor: Ref<number>,
    minWeightKg: Ref<number>,
) {
    return computed(() => {
        const div = Math.max(1, divisor.value || 6000);
        const totalActual = items.value.reduce(
            (sum, item) =>
                sum +
                Math.max(0, Number(item.actual_weight_kg) || 0) *
                    Math.max(1, Number(item.qty) || 1),
            0,
        );

        const totalVolumetric = items.value.reduce((sum, item) => {
            const qty = Math.max(1, Number(item.qty) || 1);
            const vol =
                ((Math.max(0, Number(item.length_cm) || 0) *
                    Math.max(0, Number(item.width_cm) || 0) *
                    Math.max(0, Number(item.height_cm) || 0)) /
                    div) *
                qty;
            return sum + vol;
        }, 0);

        const rawChargeable = Math.ceil(Math.max(totalActual, totalVolumetric));
        const chargeableKg = Math.max(minWeightKg.value || 1, rawChargeable);

        return {
            actualKg: totalActual,
            volumetricKg: totalVolumetric,
            actualFormatted: totalActual.toFixed(2),
            volumetricFormatted: totalVolumetric.toFixed(2),
            chargeableKg,
            isVolumetricDominant: totalVolumetric > totalActual,
        };
    });
}
