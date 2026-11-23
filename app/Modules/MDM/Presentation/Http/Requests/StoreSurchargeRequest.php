<?php

declare(strict_types=1);

namespace App\Modules\MDM\Presentation\Http\Requests;

use App\Modules\Shared\Domain\Enums\SurchargeCalcMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreSurchargeRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:24'],
            'name' => ['required', 'string', 'max:120'],
            'calc_mode' => ['required', Rule::in(SurchargeCalcMode::values())],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
