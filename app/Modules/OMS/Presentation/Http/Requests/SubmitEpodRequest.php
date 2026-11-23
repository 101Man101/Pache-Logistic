<?php

declare(strict_types=1);

namespace App\Modules\OMS\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SubmitEpodRequest extends FormRequest
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
            'receiver_name' => ['required', 'string', 'max:150'],
            'receiver_relation' => ['required', 'string', 'max:60'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
