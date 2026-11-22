<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Immutable Value Object for package volumetric & chargeable weight (BR-01).
 */
final readonly class Dimensions
{
    public function __construct(
        public float $lengthCm,
        public float $widthCm,
        public float $heightCm,
    ) {
        if ($lengthCm <= 0 || $widthCm <= 0 || $heightCm <= 0) {
            throw new InvalidArgumentException('Dimensi koli (P, L, T) wajib lebih besar dari nol.');
        }
    }

    public function volumetricWeightKg(int $divisor = 6000): float
    {
        if ($divisor <= 0) {
            throw new InvalidArgumentException('Pembagi volumetrik wajib lebih besar dari nol.');
        }

        return ($this->lengthCm * $this->widthCm * $this->heightCm) / $divisor;
    }

    public function chargeableWeightKg(float $actualWeightKg, int $divisor = 6000): float
    {
        if ($actualWeightKg <= 0) {
            throw new InvalidArgumentException('Berat aktual koli wajib lebih besar dari nol.');
        }

        return max(1.0, (float) ceil(max($actualWeightKg, $this->volumetricWeightKg($divisor))));
    }
}
