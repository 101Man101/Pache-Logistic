<?php

declare(strict_types=1);

namespace App\Modules\OMS\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Shipment;
use App\Models\Tariff;
use App\Modules\OMS\Application\UseCases\BookShipmentUseCase;
use App\Modules\OMS\Application\UseCases\ConfirmShipmentPaymentUseCase;
use App\Modules\OMS\Application\UseCases\TransitionShipmentStatusUseCase;
use App\Modules\Shared\Domain\Enums\PaymentMethod;
use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use App\Modules\TMS\Application\UseCases\SubmitEpodUseCase;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

    public function create(): Response
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
                'credit_exposure' => $c->currentCreditExposure(),
            ]);

        $branches = Branch::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'code', 'name', 'city']);

        $tariffs = Tariff::query()
            ->where('is_active', true)
            ->get(['origin_branch_id', 'destination_branch_id', 'service_type', 'volumetric_divisor', 'min_weight_kg', 'rate_per_kg', 'insurance_rate_pct', 'sla_days']);

        return Inertia::render('oms/Create', [
            'customers' => $customers,
            'branches' => $branches,
            'tariffs' => $tariffs,
        ]);
    }

    public function store(Request $request, BookShipmentUseCase $bookShipment): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'origin_branch_id' => ['required', 'integer', 'exists:branches,id'],
            'destination_branch_id' => ['required', 'integer', 'exists:branches,id', 'different:origin_branch_id'],
            'reference_no' => ['nullable', 'string', 'max:64'],
            'shipper_name' => ['required', 'string', 'max:255'],
            'shipper_phone' => ['required', 'string', 'max:30'],
            'shipper_address' => ['required', 'string'],
            'consignee_name' => ['required', 'string', 'max:255'],
            'consignee_phone' => ['required', 'string', 'max:30'],
            'consignee_address' => ['required', 'string'],
            'service_type' => ['required', Rule::in(['REG', 'EXP', 'CRG'])],
            // Strictly Non-COD validation — only online, cash, top accepted
            'payment_method' => ['required', Rule::in(PaymentMethod::values())],
            'declared_value' => ['nullable', 'numeric', 'min:0'],
            'cash_received' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.actual_weight_kg' => ['required', 'numeric', 'min:0.1'],
            'items.*.length_cm' => ['required', 'numeric', 'min:1'],
            'items.*.width_cm' => ['required', 'numeric', 'min:1'],
            'items.*.height_cm' => ['required', 'numeric', 'min:1'],
        ]);

        try {
            $shipment = $bookShipment->execute($validated, $request->user()?->id);
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
            ->with(['customer', 'originBranch', 'destinationBranch', 'items', 'trackingLogs'])
            ->where('awb_number', $awb)
            ->firstOrFail();

        return Inertia::render('oms/Show', [
            'shipment' => [
                'id' => $shipment->id,
                'awb_number' => $shipment->awb_number,
                'reference_no' => $shipment->reference_no,
                'customer_name' => $shipment->customer->name ?? '-',
                'customer_top_days' => $shipment->customer->top_days ?? 0,
                'origin' => $shipment->originBranch ? "{$shipment->originBranch->name} ({$shipment->originBranch->code})" : '-',
                'destination' => $shipment->destinationBranch ? "{$shipment->destinationBranch->name} ({$shipment->destinationBranch->code})" : '-',
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
                'total_amount' => (float) $shipment->total_amount,
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
                'logs' => $shipment->trackingLogs->map(static fn ($l): array => [
                    'id' => $l->id,
                    'status' => $l->status,
                    'location' => $l->location,
                    'description' => $l->description,
                    'created_at' => $l->created_at->format('d M Y, H:i'),
                ])->all(),
            ],
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

    public function transitionStatus(Request $request, string $awb, TransitionShipmentStatusUseCase $useCase): RedirectResponse
    {
        $shipment = Shipment::query()->with(['originBranch', 'destinationBranch'])->where('awb_number', $awb)->firstOrFail();

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_map(static fn (ShipmentStatus $s): string => $s->value, ShipmentStatus::cases()))],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $nextStatus = ShipmentStatus::from($validated['status']);
        $defaultLocation = match ($nextStatus) {
            ShipmentStatus::DESTINATION_HUB, ShipmentStatus::OUT_FOR_DELIVERY, ShipmentStatus::DELIVERED => $shipment->destinationBranch->name ?? 'Destination Hub',
            default => $shipment->originBranch->name ?? 'Origin Hub',
        };

        try {
            $useCase->execute(
                shipment: $shipment,
                nextStatus: $nextStatus,
                location: $validated['location'] ?: $defaultLocation,
                description: $validated['description'] ?: "Status resi diperbarui menjadi {$nextStatus->label()}.",
                branchId: $shipment->origin_branch_id,
                actorId: $request->user()?->id,
            );
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Status resi {$awb} diperbarui ke {$nextStatus->label()}.");
    }

    public function submitEpod(Request $request, string $awb, SubmitEpodUseCase $useCase): RedirectResponse
    {
        $shipment = Shipment::query()->where('awb_number', $awb)->firstOrFail();

        $validated = $request->validate([
            'receiver_name' => ['required', 'string', 'max:150'],
            'receiver_relation' => ['required', 'string', 'max:60'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $useCase->execute($shipment, [
                'receiver_name' => $validated['receiver_name'],
                'receiver_relation' => $validated['receiver_relation'],
                'latitude' => (float) $validated['latitude'],
                'longitude' => (float) $validated['longitude'],
                'notes' => $validated['notes'] ?? null,
            ], $request->user()?->id);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "e-POD Non-COD resi {$awb} tersimpan. Status menjadi Delivered.");
    }
}
