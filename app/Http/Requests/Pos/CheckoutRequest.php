<?php

namespace App\Http\Requests\Pos;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'items' => ['required', 'array'],
            'items.*.price' => ['required', 'numeric'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'tax_rate' => ['required', 'numeric'],
            'use_points' => ['sometimes', 'boolean'],
            'voucher_code' => ['sometimes', 'string', 'max:20'],
        ];
    }
}
