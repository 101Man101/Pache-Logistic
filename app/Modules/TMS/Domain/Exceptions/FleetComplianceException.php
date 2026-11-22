<?php

declare(strict_types=1);

namespace App\Modules\TMS\Domain\Exceptions;

use DomainException;

final class FleetComplianceException extends DomainException
{
    public static function expiredDocument(string $entity, string $documentName, string $expiredDate): self
    {
        return new self(
            "Penugasan ditolak (BR-06): dokumen {$documentName} untuk {$entity} telah kedaluwarsa pada {$expiredDate}."
        );
    }
}
