<?php

declare(strict_types=1);

namespace App\Modules\WMS\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class RecordStockMutationRequest extends FormRequest
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
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'storage_bin_id' => ['required', 'integer', 'exists:storage_bins,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'movement_type' => ['required', Rule::in(['inbound', 'outbound', 'adjustment'])],
            'quantity' => ['required', 'integer', 'not_in:0'],
            'reference_code' => ['required', 'string', 'max:64'],
            'batch_number' => ['nullable', 'string', 'max:48'],
            'expires_at' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
