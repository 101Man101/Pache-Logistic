<?php

declare(strict_types=1);

namespace App\Modules\FIN\Application\UseCases;

use App\Models\Vendor;
use App\Models\VendorBill;
use App\Models\VendorBillLine;
use App\Modules\FIN\Domain\Repositories\VendorBillRepository;
use App\Modules\FIN\Domain\Services\InvoiceTotalsCalculator;
use App\Modules\Shared\Domain\Contracts\TransactionManager;
use DomainException;

/**
 * Records incoming Vendor Bill / Purchase Invoice (Accounts Payable).
 */
final readonly class CreateVendorBillUseCase
{
    public function __construct(
        private TransactionManager $transactions,
        private VendorBillRepository $vendorBills,
        private InvoiceTotalsCalculator $calculator,
    ) {}

    /**
     * @param  array{
     *     vendor_id: int,
     *     branch_id?: int|null,
     *     manifest_id?: int|null,
     *     vendor_invoice_no?: string|null,
     *     bill_date: string,
     *     due_date: string,
     *     apply_ppn: bool,
     *     apply_pph23: bool,
     *     notes?: string|null,
     *     lines: list<array{shipment_id?: int|null, description: string, quantity: float|int|string, unit?: string|null, unit_price: float|int|string}>,
     * }  $payload
     */
    public function execute(array $payload, ?int $actorId = null): VendorBill
    {
        return $this->transactions->run(function () use ($payload, $actorId): VendorBill {
            /** @var Vendor $vendor */
            $vendor = Vendor::query()->lockForUpdate()->findOrFail($payload['vendor_id']);

            if (! $vendor->is_active) {
                throw new DomainException("Vendor {$vendor->name} berstatus nonaktif.");
            }

            $totals = $this->calculator->calculate(
                $payload['lines'],
                applyPpn: (bool) $payload['apply_ppn'],
                applyPph23: (bool) $payload['apply_pph23'],
            );

            $shipmentIds = array_values(array_filter(array_map(
                static fn (array $line): ?int => $line['shipment_id'],
                $totals['lines'],
            )));

            if ($shipmentIds !== []) {
                $alreadyBilled = VendorBillLine::query()
                    ->whereIn('shipment_id', $shipmentIds)
                    ->exists();

                if ($alreadyBilled) {
                    throw new DomainException('Salah satu biaya vendor resi sudah pernah ditagihkan di Vendor Bill lain.');
                }
            }

            $prefix = 'AP-PCH-'.now()->format('Ym').'-';
            $seq = $this->vendorBills->nextSequence($prefix);
            $billNumber = $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

            return $this->vendorBills->createWithLines([
                'bill_number' => $billNumber,
                'vendor_invoice_no' => isset($payload['vendor_invoice_no']) && trim((string) $payload['vendor_invoice_no']) !== ''
                    ? trim((string) $payload['vendor_invoice_no'])
                    : null,
                'vendor_id' => $vendor->id,
                'branch_id' => isset($payload['branch_id']) && (int) $payload['branch_id'] > 0
                    ? (int) $payload['branch_id']
                    : null,
                'manifest_id' => isset($payload['manifest_id']) && (int) $payload['manifest_id'] > 0
                    ? (int) $payload['manifest_id']
                    : null,
                'bill_date' => $payload['bill_date'],
                'due_date' => $payload['due_date'],
                'subtotal' => $totals['subtotal'],
                'apply_ppn' => (bool) $payload['apply_ppn'],
                'apply_pph23' => (bool) $payload['apply_pph23'],
                'ppn_amount' => $totals['ppn_amount'],
                'pph23_amount' => $totals['pph23_amount'],
                'total_amount' => $totals['total_amount'],
                'status' => 'unpaid',
                'notes' => isset($payload['notes']) && trim((string) $payload['notes']) !== ''
                    ? trim((string) $payload['notes'])
                    : null,
                'created_by' => $actorId,
            ], $totals['lines']);
        });
    }
}
