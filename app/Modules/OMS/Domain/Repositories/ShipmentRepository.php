<?php

declare(strict_types=1);

namespace App\Modules\OMS\Domain\Repositories;

use App\Models\Shipment;
use App\Models\Tariff;

interface ShipmentRepository
{
    public function findForUpdateOrFail(int $id): Shipment;

    public function findByAwbForUpdate(string $awbNumber): ?Shipment;

    public function nextDailySequence(string $prefix): int;

    public function findActiveTariff(int $originBranchId, int $destinationBranchId, string $serviceType): ?Tariff;

    public function customerCreditExposure(int $customerId): float;
}
