<?php

namespace App\Providers;

use App\Modules\FIN\Domain\Repositories\InvoiceRepository;
use App\Modules\FIN\Domain\Repositories\VendorBillRepository;
use App\Modules\FIN\Infrastructure\Persistence\EloquentInvoiceRepository;
use App\Modules\FIN\Infrastructure\Persistence\EloquentVendorBillRepository;
use App\Modules\MDM\Domain\Repositories\MasterDataRepository;
use App\Modules\MDM\Infrastructure\Persistence\EloquentMasterDataRepository;
use App\Modules\OMS\Domain\Repositories\ShipmentRepository;
use App\Modules\OMS\Infrastructure\Persistence\EloquentShipmentRepository;
use App\Modules\Shared\Domain\Contracts\FinancialLedgerRecorder;
use App\Modules\Shared\Domain\Contracts\TrackingLogger;
use App\Modules\Shared\Domain\Contracts\TransactionManager;
use App\Modules\Shared\Infrastructure\Persistence\EloquentFinancialLedgerRecorder;
use App\Modules\Shared\Infrastructure\Persistence\EloquentTrackingLogger;
use App\Modules\Shared\Infrastructure\Persistence\LaravelTransactionManager;
use App\Modules\TMS\Domain\Repositories\ManifestRepository;
use App\Modules\TMS\Infrastructure\Persistence\EloquentManifestRepository;
use App\Modules\WMS\Domain\Repositories\InventoryLedgerRepository;
use App\Modules\WMS\Infrastructure\Persistence\EloquentInventoryLedgerRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TransactionManager::class, LaravelTransactionManager::class);
        $this->app->bind(TrackingLogger::class, EloquentTrackingLogger::class);
        $this->app->bind(FinancialLedgerRecorder::class, EloquentFinancialLedgerRecorder::class);
        $this->app->bind(ShipmentRepository::class, EloquentShipmentRepository::class);
        $this->app->bind(InventoryLedgerRepository::class, EloquentInventoryLedgerRepository::class);
        $this->app->bind(ManifestRepository::class, EloquentManifestRepository::class);
        $this->app->bind(InvoiceRepository::class, EloquentInvoiceRepository::class);
        $this->app->bind(VendorBillRepository::class, EloquentVendorBillRepository::class);
        $this->app->bind(MasterDataRepository::class, EloquentMasterDataRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
