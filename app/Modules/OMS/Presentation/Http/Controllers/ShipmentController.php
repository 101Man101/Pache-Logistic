<?php

declare(strict_types=1);

namespace App\Modules\OMS\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Shipment;
use App\Models\ShipmentCharge;
use App\Models\Surcharge;
use App\Models\Tariff;
use App\Models\Vendor;
use App\Modules\OMS\Application\UseCases\AddShipmentChargeUseCase;
use App\Modules\OMS\Application\UseCases\AssignVendorToShipmentUseCase;
use App\Modules\OMS\Application\UseCases\BookShipmentUseCase;
use App\Modules\OMS\Application\UseCases\ConfirmShipmentPaymentUseCase;
use App\Modules\OMS\Application\UseCases\TransitionShipmentStatusUseCase;
use App\Modules\OMS\Domain\Repositories\ShipmentRepository;
use App\Modules\OMS\Presentation\Http\Requests\AddShipmentChargeRequest;
use App\Modules\OMS\Presentation\Http\Requests\AssignVendorRequest;
use App\Modules\OMS\Presentation\Http\Requests\StoreShipmentRequest;
use App\Modules\OMS\Presentation\Http\Requests\SubmitEpodRequest;
use App\Modules\OMS\Presentation\Http\Requests\TransitionShipmentStatusRequest;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use App\Modules\TMS\Application\UseCases\SubmitEpodUseCase;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ShipmentController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search', ''));
        $status = trim((string) $request->string('status', ''));
        $paymentMethod = trim((string) $request->string('payment_method', ''));
        $paymentStatus = trim((string) $request->string('payment_status', ''));

        $query = Shipment::query()
            ->with(['customer:id,name,code', 'originBranch:id,code,city', 'destinationBranch:id,code,city'])
            ->orderByDesc('created_at');

        if ($search !== '') {
            $query->where(static function ($q) use ($search): void {
                $q->where('awb_number', 'like', "%{$search}%")
                    ->orWhere('reference_no', 'like', "%{$search}%")
                    ->orWhere('consignee_name', 'like', "%{$search}%")
                    ->orWhere('consignee_phone', 'like', "%{$search}%");
            });
        }
        if ($status !== '') {
            $query->where('status', $status);
        }
        if ($paymentMethod !== '') {
            $query->where('payment_method', $paymentMethod);
        }
        if ($paymentStatus !== '') {
            $query->where('payment_status', $paymentStatus);
        }

        $shipments = $query->paginate(12)->withQueryString()->through(static fn (Shipment $s): array => [
            'id' => $s->id,
            'awb_number' => $s->awb_number,
            'reference_no' => $s->reference_no,
            'customer_name' => $s->customer->name ?? '-',
            'origin' => $s->originBranch ? "{$s->originBranch->code} ({$s->originBranch->city})" : '-',
            'destination' => $s->destinationBranch ? "{$s->destinationBranch->code} ({$s->destinationBranch->city})" : '-',
            'consignee_name' => $s->consignee_name,
            'service_type' => $s->service_type,
            'chargeable_weight_kg' => (float) $s->chargeable_weight_kg,
            'total_amount' => (float) $s->total_amount,
            'payment_method' => $s->payment_method->value,
            'payment_method_label' => $s->payment_method->shortLabel(),
            'payment_status' => $s->payment_status->value,
            'payment_status_label' => $s->payment_status->label(),
            'status' => $s->status->value,
            'status_label' => $s->status->label(),
            'created_at' => $s->created_at?->format('d M Y, H:i'),
        ]);

        return Inertia::render('oms/Index', [
            'filters' => [
                'search' => $search,
                'status' => $status,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
            ],
            'shipments' => $shipments,
        ]);
    }

    public function create(ShipmentRepository $shipmentRepository): Response
    {
        $customers = Customer::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(static fn (Customer $c): array => [
                'id' => $c->id,
                'code' => $c->code,
                'name' => $c->name,
                'type' => $c->type,
                'phone' => $c->phone,
                'address' => $c->address,
                'is_top_enabled' => $c->is_top_enabled,
                'top_days' => $c->top_days,
                'credit_limit' => (float) $c->credit_limit,
                'credit_exposure' => $shipmentRepository->customerCreditExposure($c->id),
            ]);

        $branches = Branch::query()->where('is_active', true)->orderBy('id')->get(['id', 'code', 'name', 'city']);

        $tariffs = Tariff::query()
            ->where('is_active', true)
            ->get([
                'origin_branch_id',
                'destination_branch_id',
                'service_type',
                'volumetric_divisor',
                'min_weight_kg',
                'rate_per_kg',
                'insurance_rate_pct',
                'fuel_surcharge_pct',
                'handling_fee',
                'heavy_weight_threshold_kg',
                'heavy_weight_surcharge_per_kg',
                'sla_days',
            ]);

        $vendors = Vendor::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'type', 'base_cost_per_kg'])
            ->map(static fn (Vendor $v): array => [
                'id' => $v->id,
                'code' => $v->code,
                'name' => $v->name,
                'type' => $v->type->value,
                'base_cost_per_kg' => (float) $v->base_cost_per_kg,
            ]);

        $surcharges = Surcharge::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'calc_mode', 'amount', 'description'])
            ->map(static fn (Surcharge $s): array => [
                'id' => $s->id,
                'code' => $s->code,
                'name' => $s->name,
                'calc_mode' => $s->calc_mode->value,
                'amount' => (float) $s->amount,
                'description' => $s->description,
            ]);

        return Inertia::render('oms/Create', [
            'customers' => $customers,
            'branches' => $branches,
            'tariffs' => $tariffs,
            'vendors' => $vendors,
            'surcharges' => $surcharges,
        ]);
    }

    public function store(StoreShipmentRequest $request, BookShipmentUseCase $bookShipment): RedirectResponse
    {
        try {
            /** @var array{customer_id: int, origin_branch_id: int, destination_branch_id: int, vendor_id?: int|null, vendor_awb_no?: string|null, vendor_cost?: float|null, reference_no?: string|null, shipper_name: string, shipper_phone: string, shipper_address: string, consignee_name: string, consignee_phone: string, consignee_address: string, service_type: string, payment_method: string, declared_value?: float|null, cash_received?: bool|null, surcharge_ids?: list<int>, additional_charges?: list<array{description: string, amount: float, notes?: string|null}>, items: list<array{description: string, qty: int, actual_weight_kg: float, length_cm: float, width_cm: float, height_cm: float}>} $payload */
            $payload = $request->validated();
            $shipment = $bookShipment->execute($payload, $request->user()?->id);
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return to_route('oms.shipments.show', $shipment->awb_number)
            ->with('success', "Resi {$shipment->awb_number} berhasil diterbitkan (Non-COD · {$shipment->payment_method->label()}).");
    }

    public function show(string $awb): Response
    {
        /** @var Shipment $shipment */
        $shipment = Shipment::query()
            ->with(['customer', 'originBranch', 'destinationBranch', 'vendor', 'items', 'charges', 'trackingLogs', 'invoiceLine'])
            ->where('awb_number', $awb)
            ->firstOrFail();

        $activeVendors = Vendor::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'type', 'base_cost_per_kg'])
            ->map(static fn (Vendor $v): array => [
                'id' => $v->id,
                'code' => $v->code,
                'name' => $v->name,
                'type' => $v->type->value,
                'base_cost_per_kg' => (float) $v->base_cost_per_kg,
            ]);

        $activeSurcharges = Surcharge::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'calc_mode', 'amount', 'description'])
            ->map(static fn (Surcharge $s): array => [
                'id' => $s->id,
                'code' => $s->code,
                'name' => $s->name,
                'calc_mode' => $s->calc_mode->value,
                'amount' => (float) $s->amount,
                'description' => $s->description,
            ]);

        return Inertia::render('oms/Show', [
            'shipment' => [
                'id' => $shipment->id,
                'awb_number' => $shipment->awb_number,
                'reference_no' => $shipment->reference_no,
                'customer_name' => $shipment->customer->name ?? '-',
                'customer_top_days' => $shipment->customer->top_days ?? 0,
                'origin' => $shipment->originBranch ? "{$shipment->originBranch->name} ({$shipment->originBranch->code})" : '-',
                'destination' => $shipment->destinationBranch ? "{$shipment->destinationBranch->name} ({$shipment->destinationBranch->code})" : '-',
                'vendor_id' => $shipment->vendor_id,
                'vendor' => $shipment->vendor ? [
                    'id' => $shipment->vendor->id,
                    'code' => $shipment->vendor->code,
                    'name' => $shipment->vendor->name,
                    'type' => $shipment->vendor->type->value,
                ] : null,
                'vendor_awb_no' => $shipment->vendor_awb_no,
                'vendor_cost' => (float) $shipment->vendor_cost,
                'shipper_name' => $shipment->shipper_name,
                'shipper_phone' => $shipment->shipper_phone,
                'shipper_address' => $shipment->shipper_address,
                'consignee_name' => $shipment->consignee_name,
                'consignee_phone' => $shipment->consignee_phone,
                'consignee_address' => $shipment->consignee_address,
                'service_type' => $shipment->service_type,
                'volumetric_divisor' => $shipment->volumetric_divisor,
                'actual_weight_kg' => (float) $shipment->actual_weight_kg,
                'volumetric_weight_kg' => (float) $shipment->volumetric_weight_kg,
                'chargeable_weight_kg' => (float) $shipment->chargeable_weight_kg,
                'shipping_cost' => (float) $shipment->shipping_cost,
                'insurance_cost' => (float) $shipment->insurance_cost,
                'surcharge_cost' => (float) $shipment->surcharge_cost,
                'additional_charge_cost' => (float) $shipment->additional_charge_cost,
                'total_amount' => (float) $shipment->total_amount,
                'is_invoiced' => $shipment->invoiceLine !== null,
                'payment_method' => $shipment->payment_method->value,
                'payment_method_label' => $shipment->payment_method->label(),
                'payment_status' => $shipment->payment_status->value,
                'payment_status_label' => $shipment->payment_status->label(),
                'payment_reference' => $shipment->payment_reference,
                'paid_at' => $shipment->paid_at?->format('d M Y, H:i'),
                'status' => $shipment->status->value,
                'status_label' => $shipment->status->label(),
                'allowed_transitions' => array_map(static fn (ShipmentStatus $s): array => [
                    'value' => $s->value,
                    'label' => $s->label(),
                ], $shipment->status->allowedTransitions()),
                'all_statuses' => array_values(array_map(
                    static fn (ShipmentStatus $s): array => [
                        'value' => $s->value,
                        'label' => $s->label(),
                    ],
                    array_filter(ShipmentStatus::cases(), static fn (ShipmentStatus $s): bool => $s !== ShipmentStatus::DRAFT),
                )),
                'epod' => $shipment->epod_receiver_name ? [
                    'receiver_name' => $shipment->epod_receiver_name,
                    'receiver_relation' => $shipment->epod_receiver_relation,
                    'latitude' => $shipment->epod_latitude,
                    'longitude' => $shipment->epod_longitude,
                    'notes' => $shipment->epod_notes,
                    'delivered_at' => $shipment->delivered_at?->format('d M Y, H:i'),
                ] : null,
                'items' => $shipment->items->map(static fn ($i): array => [
                    'id' => $i->id,
                    'description' => $i->description,
                    'qty' => $i->qty,
                    'actual_weight_kg' => (float) $i->actual_weight_kg,
                    'length_cm' => (float) $i->length_cm,
                    'width_cm' => (float) $i->width_cm,
                    'height_cm' => (float) $i->height_cm,
                ])->all(),
                'charges' => $shipment->charges->map(static fn (ShipmentCharge $c): array => [
                    'id' => $c->id,
                    'charge_type' => $c->charge_type,
                    'code' => $c->code,
                    'description' => $c->description,
                    'calc_mode' => $c->calc_mode,
                    'rate_value' => (float) $c->rate_value,
                    'quantity' => (float) $c->quantity,
                    'amount' => (float) $c->amount,
                    'notes' => $c->notes,
                ])->all(),
                'logs' => $shipment->trackingLogs->map(static fn ($l): array => [
                    'id' => $l->id,
                    'status' => $l->status,
                    'location' => $l->location,
                    'description' => $l->description,
                    'created_at' => $l->created_at->format('d M Y, H:i'),
                ])->all(),
            ],
            'activeVendors' => $activeVendors,
            'activeSurcharges' => $activeSurcharges,
        ]);
    }

    public function label(string $awb): Response
    {
        /** @var Shipment $shipment */
        $shipment = Shipment::query()
            ->with(['originBranch', 'destinationBranch', 'items'])
            ->where('awb_number', $awb)
            ->firstOrFail();

        return Inertia::render('oms/Label', [
            'shipment' => [
                'awb_number' => $shipment->awb_number,
                'reference_no' => $shipment->reference_no,
                'service_type' => $shipment->service_type,
                'origin_code' => $shipment->originBranch->code ?? 'JKT',
                'origin_city' => $shipment->originBranch->city ?? 'Jakarta',
                'dest_code' => $shipment->destinationBranch->code ?? 'SBY',
                'dest_city' => $shipment->destinationBranch->city ?? 'Surabaya',
                'shipper_name' => $shipment->shipper_name,
                'shipper_phone' => $shipment->shipper_phone,
                'shipper_address' => $shipment->shipper_address,
                'consignee_name' => $shipment->consignee_name,
                'consignee_phone' => $shipment->consignee_phone,
                'consignee_address' => $shipment->consignee_address,
                'chargeable_weight_kg' => (float) $shipment->chargeable_weight_kg,
                'colli_count' => (int) $shipment->items->sum('qty'),
                'payment_method' => $shipment->payment_method->value,
                'payment_method_label' => $shipment->payment_method->shortLabel(),
                'payment_status' => $shipment->payment_status->value,
                'payment_status_label' => $shipment->payment_status->label(),
                'created_at' => $shipment->created_at?->format('d M Y, H:i'),
            ],
        ]);
    }

    public function confirmPayment(Request $request, string $awb, ConfirmShipmentPaymentUseCase $useCase): RedirectResponse
    {
        $shipment = Shipment::query()->where('awb_number', $awb)->firstOrFail();

        try {
            $useCase->execute($shipment, $request->string('reference')->toString() ?: null, $request->user()?->id);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Pembayaran resi {$awb} terkonfirmasi LUNAS (PAID). Paket siap diberangkatkan.");
    }

    public function transitionStatus(
        TransitionShipmentStatusRequest $request,
        string $awb,
        TransitionShipmentStatusUseCase $useCase,
    ): RedirectResponse {
        $shipment = Shipment::query()->with(['originBranch', 'destinationBranch'])->where('awb_number', $awb)->firstOrFail();
        $validated = $request->validated();

        $nextStatus = ShipmentStatus::from((string) $validated['status']);
        $defaultLocation = match ($nextStatus) {
            ShipmentStatus::DESTINATION_HUB, ShipmentStatus::OUT_FOR_DELIVERY, ShipmentStatus::DELIVERED => $shipment->destinationBranch->name ?? 'Destination Hub',
            default => $shipment->originBranch->name ?? 'Origin Hub',
        };
        $locationInput = trim((string) ($validated['location'] ?? ''));
        $descriptionInput = trim((string) ($validated['description'] ?? ''));

        try {
            $useCase->execute(
                shipment: $shipment,
                nextStatus: $nextStatus,
                location: $locationInput !== '' ? $locationInput : $defaultLocation,
                description: $descriptionInput !== '' ? $descriptionInput : "Status resi diperbarui menjadi {$nextStatus->label()}.",
                branchId: $shipment->origin_branch_id,
                actorId: $request->user()?->id,
                allowManual: (bool) ($validated['manual'] ?? false),
            );
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Status resi {$awb} diperbarui ke {$nextStatus->label()}.");
    }

    public function submitEpod(SubmitEpodRequest $request, string $awb, SubmitEpodUseCase $useCase): RedirectResponse
    {
        $shipment = Shipment::query()->where('awb_number', $awb)->firstOrFail();
        $validated = $request->validated();

        try {
            $useCase->execute($shipment, [
                'receiver_name' => (string) $validated['receiver_name'],
                'receiver_relation' => (string) $validated['receiver_relation'],
                'latitude' => (float) $validated['latitude'],
                'longitude' => (float) $validated['longitude'],
                'notes' => $validated['notes'] ?? null,
            ], $request->user()?->id);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "e-POD Non-COD resi {$awb} tersimpan. Status menjadi Delivered.");
    }

    public function addCharge(
        AddShipmentChargeRequest $request,
        string $awb,
        AddShipmentChargeUseCase $useCase,
    ): RedirectResponse {
        $shipment = Shipment::query()->where('awb_number', $awb)->firstOrFail();
        $validated = $request->validated();

        try {
            $charge = $useCase->add($shipment, [
                'surcharge_id' => isset($validated['surcharge_id']) ? (int) $validated['surcharge_id'] : null,
                'charge_type' => $validated['charge_type'] ?? 'add_charge',
                'description' => $validated['description'] ?? null,
                'amount' => isset($validated['amount']) ? (float) $validated['amount'] : null,
                'notes' => $validated['notes'] ?? null,
            ], $request->user()?->id);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Biaya {$charge->description} berhasil ditambahkan ke {$awb}.");
    }

    public function removeCharge(string $awb, ShipmentCharge $charge, AddShipmentChargeUseCase $useCase): RedirectResponse
    {
        $shipment = Shipment::query()->where('awb_number', $awb)->firstOrFail();

        try {
            $useCase->remove($shipment, $charge);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Item biaya {$charge->description} dihapus dari {$awb}.");
    }

    public function assignVendor(
        AssignVendorRequest $request,
        string $awb,
        AssignVendorToShipmentUseCase $useCase,
    ): RedirectResponse {
        $shipment = Shipment::query()->where('awb_number', $awb)->firstOrFail();
        $validated = $request->validated();

        $vendor = $useCase->execute(
            shipment: $shipment,
            vendorId: isset($validated['vendor_id']) ? (int) $validated['vendor_id'] : null,
            vendorAwbNo: $validated['vendor_awb_no'] ?? null,
            vendorCost: (float) ($validated['vendor_cost'] ?? 0),
            actorId: $request->user()?->id,
        );

        return back()->with('success', $vendor !== null ? "Vendor {$vendor->name} ditugaskan ke {$awb}." : "Vendor dilepas dari {$awb}.");
    }
}
