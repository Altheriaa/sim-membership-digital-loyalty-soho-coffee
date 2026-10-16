<?php

namespace App\Http\Requests;

use App\Models\Tier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The base tier keeps a zero threshold, so every member always belongs to a tier.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Tier|null $tier */
        $tier = $this->route('tier');

        return [
            'name' => ['required', 'string', 'max:40', Rule::unique('tiers', 'name')->ignore($tier)],
            'min_spend' => [
                'required',
                'integer',
                'min:0',
                'max:9999999999',
                Rule::unique('tiers', 'min_spend')->ignore($tier),
                ...($tier?->isBase() ? [Rule::in([0])] : []),
            ],
            'multiplier' => ['required', 'numeric', 'decimal:0,2', 'between:1,10'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'min_spend.in' => 'Tier dasar harus tetap mulai dari Rp 0.',
            'min_spend.unique' => 'Sudah ada tier dengan syarat belanja yang sama.',
        ];
    }
}
