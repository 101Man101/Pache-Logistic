<?php

declare(strict_types=1);

namespace App\Modules\MDM\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Surcharge;
use App\Models\Tariff;
use App\Models\Vendor;
use App\Modules\MDM\Application\UseCases\SaveMasterCustomerUseCase;
use App\Modules\MDM\Application\UseCases\SaveMasterSurchargeUseCase;
use App\Modules\MDM\Application\UseCases\SaveMasterTariffUseCase;
use App\Modules\MDM\Application\UseCases\SaveMasterVendorUseCase;
use App\Modules\MDM\Presentation\Http\Requests\StoreCustomerRequest;
use App\Modules\MDM\Presentation\Http\Requests\StoreSurchargeRequest;
use App\Modules\MDM\Presentation\Http\Requests\StoreTariffRequest;
use App\Modules\MDM\Presentation\Http\Requests\StoreVendorRequest;
use App\Modules\MDM\Presentation\Http\Requests\SwitchBranchRequest;
use App\Modules\OMS\Domain\Repositories\ShipmentRepository;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class MasterDataController extends Controller
{
    public function index(ShipmentRepository $shipments): Response
    {
        $branches = Branch::query()->orderBy('id')->get();
        $customers = Customer::query()->orderBy('name')->get()->map(static fn (Customer $c): array => [
            'id' => $c->id,
            'code' => $c->code,
            'name' => $c->name,
            'type' => $c->type,
            'npwp' => $c->npwp,
            'phone' => $c->phone,
            'email' => $c->email,
            'address' => $c->address,
            'is_top_enabled' => $c->is_top_enabled,
            'top_days' => $c->top_days,
            'credit_limit' => (float) $c->credit_limit,
            'credit_exposure' => $shipments->customerCreditExposure($c->id),
        ]);

        $tariffs = Tariff::query()
            ->with(['originBranch:id,code,city', 'destinationBranch:id,code,city'])
            ->orderBy('origin_branch_id')
            ->orderBy('destination_branch_id')
            ->orderBy('service_type')
            ->get()
            ->map(static fn (Tariff $t): array => [
                'id' => $t->id,
                'origin_branch_id' => $t->origin_branch_id,
                'destination_branch_id' => $t->destination_branch_id,
                'origin' => $t->originBranch ? "{$t->originBranch->code} ({$t->originBranch->city})" : '-',
                'destination' => $t->destinationBranch ? "{$t->destinationBranch->code} ({$t->destinationBranch->city})" : '-',
                'service_type' => $t->service_type,
                'volumetric_divisor' => $t->volumetric_divisor,
                'min_weight_kg' => (float) $t->min_weight_kg,
                'rate_per_kg' => (float) $t->rate_per_kg,
                'insurance_rate_pct' => (float) $t->insurance_rate_pct,
                'fuel_surcharge_pct' => (float) $t->fuel_surcharge_pct,
                'handling_fee' => (float) $t->handling_fee,
                'heavy_weight_threshold_kg' => $t->heavy_weight_threshold_kg !== null ? (float) $t->heavy_weight_threshold_kg : null,
                'heavy_weight_surcharge_per_kg' => (float) $t->heavy_weight_surcharge_per_kg,
                'sla_days' => $t->sla_days,
                'is_active' => $t->is_active,
            ]);

        $surcharges = Surcharge::query()
            ->orderBy('code')
            ->get()
            ->map(static fn (Surcharge $s): array => [
                'id' => $s->id,
                'code' => $s->code,
                'name' => $s->name,
                'calc_mode' => $s->calc_mode->value,
                'amount' => (float) $s->amount,
                'description' => $s->description,
                'is_active' => $s->is_active,
            ]);

        $vendors = Vendor::query()
            ->orderBy('name')
            ->get()
            ->map(static fn (Vendor $v): array => [
                'id' => $v->id,
                'code' => $v->code,
                'name' => $v->name,
                'type' => $v->type->value,
                'contact_person' => $v->contact_person,
                'phone' => $v->phone,
                'email' => $v->email,
                'address' => $v->address,
                'payment_terms_days' => $v->payment_terms_days,
                'base_cost_per_kg' => (float) $v->base_cost_per_kg,
                'notes' => $v->notes,
                'is_active' => $v->is_active,
            ]);

        return Inertia::render('master/Index', [
            'branches' => $branches,
            'customers' => $customers,
            'tariffs' => $tariffs,
            'surcharges' => $surcharges,
            'vendors' => $vendors,
        ]);
    }

    public function storeCustomer(StoreCustomerRequest $request, SaveMasterCustomerUseCase $useCase): RedirectResponse
    {
        /** @var array{name: string, type: string, npwp?: string|null, phone: string, email?: string|null, address: string, is_top_enabled?: bool, top_days?: int|null, credit_limit?: float|null} $validated */
        $validated = $request->validated();
        $customer = $useCase->execute($validated);

        return back()->with('success', "Pelanggan {$customer->name} berhasil ditambahkan.");
    }

    public function storeTariff(StoreTariffRequest $request, SaveMasterTariffUseCase $useCase): RedirectResponse
    {
        $v = $request->validated();
        $useCase->execute([
            'origin_branch_id' => (int) $v['origin_branch_id'],
            'destination_branch_id' => (int) $v['destination_branch_id'],
            'service_type' => (string) $v['service_type'],
            'volumetric_divisor' => (int) $v['volumetric_divisor'],
            'min_weight_kg' => (float) $v['min_weight_kg'],
            'rate_per_kg' => (float) $v['rate_per_kg'],
            'insurance_rate_pct' => isset($v['insurance_rate_pct']) ? (float) $v['insurance_rate_pct'] : null,
            'fuel_surcharge_pct' => isset($v['fuel_surcharge_pct']) ? (float) $v['fuel_surcharge_pct'] : null,
            'handling_fee' => isset($v['handling_fee']) ? (float) $v['handling_fee'] : null,
            'heavy_weight_threshold_kg' => isset($v['heavy_weight_threshold_kg']) && $v['heavy_weight_threshold_kg'] !== '' ? (float) $v['heavy_weight_threshold_kg'] : null,
            'heavy_weight_surcharge_per_kg' => isset($v['heavy_weight_surcharge_per_kg']) ? (float) $v['heavy_weight_surcharge_per_kg'] : null,
            'sla_days' => (int) $v['sla_days'],
            'is_active' => isset($v['is_active']) ? (bool) $v['is_active'] : null,
        ]);

        return back()->with('success', 'Pengaturan tarif & surcharge rute berhasil disimpan (Strictly Non-COD).');
    }

    public function toggleTariff(Tariff $tariff, SaveMasterTariffUseCase $useCase): RedirectResponse
    {
        $useCase->toggle($tariff);

        return back()->with('success', 'Status tarif rute diperbarui.');
    }

    public function storeSurcharge(StoreSurchargeRequest $request, SaveMasterSurchargeUseCase $useCase): RedirectResponse
    {
        $v = $request->validated();
        $surcharge = $useCase->execute([
            'code' => (string) $v['code'],
            'name' => (string) $v['name'],
            'calc_mode' => (string) $v['calc_mode'],
            'amount' => (float) $v['amount'],
            'description' => $v['description'] ?? null,
            'is_active' => isset($v['is_active']) ? (bool) $v['is_active'] : null,
        ]);

        return back()->with('success', "Aturan surcharge {$surcharge->code} berhasil disimpan.");
    }

    public function toggleSurcharge(Surcharge $surcharge, SaveMasterSurchargeUseCase $useCase): RedirectResponse
    {
        $useCase->toggle($surcharge);

        return back()->with('success', "Status surcharge {$surcharge->code} diperbarui.");
    }

    public function storeVendor(StoreVendorRequest $request, SaveMasterVendorUseCase $useCase): RedirectResponse
    {
        $v = $request->validated();
        $vendor = $useCase->execute([
            'code' => $v['code'] ?? null,
            'name' => (string) $v['name'],
            'type' => (string) $v['type'],
            'contact_person' => $v['contact_person'] ?? null,
            'phone' => (string) $v['phone'],
            'email' => $v['email'] ?? null,
            'address' => $v['address'] ?? null,
            'payment_terms_days' => isset($v['payment_terms_days']) ? (int) $v['payment_terms_days'] : null,
            'base_cost_per_kg' => isset($v['base_cost_per_kg']) ? (float) $v['base_cost_per_kg'] : null,
            'notes' => $v['notes'] ?? null,
            'is_active' => isset($v['is_active']) ? (bool) $v['is_active'] : null,
        ]);

        return back()->with('success', "Vendor {$vendor->name} ({$vendor->code}) berhasil disimpan.");
    }

    public function toggleVendor(Vendor $vendor, SaveMasterVendorUseCase $useCase): RedirectResponse
    {
        $useCase->toggle($vendor);

        return back()->with('success', "Status vendor {$vendor->name} diperbarui.");
    }

    public function switchBranch(SwitchBranchRequest $request): RedirectResponse
    {
        $request->session()->put('active_branch_id', (int) $request->validated('branch_id'));

        return back();
    }
}
