<?php

namespace App\Http\Requests;

use App\VoucherType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Only the field that belongs to the chosen type is kept, so a free-item voucher never carries a discount.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', Rule::enum(VoucherType::class)],
            'menu_item_id' => ['nullable', 'required_if:type,'.VoucherType::FreeItem->value, 'integer', Rule::exists('menu_items', 'id')],
            'discount_amount' => ['nullable', 'required_if:type,'.VoucherType::DiscountAmount->value, 'integer', 'min:1', 'max:100000000'],
            'points_cost' => ['required', 'integer', 'min:1', 'max:100000'],
            'quota' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'valid_days' => ['required', 'integer', 'min:1', 'max:365'],
            'available_from' => ['nullable', 'date'],
            'available_until' => ['nullable', 'date', 'after_or_equal:available_from'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'menu_item_id.required_if' => 'Pilih menu yang diberikan gratis.',
            'discount_amount.required_if' => 'Isi nominal diskon.',
            'available_until.after_or_equal' => 'Tanggal berakhir tidak boleh sebelum tanggal mulai.',
        ];
    }

    /**
     * The validated data with the field of the other type cleared.
     *
     * @return array<string, mixed>
     */
    public function voucherData(): array
    {
        $data = $this->safe()->except('is_active');
        $data['is_active'] = $this->boolean('is_active');

        if ($data['type'] === VoucherType::FreeItem->value) {
            $data['discount_amount'] = null;
        } else {
            $data['menu_item_id'] = null;
        }

        return $data;
    }
}
