<?php

declare(strict_types=1);

use App\Modules\Dashboard\Presentation\Http\Controllers\DashboardController;
use App\Modules\FIN\Presentation\Http\Controllers\FinanceController;
use App\Modules\MDM\Presentation\Http\Controllers\MasterDataController;
use App\Modules\OMS\Presentation\Http\Controllers\ShipmentController;
use App\Modules\PublicSite\Presentation\Http\Controllers\PublicTrackingController;
use App\Modules\TMS\Presentation\Http\Controllers\ManifestController;
use App\Modules\WMS\Presentation\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

// Public Website & Multi-AWB Tracking (FR-TRK-01)
Route::get('/', [PublicTrackingController::class, 'welcome'])->name('home');
Route::get('/tracking', [PublicTrackingController::class, 'track'])->name('public.tracking');

// Authenticated ERP Workspace (Pache Logistic)
Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/erp/search', [DashboardController::class, 'globalSearch'])->name('erp.search');
    Route::post('/master/switch-branch', [MasterDataController::class, 'switchBranch'])->name('master.switch-branch');

    // OMS — Order Management System (Strictly Non-COD)
    Route::prefix('oms')->name('oms.')->group(function (): void {
        Route::middleware('role:super_admin,admin_oms')->group(function (): void {
            Route::get('/shipments/create', [ShipmentController::class, 'create'])->name('shipments.create');
            Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');
            Route::post('/shipments/{awb}/charges', [ShipmentController::class, 'addCharge'])->name('shipments.charges.store');
            Route::delete('/shipments/{awb}/charges/{charge}', [ShipmentController::class, 'removeCharge'])->name('shipments.charges.destroy');
            Route::post('/shipments/{awb}/vendor', [ShipmentController::class, 'assignVendor'])->name('shipments.vendor.update');
        });

        Route::middleware('role:super_admin,admin_oms,finance_cashier,dispatcher_tms')->group(function (): void {
            Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
            Route::get('/shipments/{awb}', [ShipmentController::class, 'show'])->name('shipments.show');
            Route::get('/shipments/{awb}/label', [ShipmentController::class, 'label'])->name('shipments.label');
        });

        Route::post('/shipments/{awb}/confirm-payment', [ShipmentController::class, 'confirmPayment'])
            ->middleware('role:super_admin,admin_oms,finance_cashier')
            ->name('shipments.confirm-payment');

        Route::middleware('role:super_admin,admin_oms,dispatcher_tms')->group(function (): void {
            Route::post('/shipments/{awb}/transition', [ShipmentController::class, 'transitionStatus'])->name('shipments.transition');
            Route::post('/shipments/{awb}/epod', [ShipmentController::class, 'submitEpod'])->name('shipments.epod');
        });
    });

    // WMS — Warehouse Management & Immutable Inventory Ledger
    Route::prefix('wms')->name('wms.')->middleware('role:super_admin,operator_wms')->group(function (): void {
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/mutate', [InventoryController::class, 'mutate'])->name('inventory.mutate');
    });

    // TMS — Transportation, Manifests & Scan Bagging
    Route::prefix('tms')->name('tms.')->middleware('role:super_admin,dispatcher_tms')->group(function (): void {
        Route::get('/manifests', [ManifestController::class, 'index'])->name('manifests.index');
        Route::post('/manifests', [ManifestController::class, 'store'])->name('manifests.store');
        Route::post('/manifests/{manifest}/scan', [ManifestController::class, 'scanBagging'])->name('manifests.scan');
        Route::post('/manifests/{manifest}/depart', [ManifestController::class, 'depart'])->name('manifests.depart');
        Route::post('/manifests/{manifest}/arrive', [ManifestController::class, 'arrive'])->name('manifests.arrive');
    });

    // FIN — Finance Non-COD (Cash Loket, Online Payment PG, AR Invoices & AP Vendor Bills)
    Route::prefix('finance')->name('finance.')->middleware('role:super_admin,finance_cashier')->group(function (): void {
        Route::get('/', [FinanceController::class, 'index'])->name('index');
        Route::post('/invoices/generate', [FinanceController::class, 'generateInvoice'])->name('invoices.generate');
        Route::post('/invoices/manual', [FinanceController::class, 'storeManualInvoice'])->name('invoices.manual');
        Route::get('/invoices/{invoice}', [FinanceController::class, 'showInvoice'])->name('invoices.show');
        Route::get('/invoices/{invoice}/print', [FinanceController::class, 'printInvoice'])->name('invoices.print');
        Route::post('/invoices/{invoice}/mark-paid', [FinanceController::class, 'markPaid'])->name('invoices.mark-paid');

        Route::post('/vendor-bills', [FinanceController::class, 'storeVendorBill'])->name('vendor-bills.store');
        Route::get('/vendor-bills/{vendorBill}', [FinanceController::class, 'showVendorBill'])->name('vendor-bills.show');
        Route::get('/vendor-bills/{vendorBill}/print', [FinanceController::class, 'printVendorBill'])->name('vendor-bills.print');
        Route::post('/vendor-bills/{vendorBill}/mark-paid', [FinanceController::class, 'markVendorBillPaid'])->name('vendor-bills.mark-paid');
    });

    // MDM — Master Data (Multi-Branch, Customers TOP, Rate Engine, Surcharges, Vendors)
    Route::prefix('master')->name('master.')->middleware('role:super_admin,admin_oms,finance_cashier')->group(function (): void {
        Route::get('/', [MasterDataController::class, 'index'])->name('index');
        Route::post('/customers', [MasterDataController::class, 'storeCustomer'])->name('customers.store');
        Route::post('/tariffs', [MasterDataController::class, 'storeTariff'])->name('tariffs.store');
        Route::patch('/tariffs/{tariff}/toggle', [MasterDataController::class, 'toggleTariff'])->name('tariffs.toggle');
        Route::post('/surcharges', [MasterDataController::class, 'storeSurcharge'])->name('surcharges.store');
        Route::patch('/surcharges/{surcharge}/toggle', [MasterDataController::class, 'toggleSurcharge'])->name('surcharges.toggle');
        Route::post('/vendors', [MasterDataController::class, 'storeVendor'])->name('vendors.store');
        Route::patch('/vendors/{vendor}/toggle', [MasterDataController::class, 'toggleVendor'])->name('vendors.toggle');
    });
});

require __DIR__.'/settings.php';
