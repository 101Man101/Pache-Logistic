<?php

declare(strict_types=1);

namespace App\Modules\TMS\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Driver;
use App\Models\Manifest;
use App\Models\Shipment;
use App\Models\Vehicle;
use App\Models\Vendor;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use App\Modules\TMS\Application\UseCases\ArriveManifestUseCase;
use App\Modules\TMS\Application\UseCases\AssignShipmentToManifestUseCase;
use App\Modules\TMS\Application\UseCases\CreateManifestUseCase;
use App\Modules\TMS\Application\UseCases\DepartManifestUseCase;
use App\Modules\TMS\Presentation\Http\Requests\ScanBaggingRequest;
use App\Modules\TMS\Presentation\Http\Requests\StoreManifestRequest;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ManifestController extends Controller
{
    public function index(): Response
    {
        $manifests = Manifest::query()
            ->with([
                'originBranch:id,code,name,city',
                'destinationBranch:id,code,name,city',
                'vehicle:id,plate_number,type,capacity_kg',
                'driver:id,name,phone',
                'vendor:id,code,name',
                'items.shipment:id,awb_number,chargeable_weight_kg,payment_method,payment_status,status',
            ])
            ->orderByDesc('created_at')
            ->get()
            ->map(static fn (Manifest $m): array => [
                'id' => $m->id,
                'manifest_number' => $m->manifest_number,
                'origin' => $m->originBranch ? "{$m->originBranch->code} ({$m->originBranch->city})" : '-',
                'destination' => $m->destinationBranch ? "{$m->destinationBranch->code} ({$m->destinationBranch->city})" : '-',
                'destination_branch_id' => $m->destination_branch_id,
                'vehicle' => $m->vehicle ? "{$m->vehicle->plate_number} · {$m->vehicle->type}" : '-',
                'capacity_kg' => (float) ($m->vehicle->capacity_kg ?? 0),
                'driver_name' => $m->driver->name ?? '-',
                'vendor_name' => $m->vendor->name ?? null,
                'vendor_cost' => (float) $m->vendor_cost,
                'seal_number' => $m->seal_number,
                'driver_advance_amount' => (float) $m->driver_advance_amount,
                'status' => $m->status->value,
                'departed_at' => $m->departed_at?->format('d M Y, H:i'),
                'arrived_at' => $m->arrived_at?->format('d M Y, H:i'),
                'total_weight_kg' => (float) $m->items->sum(static fn ($i): float => (float) ($i->shipment->chargeable_weight_kg ?? 0)),
                'items' => $m->items->map(static fn ($i): array => [
                    'id' => $i->id,
                    'awb_number' => $i->shipment->awb_number ?? '-',
                    'weight_kg' => (float) ($i->shipment->chargeable_weight_kg ?? 0),
                    'payment_method' => $i->shipment?->payment_method->shortLabel() ?? '-',
                    'payment_status' => $i->shipment?->payment_status->value ?? '-',
                    'status' => $i->shipment?->status->label() ?? '-',
                ])->all(),
            ]);

        $vehicles = Vehicle::query()->with('branch:id,code')->orderBy('plate_number')->get()->map(static fn (Vehicle $v): array => [
            'id' => $v->id,
            'plate_number' => $v->plate_number,
            'type' => $v->type,
            'capacity_kg' => (float) $v->capacity_kg,
            'stnk_expires_at' => $v->stnk_expires_at->format('d M Y'),
            'kir_expires_at' => $v->kir_expires_at->format('d M Y'),
            'is_expired' => $v->stnk_expires_at->isPast() || $v->kir_expires_at->isPast(),
            'expiring_soon' => ! $v->stnk_expires_at->isPast() && ! $v->kir_expires_at->isPast()
                && ($v->stnk_expires_at->lte(now()->addDays(30)) || $v->kir_expires_at->lte(now()->addDays(30))),
            'status' => $v->status,
        ]);

        $drivers = Driver::query()->orderBy('name')->get()->map(static fn (Driver $d): array => [
            'id' => $d->id,
            'name' => $d->name,
            'phone' => $d->phone,
            'sim_number' => $d->sim_number,
            'sim_expires_at' => $d->sim_expires_at->format('d M Y'),
            'is_expired' => $d->sim_expires_at->isPast(),
        ]);

        $readyShipments = Shipment::query()
            ->with('destinationBranch:id,code,city')
            ->whereIn('status', [ShipmentStatus::BOOKED->value, ShipmentStatus::PICKED_UP->value, ShipmentStatus::ORIGIN_HUB->value])
            ->whereDoesntHave('manifestItems')
            ->orderByDesc('id')
            ->get()
            ->map(static fn (Shipment $s): array => [
                'awb_number' => $s->awb_number,
                'destination' => $s->destinationBranch ? "{$s->destinationBranch->code} ({$s->destinationBranch->city})" : '-',
                'payment_method' => $s->payment_method->shortLabel(),
                'payment_status' => $s->payment_status->value,
                'cleared' => $s->payment_status->isClearedForDispatch(),
            ]);

        $vendors = Vendor::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'type'])
            ->map(static fn (Vendor $v): array => [
                'id' => $v->id,
                'code' => $v->code,
                'name' => $v->name,
                'type' => $v->type->value,
            ]);

        return Inertia::render('tms/Index', [
            'manifests' => $manifests,
            'branches' => Branch::query()->where('is_active', true)->orderBy('id')->get(['id', 'code', 'name', 'city']),
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'vendors' => $vendors,
            'readyShipments' => $readyShipments,
        ]);
    }

    public function store(StoreManifestRequest $request, CreateManifestUseCase $useCase): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $manifest = $useCase->execute([
                'origin_branch_id' => (int) $validated['origin_branch_id'],
                'destination_branch_id' => (int) $validated['destination_branch_id'],
                'vehicle_id' => (int) $validated['vehicle_id'],
                'driver_id' => (int) $validated['driver_id'],
                'vendor_id' => isset($validated['vendor_id']) ? (int) $validated['vendor_id'] : null,
                'seal_number' => $validated['seal_number'] ?? null,
                'driver_advance_amount' => (float) ($validated['driver_advance_amount'] ?? 0),
                'vendor_cost' => (float) ($validated['vendor_cost'] ?? 0),
            ], $request->user()?->id);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Manifest {$manifest->manifest_number} berhasil dibuat. Siap untuk Scan Bagging.");
    }

    public function scanBagging(
        ScanBaggingRequest $request,
        Manifest $manifest,
        AssignShipmentToManifestUseCase $useCase,
    ): RedirectResponse {
        $awbNumber = (string) $request->validated('awb_number');

        try {
            $useCase->execute($manifest, $awbNumber, $request->user()?->id);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Scan Bagging sukses: {$awbNumber} masuk ke {$manifest->manifest_number}.");
    }

    public function depart(Request $request, Manifest $manifest, DepartManifestUseCase $useCase): RedirectResponse
    {
        $updated = $useCase->execute($manifest, $request->user()?->id);

        return back()->with('success', "Manifest {$updated->manifest_number} diberangkatkan (InTransit).");
    }

    public function arrive(Request $request, Manifest $manifest, ArriveManifestUseCase $useCase): RedirectResponse
    {
        $updated = $useCase->execute($manifest, $request->user()?->id);

        return back()->with('success', "Manifest {$updated->manifest_number} tiba di Hub Tujuan (DestinationHub).");
    }
}
