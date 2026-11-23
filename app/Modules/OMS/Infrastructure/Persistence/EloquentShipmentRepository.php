<?php

declare(strict_types=1);

namespace App\Modules\OMS\Infrastructure\Persistence;

use App\Models\Invoice;
use App\Models\Shipment;
use App\Models\Tariff;
use App\Modules\OMS\Domain\Repositories\ShipmentRepository;
use App\Modules\Shared\Domain\Enums\PaymentMethod;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;

final readonly class EloquentShipmentRepository implements ShipmentRepository
{
    public function findForUpdateOrFail(int $id): Shipment
    {
        /** @var Shipment */
        return Shipment::query()
            ->with(['customer', 'originBranch', 'destinationBranch'])
            ->lockForUpdate()
            ->findOrFail($id);
    }

    public function findByAwbForUpdate(string $awbNumber): ?Shipment
    {
        return Shipment::query()
            ->where('awb_number', trim($awbNumber))
            ->lockForUpdate()
            ->first();
    }

    public function nextDailySequence(string $prefix): int
    {
        $lastAwb = Shipment::query()
            ->where('awb_number', 'like', "{$prefix}%")
            ->orderByDesc('awb_number')
            ->lockForUpdate()
            ->value('awb_number');

        return is_string($lastAwb) ? ((int) substr($lastAwb, -5) + 1) : 1;
    }

    public function findActiveTariff(int $originBranchId, int $destinationBranchId, string $serviceType): ?Tariff
    {
        return Tariff::query()
            ->where('origin_branch_id', $originBranchId)
            ->where('destination_branch_id', $destinationBranchId)
            ->where('service_type', $serviceType)
            ->where('is_active', true)
            ->first();
    }

    public function customerCreditExposure(int $customerId): float
    {
        $unpaidInvoices = (float) Invoice::query()
            ->where('customer_id', $customerId)
            ->whereIn('status', ['unpaid', 'overdue'])
            ->sum('total_amount');

        $unbilledShipments = (float) Shipment::query()
            ->where('customer_id', $customerId)
            ->where('payment_method', PaymentMethod::TOP->value)
            ->where('status', '!=', ShipmentStatus::CANCELLED->value)
            ->whereDoesntHave('invoiceLine')
            ->sum('total_amount');

        return $unpaidInvoices + $unbilledShipments;
    }
}
