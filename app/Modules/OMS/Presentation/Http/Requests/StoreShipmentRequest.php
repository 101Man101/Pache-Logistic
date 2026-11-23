<?php

declare(strict_types=1);

namespace App\Modules\OMS\Presentation\Http\Requests;

use App\Modules\Shared\Domain\Enums\PaymentMethod;
use App\Modules\Shared\Domain\Enums\ServiceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreShipmentRequest extends FormRequest
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
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'origin_branch_id' => ['required', 'integer', 'exists:branches,id'],
            'destination_branch_id' => ['required', 'integer', 'exists:branches,id', 'different:origin_branch_id'],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'vendor_awb_no' => ['nullable', 'string', 'max:64'],
            'vendor_cost' => ['nullable', 'numeric', 'min:0'],
            'reference_no' => ['nullable', 'string', 'max:64'],
            'shipper_name' => ['required', 'string', 'max:255'],
            'shipper_phone' => ['required', 'string', 'max:30'],
            'shipper_address' => ['required', 'string'],
            'consignee_name' => ['required', 'string', 'max:255'],
            'consignee_phone' => ['required', 'string', 'max:30'],
            'consignee_address' => ['required', 'string'],
            'service_type' => ['required', Rule::in(ServiceType::values())],
            'payment_method' => ['required', Rule::in(PaymentMethod::values())],
            'declared_value' => ['nullable', 'numeric', 'min:0'],
            'cash_received' => ['nullable', 'boolean'],
            'surcharge_ids' => ['nullable', 'array'],
            'surcharge_ids.*' => ['integer', 'exists:surcharges,id'],
            'additional_charges' => ['nullable', 'array'],
            'additional_charges.*.description' => ['required', 'string', 'max:255'],
            'additional_charges.*.amount' => ['required', 'numeric', 'min:1'],
            'additional_charges.*.notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.actual_weight_kg' => ['required', 'numeric', 'min:0.1'],
            'items.*.length_cm' => ['required', 'numeric', 'min:1'],
            'items.*.width_cm' => ['required', 'numeric', 'min:1'],
            'items.*.height_cm' => ['required', 'numeric', 'min:1'],
        ];
    }
}
