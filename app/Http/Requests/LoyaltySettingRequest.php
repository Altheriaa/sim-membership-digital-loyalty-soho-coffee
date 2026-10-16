<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoyaltySettingRequest extends FormRequest
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
            'spend_per_point' => ['required', 'integer', 'min:1', 'max:100000000'],
            'point_value' => ['required', 'integer', 'min:1', 'max:100000000'],
            'tax_percent' => ['required', 'numeric', 'decimal:0,2', 'between:0,100'],
        ];
    }
}
