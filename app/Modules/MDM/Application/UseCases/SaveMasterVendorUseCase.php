<?php

declare(strict_types=1);

namespace App\Modules\MDM\Application\UseCases;

use App\Models\Vendor;
use App\Modules\MDM\Domain\Repositories\MasterDataRepository;

final readonly class SaveMasterVendorUseCase
{
    public function __construct(
        private MasterDataRepository $masterData,
    ) {}

    /**
     * @param  array{
     *     code?: string|null,
     *     name: string,
     *     type: string,
     *     contact_person?: string|null,
     *     phone: string,
     *     email?: string|null,
     *     address?: string|null,
     *     payment_terms_days?: int|null,
     *     base_cost_per_kg?: float|null,
     *     notes?: string|null,
     *     is_active?: bool|null
     * }  $payload
     */
    public function execute(array $payload): Vendor
    {
        $rawCode = trim((string) ($payload['code'] ?? ''));
        $code = $rawCode !== '' ? strtoupper($rawCode) : $this->masterData->nextVendorCode();

        return $this->masterData->saveVendor($code, [
            'name' => $payload['name'],
            'type' => $payload['type'],
            'contact_person' => $payload['contact_person'] ?? null,
            'phone' => $payload['phone'],
            'email' => $payload['email'] ?? null,
            'address' => $payload['address'] ?? null,
            'payment_terms_days' => (int) ($payload['payment_terms_days'] ?? 14),
            'base_cost_per_kg' => (float) ($payload['base_cost_per_kg'] ?? 0),
            'notes' => $payload['notes'] ?? null,
            'is_active' => (bool) ($payload['is_active'] ?? true),
        ]);
    }

    public function toggle(Vendor $vendor): Vendor
    {
        return $this->masterData->toggleVendor($vendor);
    }
}
