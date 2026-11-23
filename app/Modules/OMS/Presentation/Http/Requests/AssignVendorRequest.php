<?php

declare(strict_types=1);

namespace App\Modules\OMS\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AssignVendorRequest extends FormRequest
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
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'vendor_awb_no' => ['nullable', 'string', 'max:64'],
            'vendor_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
