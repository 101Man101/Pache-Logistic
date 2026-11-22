<?php

declare(strict_types=1);

namespace App\Modules\OMS\Domain\Exceptions;

use DomainException;

final class UnpaidShipmentDispatchException extends DomainException
{
    public static function forAwb(string $awbNumber): self
    {
        return new self(
            "Diblokir Non-COD Dispatch Gate (BR-08): Resi {$awbNumber} masih berstatus PENDING / belum lunas. Lunasi via Online Payment atau Kasir Loket sebelum masuk Manifest/Transit."
        );
    }
}
