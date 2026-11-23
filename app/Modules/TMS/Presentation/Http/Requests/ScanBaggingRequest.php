<?php

declare(strict_types=1);

namespace App\Modules\TMS\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ScanBaggingRequest extends FormRequest
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
            'awb_number' => ['required', 'string', 'max:40'],
        ];
    }
}
