<?php

declare(strict_types=1);

namespace App\Modules\FIN\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreVendorBillRequest extends FormRequest
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
            'vendor_id' => ['required', 'integer', 'exists:vendors,id'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'manifest_id' => ['nullable', 'integer', 'exists:manifests,id'],
            'vendor_invoice_no' => ['nullable', 'string', 'max:64'],
            'bill_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:bill_date'],
            'apply_ppn' => ['sometimes', 'boolean'],
            'apply_pph23' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.shipment_id' => ['nullable', 'integer', 'exists:shipments,id'],
            'lines.*.description' => ['required', 'string', 'max:255'],
            'lines.*.quantity' => ['required', 'numeric', 'gt:0'],
            'lines.*.unit' => ['nullable', 'string', 'max:24'],
            'lines.*.unit_price' => ['required', 'numeric', 'gte:0'],
        ];
    }
}
