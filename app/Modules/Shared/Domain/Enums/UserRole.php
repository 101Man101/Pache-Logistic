<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN_OMS = 'admin_oms';
    case FINANCE_CASHIER = 'finance_cashier';
    case OPERATOR_WMS = 'operator_wms';
    case DISPATCHER_TMS = 'dispatcher_tms';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin HO',
            self::ADMIN_OMS => 'Admin Loket OMS',
            self::FINANCE_CASHIER => 'Kasir & Finance',
            self::OPERATOR_WMS => 'Operator Gudang WMS',
            self::DISPATCHER_TMS => 'Dispatcher TMS',
        };
    }

    /**
     * @return list<string>
     */
    public function allowedModules(): array
    {
        return match ($this) {
            self::SUPER_ADMIN => ['oms', 'wms', 'tms', 'finance', 'master'],
            self::ADMIN_OMS => ['oms', 'master'],
            self::FINANCE_CASHIER => ['oms', 'finance', 'master'],
            self::OPERATOR_WMS => ['wms'],
            self::DISPATCHER_TMS => ['oms', 'tms'],
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
