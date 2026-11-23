<?php

declare(strict_types=1);

namespace App\Modules\FIN\Domain\Services;

use DomainException;

/**
 * Shared tax and line total calculator for AR Invoices & AP Vendor Bills.
 */
final readonly class InvoiceTotalsCalculator
{
    /**
     * @param  list<array{shipment_id?: int|null, description: string, quantity: float|int|string, unit?: string|null, unit_price: float|int|string}>  $items
     * @return array{
     *     lines: list<array{shipment_id: int|null, description: string, quantity: float, unit: string, unit_price: float, amount: float}>,
     *     subtotal: float,
     *     ppn_amount: float,
     *     pph23_amount: float,
     *     total_amount: float,
     * }
     */
    public function calculate(array $items, bool $applyPpn = true, bool $applyPph23 = true): array
    {
        if ($items === []) {
            throw new DomainException('Minimal harus ada 1 baris item pada dokumen tagihan.');
        }

        $normalized = [];
        $subtotal = 0.0;

        foreach ($items as $item) {
            $qty = round((float) $item['quantity'], 2);
            $unitPrice = round((float) $item['unit_price'], 2);

            if ($qty <= 0) {
                throw new DomainException('Kuantitas baris tagihan harus lebih besar dari 0.');
            }

            if ($unitPrice < 0) {
                throw new DomainException('Harga satuan baris tagihan tidak boleh negatif.');
            }

            $amount = round($qty * $unitPrice, 2);
            $subtotal += $amount;

            $unit = trim((string) ($item['unit'] ?? ''));

            $normalized[] = [
                'shipment_id' => isset($item['shipment_id']) && (int) $item['shipment_id'] > 0
                    ? (int) $item['shipment_id']
                    : null,
                'description' => trim((string) $item['description']),
                'quantity' => $qty,
                'unit' => $unit !== '' ? $unit : 'unit',
                'unit_price' => $unitPrice,
                'amount' => $amount,
            ];
        }

        $subtotal = round($subtotal, 2);
        $ppnAmount = $applyPpn ? round($subtotal * 0.11, 2) : 0.0;
        $pph23Amount = $applyPph23 ? round($subtotal * 0.02, 2) : 0.0;
        $totalAmount = round($subtotal + $ppnAmount - $pph23Amount, 2);

        return [
            'lines' => $normalized,
            'subtotal' => $subtotal,
            'ppn_amount' => $ppnAmount,
            'pph23_amount' => $pph23Amount,
            'total_amount' => $totalAmount,
        ];
    }
}
