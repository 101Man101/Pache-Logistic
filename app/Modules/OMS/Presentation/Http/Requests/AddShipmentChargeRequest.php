<?php

declare(strict_types=1);

namespace App\Modules\OMS\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AddShipmentChargeRequest extends FormRequest
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
            'surcharge_id' => ['nullable', 'integer', 'exists:surcharges,id'],
            'charge_type' => ['nullable', Rule::in(['surcharge', 'add_charge'])],
            'description' => ['nullable', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
