<?php

declare(strict_types=1);

namespace App\Modules\MDM\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreCustomerRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['retail', 'b2b_contract'])],
            'npwp' => ['nullable', 'string', 'max:32'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['required', 'string'],
            'is_top_enabled' => ['boolean'],
            'top_days' => ['nullable', 'integer', 'min:0', 'max:90'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
