<?php

declare(strict_types=1);

namespace App\Modules\MDM\Presentation\Http\Requests;

use App\Modules\Shared\Domain\Enums\VendorType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreVendorRequest extends FormRequest
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
            'code' => ['nullable', 'string', 'max:24'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(VendorType::values())],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'payment_terms_days' => ['nullable', 'integer', 'min:0', 'max:90'],
            'base_cost_per_kg' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
