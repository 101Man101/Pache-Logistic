<?php

declare(strict_types=1);

namespace App\Modules\OMS\Application\UseCases;

use App\Models\Shipment;
use App\Modules\OMS\Domain\Repositories\ShipmentRepository;
use App\Modules\Shared\Domain\Contracts\FinancialLedgerRecorder;
use App\Modules\Shared\Domain\Contracts\TrackingLogger;
use App\Modules\Shared\Domain\Contracts\TransactionManager;
use App\Modules\Shared\Domain\Enums\PaymentMethod;
use App\Modules\Shared\Domain\Enums\PaymentStatus;
use DomainException;

final readonly class ConfirmShipmentPaymentUseCase
{
    public function __construct(
        private TransactionManager $transactions,
        private ShipmentRepository $shipments,
        private TrackingLogger $trackingLogger,
        private FinancialLedgerRecorder $ledgerRecorder,
    ) {}

    public function execute(Shipment $shipment, ?string $reference = null, ?int $actorId = null): Shipment
    {
        return $this->transactions->run(function () use ($shipment, $reference, $actorId): Shipment {
            $locked = $this->shipments->findForUpdateOrFail($shipment->id);

            if ($locked->payment_method === PaymentMethod::TOP) {
                throw new DomainException('Resi B2B TOP dilunasi melalui modul Invoice B2B, bukan kasir tunai/online.');
            }

            if ($locked->payment_status === PaymentStatus::PAID) {
                return $locked;
            }

            $ref = $reference ?: ($locked->payment_reference ?: 'PAY-'.strtoupper(substr(md5($locked->awb_number.now()->timestamp), 0, 8)));

            $locked->update([
                'payment_status' => PaymentStatus::PAID,
                'payment_reference' => $ref,
                'paid_at' => now(),
            ]);

            $channelDesc = $locked->payment_method === PaymentMethod::ONLINE
                ? "Pelunasan Online Payment (QRIS/VA Webhook) ref {$ref}"
                : "Konfirmasi penerimaan Cash Loket ref {$ref}";

            $this->ledgerRecorder->recordCredit(
                prefix: 'FIN',
                branchId: $locked->origin_branch_id,
                channel: $locked->payment_method->value,
                amount: (float) $locked->total_amount,
                referenceType: 'shipment',
                referenceId: $locked->id,
                description: "{$channelDesc} — {$locked->awb_number}",
                actorId: $actorId,
            );

            $location = $locked->originBranch ? "{$locked->originBranch->name} ({$locked->originBranch->city})" : 'Pache Logistic';
            $this->trackingLogger->record(
                shipment: $locked,
                status: $locked->status,
                location: $location,
                description: "{$channelDesc}. Resi lolos Non-COD Dispatch Gate (BR-08).",
                branchId: $locked->origin_branch_id,
                actorId: $actorId,
            );

            return $locked->refresh();
        });
    }
}
