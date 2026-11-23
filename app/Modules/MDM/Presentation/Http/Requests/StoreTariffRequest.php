<?php

declare(strict_types=1);

namespace App\Modules\MDM\Presentation\Http\Requests;

use App\Modules\Shared\Domain\Enums\ServiceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreTariffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'origin_branch_id' => ['required', 'integer', 'exists:branches,id'],
            'destination_branch_id' => ['required', 'integer', 'exists:branches,id', 'different:origin_branch_id'],
            'service_type' => ['required', Rule::in(ServiceType::values())],
            'volumetric_divisor' => ['required', 'integer', Rule::in([4000, 5000, 6000])],
            'min_weight_kg' => ['required', 'numeric', 'min:1'],
            'rate_per_kg' => ['required', 'numeric', 'min:1000'],
            'insurance_rate_pct' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'fuel_surcharge_pct' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'handling_fee' => ['nullable', 'numeric', 'min:0'],
            'heavy_weight_threshold_kg' => ['nullable', 'numeric', 'min:1'],
            'heavy_weight_surcharge_per_kg' => ['nullable', 'numeric', 'min:0'],
            'sla_days' => ['required', 'integer', 'min:1', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
