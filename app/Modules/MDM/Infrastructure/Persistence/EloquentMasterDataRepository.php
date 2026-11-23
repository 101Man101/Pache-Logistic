<?php

declare(strict_types=1);

namespace App\Modules\MDM\Infrastructure\Persistence;

use App\Models\Customer;
use App\Models\Surcharge;
use App\Models\Tariff;
use App\Models\Vendor;
use App\Modules\MDM\Domain\Repositories\MasterDataRepository;

final readonly class EloquentMasterDataRepository implements MasterDataRepository
{
    public function createCustomer(array $attributes): Customer
    {
        /** @var Customer */
        return Customer::query()->create($attributes);
    }

    public function nextCustomerCode(string $prefix): string
    {
        $seq = Customer::query()->count() + 1;

        return $prefix.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }

    public function saveTariff(array $keys, array $attributes): Tariff
    {
        /** @var Tariff */
        return Tariff::query()->updateOrCreate($keys, $attributes);
    }

    public function toggleTariff(Tariff $tariff): Tariff
    {
        $tariff->update(['is_active' => ! $tariff->is_active]);

        return $tariff;
    }

    public function saveSurcharge(string $code, array $attributes): Surcharge
    {
        /** @var Surcharge */
        return Surcharge::query()->updateOrCreate(['code' => $code], $attributes);
    }

    public function toggleSurcharge(Surcharge $surcharge): Surcharge
    {
        $surcharge->update(['is_active' => ! $surcharge->is_active]);

        return $surcharge;
    }

    public function saveVendor(string $code, array $attributes): Vendor
    {
        /** @var Vendor */
        return Vendor::query()->updateOrCreate(['code' => $code], $attributes);
    }

    public function nextVendorCode(): string
    {
        $seq = Vendor::query()->count() + 1;

        return 'VND-3PL-'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }

    public function toggleVendor(Vendor $vendor): Vendor
    {
        $vendor->update(['is_active' => ! $vendor->is_active]);

        return $vendor;
    }
}
