<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Enums;

/**
 * Strict Shipment State Machine (BR-03).
 */
enum ShipmentStatus: string
{
    case DRAFT = 'draft';
    case BOOKED = 'booked';
    case PICKED_UP = 'picked_up';
    case ORIGIN_HUB = 'origin_hub';
    case IN_TRANSIT = 'in_transit';
    case DESTINATION_HUB = 'destination_hub';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::BOOKED => 'Booked (AWB Terbit)',
            self::PICKED_UP => 'Picked Up',
            self::ORIGIN_HUB => 'Origin Sorting Hub',
            self::IN_TRANSIT => 'In Transit (Linehaul)',
            self::DESTINATION_HUB => 'Destination Hub',
            self::OUT_FOR_DELIVERY => 'Out for Delivery',
            self::DELIVERED => 'Delivered (e-POD)',
            self::CANCELLED => 'Cancelled',
        };
    }

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::DRAFT => [self::BOOKED, self::CANCELLED],
            self::BOOKED => [self::PICKED_UP, self::ORIGIN_HUB, self::CANCELLED],
            self::PICKED_UP => [self::ORIGIN_HUB, self::CANCELLED],
            self::ORIGIN_HUB => [self::IN_TRANSIT, self::CANCELLED],
            self::IN_TRANSIT => [self::DESTINATION_HUB],
            self::DESTINATION_HUB => [self::OUT_FOR_DELIVERY],
            self::OUT_FOR_DELIVERY => [self::DELIVERED],
            self::DELIVERED, self::CANCELLED => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }

    public function requiresPaymentClearance(): bool
    {
        return in_array($this, [
            self::IN_TRANSIT,
            self::DESTINATION_HUB,
            self::OUT_FOR_DELIVERY,
            self::DELIVERED,
        ], true);
    }
}
