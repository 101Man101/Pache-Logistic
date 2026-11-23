<?php

declare(strict_types=1);

namespace App\Modules\OMS\Presentation\Http\Requests;

use App\Modules\Shared\Domain\Enums\ShipmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class TransitionShipmentStatusRequest extends FormRequest
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
            'status' => ['required', Rule::in(array_map(static fn (ShipmentStatus $s): string => $s->value, ShipmentStatus::cases()))],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'manual' => ['nullable', 'boolean'],
        ];
    }
}
