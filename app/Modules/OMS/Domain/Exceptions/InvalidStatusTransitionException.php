<?php

declare(strict_types=1);

namespace App\Modules\OMS\Domain\Exceptions;

use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use DomainException;

final class InvalidStatusTransitionException extends DomainException
{
    public static function between(ShipmentStatus $from, ShipmentStatus $to): self
    {
        return new self(
            "Transisi status ilegal (BR-03): tidak dapat mengubah resi dari {$from->label()} ke {$to->label()}."
        );
    }
}
