<?php

declare(strict_types=1);

namespace App\Modules\TMS\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreManifestRequest extends FormRequest
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
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'driver_id' => ['required', 'integer', 'exists:drivers,id'],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'seal_number' => ['nullable', 'string', 'max:40'],
            'driver_advance_amount' => ['nullable', 'numeric', 'min:0'],
            'vendor_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
