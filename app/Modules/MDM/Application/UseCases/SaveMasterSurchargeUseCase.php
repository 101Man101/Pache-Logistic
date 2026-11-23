<?php

declare(strict_types=1);

namespace App\Modules\MDM\Application\UseCases;

use App\Models\Surcharge;
use App\Modules\MDM\Domain\Repositories\MasterDataRepository;

final readonly class SaveMasterSurchargeUseCase
{
    public function __construct(
        private MasterDataRepository $masterData,
    ) {}

    /**
     * @param  array{
     *     code: string,
     *     name: string,
     *     calc_mode: string,
     *     amount: float,
     *     description?: string|null,
     *     is_active?: bool|null
     * }  $payload
     */
    public function execute(array $payload): Surcharge
    {
        $code = strtoupper(trim($payload['code']));

        return $this->masterData->saveSurcharge($code, [
            'name' => $payload['name'],
            'calc_mode' => $payload['calc_mode'],
            'amount' => $payload['amount'],
            'description' => $payload['description'] ?? null,
            'is_active' => (bool) ($payload['is_active'] ?? true),
        ]);
    }

    public function toggle(Surcharge $surcharge): Surcharge
    {
        return $this->masterData->toggleSurcharge($surcharge);
    }
}
