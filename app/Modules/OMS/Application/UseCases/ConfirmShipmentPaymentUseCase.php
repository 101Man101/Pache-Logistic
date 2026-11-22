<?php

declare(strict_types=1);

namespace App\Modules\OMS\Application\UseCases;

use App\Models\FinancialLedger;
use App\Models\Shipment;
use App\Models\TrackingLog;
use App\Modules\Shared\Domain\Enums\PaymentMethod;
use App\Modules\Shared\Domain\Enums\PaymentStatus;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class ConfirmShipmentPaymentUseCase
{
    public function execute(Shipment $shipment, ?string $reference = null, ?int $actorId = null): Shipment
    {
        return DB::transaction(function () use ($shipment, $reference, $actorId): Shipment {
            /** @var Shipment $locked */
            $locked = Shipment::query()->lockForUpdate()->with('originBranch')->findOrFail($shipment->id);

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

            FinancialLedger::query()->create([
                'entry_code' => 'FIN-'.now()->format('ymdHis').'-'.str_pad((string) $locked->id, 4, '0', STR_PAD_LEFT),
                'branch_id' => $locked->origin_branch_id,
                'channel' => $locked->payment_method->value,
                'direction' => 'credit',
                'amount' => $locked->total_amount,
                'reference_type' => 'shipment',
                'reference_id' => $locked->id,
                'description' => "{$channelDesc} — {$locked->awb_number}",
                'actor_id' => $actorId,
                'created_at' => now(),
            ]);

            TrackingLog::query()->create([
                'shipment_id' => $locked->id,
                'branch_id' => $locked->origin_branch_id,
                'status' => $locked->status->value,
                'location' => $locked->originBranch ? "{$locked->originBranch->name} ({$locked->originBranch->city})" : 'Pache Logistic',
                'description' => "{$channelDesc}. Resi lolos Non-COD Dispatch Gate (BR-08).",
                'actor_id' => $actorId,
                'created_at' => now(),
            ]);

            return $locked->refresh();
        });
    }
}
