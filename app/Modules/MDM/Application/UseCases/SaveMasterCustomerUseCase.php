<?php

declare(strict_types=1);

namespace App\Modules\MDM\Application\UseCases;

use App\Models\Customer;
use App\Modules\MDM\Domain\Repositories\MasterDataRepository;

final readonly class SaveMasterCustomerUseCase
{
    public function __construct(
        private MasterDataRepository $masterData,
    ) {}

    /**
     * @param  array{
     *     name: string,
     *     type: string,
     *     npwp?: string|null,
     *     phone: string,
     *     email?: string|null,
     *     address: string,
     *     is_top_enabled?: bool,
     *     top_days?: int|null,
     *     credit_limit?: float|null
     * }  $payload
     */
    public function execute(array $payload): Customer
    {
        $isTop = $payload['type'] === 'b2b_contract' && (bool) ($payload['is_top_enabled'] ?? false);
        $prefix = $payload['type'] === 'b2b_contract' ? 'CUST-B2B-' : 'CUST-RTL-';

        return $this->masterData->createCustomer([
            'code' => $this->masterData->nextCustomerCode($prefix),
            'name' => $payload['name'],
            'type' => $payload['type'],
            'npwp' => $payload['npwp'] ?? null,
            'phone' => $payload['phone'],
            'email' => $payload['email'] ?? null,
            'address' => $payload['address'],
            'is_top_enabled' => $isTop,
            'top_days' => $isTop ? max(7, (int) ($payload['top_days'] ?? 14)) : 0,
            'credit_limit' => $isTop ? (float) ($payload['credit_limit'] ?? 0) : 0,
            'is_active' => true,
        ]);
    }
}
