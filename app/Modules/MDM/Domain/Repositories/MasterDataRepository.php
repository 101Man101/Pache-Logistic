<?php

declare(strict_types=1);

namespace App\Modules\MDM\Domain\Repositories;

use App\Models\Customer;
use App\Models\Surcharge;
use App\Models\Tariff;
use App\Models\Vendor;

interface MasterDataRepository
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createCustomer(array $attributes): Customer;

    public function nextCustomerCode(string $prefix): string;

    /**
     * @param  array<string, mixed>  $keys
     * @param  array<string, mixed>  $attributes
     */
    public function saveTariff(array $keys, array $attributes): Tariff;

    public function toggleTariff(Tariff $tariff): Tariff;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function saveSurcharge(string $code, array $attributes): Surcharge;

    public function toggleSurcharge(Surcharge $surcharge): Surcharge;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function saveVendor(string $code, array $attributes): Vendor;

    public function nextVendorCode(): string;

    public function toggleVendor(Vendor $vendor): Vendor;
}
