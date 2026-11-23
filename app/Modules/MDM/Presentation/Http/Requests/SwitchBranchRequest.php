<?php

declare(strict_types=1);

namespace App\Modules\MDM\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SwitchBranchRequest extends FormRequest
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
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
        ];
    }
}
