<?php

declare(strict_types=1);

namespace App\Modules\FIN\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class MarkDocumentPaidRequest extends FormRequest
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
            'payment_reference' => ['nullable', 'string', 'max:80'],
        ];
    }
}
