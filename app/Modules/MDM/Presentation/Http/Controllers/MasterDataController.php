<?php

declare(strict_types=1);

namespace App\Modules\MDM\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Tariff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

final class MasterDataController extends Controller
{
    public function index(): Response
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
            'credit_exposure' => $c->currentCreditExposure(),
        ]);

        $tariffs = Tariff::query()
            ->with(['originBranch:id,code,city', 'destinationBranch:id,code,city'])
            ->orderBy('origin_branch_id')
            ->orderBy('destination_branch_id')
            ->get()
            ->map(static fn (Tariff $t): array => [
                'id' => $t->id,
                'origin' => $t->originBranch ? "{$t->originBranch->code} ({$t->originBranch->city})" : '-',
                'destination' => $t->destinationBranch ? "{$t->destinationBranch->code} ({$t->destinationBranch->city})" : '-',
                'service_type' => $t->service_type,
                'volumetric_divisor' => $t->volumetric_divisor,
                'min_weight_kg' => (float) $t->min_weight_kg,
                'rate_per_kg' => (float) $t->rate_per_kg,
                'insurance_rate_pct' => (float) $t->insurance_rate_pct,
                'sla_days' => $t->sla_days,
            ]);

        return Inertia::render('master/Index', [
            'branches' => $branches,
            'customers' => $customers,
            'tariffs' => $tariffs,
        ]);
    }

    public function storeCustomer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['retail', 'b2b_contract'])],
            'npwp' => ['nullable', 'string', 'max:32'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['required', 'string'],
            'is_top_enabled' => ['boolean'],
            'top_days' => ['nullable', 'integer', 'min:0', 'max:90'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
        ]);

        $isTop = $validated['type'] === 'b2b_contract' && (bool) ($validated['is_top_enabled'] ?? false);
        $prefix = $validated['type'] === 'b2b_contract' ? 'CUST-B2B-' : 'CUST-RTL-';
        $seq = Customer::query()->count() + 1;

        Customer::query()->create([
            'code' => $prefix.str_pad((string) $seq, 3, '0', STR_PAD_LEFT),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'npwp' => $validated['npwp'] ?? null,
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'],
            'is_top_enabled' => $isTop,
            'top_days' => $isTop ? max(7, (int) ($validated['top_days'] ?? 14)) : 0,
            'credit_limit' => $isTop ? (float) ($validated['credit_limit'] ?? 0) : 0,
            'is_active' => true,
        ]);

        return back()->with('success', "Pelanggan {$validated['name']} berhasil ditambahkan.");
    }

    public function storeTariff(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'origin_branch_id' => ['required', 'integer', 'exists:branches,id'],
            'destination_branch_id' => ['required', 'integer', 'exists:branches,id', 'different:origin_branch_id'],
            'service_type' => ['required', Rule::in(['REG', 'EXP', 'CRG'])],
            'volumetric_divisor' => ['required', 'integer', Rule::in([4000, 5000, 6000])],
            'min_weight_kg' => ['required', 'numeric', 'min:1'],
            'rate_per_kg' => ['required', 'numeric', 'min:1000'],
            'sla_days' => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        Tariff::query()->updateOrCreate(
            [
                'origin_branch_id' => (int) $validated['origin_branch_id'],
                'destination_branch_id' => (int) $validated['destination_branch_id'],
                'service_type' => $validated['service_type'],
            ],
            [
                'volumetric_divisor' => (int) $validated['volumetric_divisor'],
                'min_weight_kg' => (float) $validated['min_weight_kg'],
                'rate_per_kg' => (float) $validated['rate_per_kg'],
                'insurance_rate_pct' => 0.25,
                'sla_days' => (int) $validated['sla_days'],
                'is_active' => true,
            ]
        );

        return back()->with('success', 'Matriks tarif berhasil disimpan (Non-COD, tanpa biaya admin COD).');
    }

    public function switchBranch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
        ]);

        $request->session()->put('active_branch_id', (int) $validated['branch_id']);

        return back();
    }
}
