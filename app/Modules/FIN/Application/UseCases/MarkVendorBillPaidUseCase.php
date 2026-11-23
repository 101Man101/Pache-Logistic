<?php

declare(strict_types=1);

namespace App\Modules\FIN\Application\UseCases;

use App\Models\VendorBill;
use App\Modules\FIN\Domain\Repositories\VendorBillRepository;
use App\Modules\Shared\Domain\Contracts\FinancialLedgerRecorder;
use App\Modules\Shared\Domain\Contracts\TransactionManager;

final readonly class MarkVendorBillPaidUseCase
{
    public function __construct(
        private TransactionManager $transactions,
        private VendorBillRepository $vendorBills,
        private FinancialLedgerRecorder $ledgerRecorder,
    ) {}

    public function execute(VendorBill $vendorBill, ?int $actorId = null, ?string $paymentReference = null): VendorBill
    {
        return $this->transactions->run(function () use ($vendorBill, $actorId, $paymentReference): VendorBill {
            $locked = $this->vendorBills->findForUpdateOrFail($vendorBill->id);

            if ($locked->status === 'paid') {
                return $locked;
            }

            $cleanRef = $paymentReference !== null && trim($paymentReference) !== ''
                ? trim($paymentReference)
                : null;

            $locked->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_reference' => $cleanRef,
            ]);

            $this->ledgerRecorder->recordDebit(
                prefix: 'FIN-VBL',
                branchId: $locked->branch_id,
                channel: 'vendor_ap',
                amount: (float) $locked->total_amount,
                referenceType: 'vendor_bill',
                referenceId: $locked->id,
                description: "Pembayaran Tagihan Vendor {$locked->bill_number} ({$locked->vendor?->name})",
                actorId: $actorId,
            );

            return $locked->refresh();
        });
    }
}
