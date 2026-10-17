<?php

namespace App\Http\Requests;

use App\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
{
    public const MAX_QUANTITY = 99;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Only ids and quantities come from the register. Prices, tax and points are worked out on the server.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.id' => ['required', 'integer', 'distinct', Rule::exists('menu_items', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:'.self::MAX_QUANTITY],
            'member_id' => ['nullable', 'integer', Rule::exists('members', 'id')],
            'voucher_id' => [
                'nullable',
                'integer',
                Rule::prohibitedIf(blank($this->input('member_id'))),
                Rule::exists('member_vouchers', 'id'),
            ],
            'voucher_pin' => ['required_with:voucher_id', 'nullable', 'digits:6'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'cash_received' => [
                Rule::requiredIf($this->input('payment_method') === PaymentMethod::Cash->value),
                Rule::prohibitedIf($this->input('payment_method') !== PaymentMethod::Cash->value),
                'nullable', 'integer', 'min:0', 'max:1000000000',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Keranjang masih kosong. Pilih minimal satu menu.',
            'items.min' => 'Keranjang masih kosong. Pilih minimal satu menu.',
            'items.*.id.exists' => 'Ada menu yang sudah dihapus dari daftar. Muat ulang halaman kasir.',
            'items.*.id.distinct' => 'Menu yang sama muncul dua kali di keranjang. Muat ulang halaman kasir.',
            'cash_received.required' => 'Isi uang yang diterima dari pelanggan.',
            'cash_received.prohibited' => 'Uang diterima hanya diisi untuk pembayaran tunai.',
            'voucher_id.prohibited_if' => 'Voucer hanya bisa dipakai untuk transaksi member. Pilih member dulu di panel member.',
            'voucher_id.exists' => 'Voucer tidak dikenali. Hapus voucer dari keranjang lalu cek kode lagi.',
            'voucher_pin.required_with' => 'PIN Keamanan member wajib diisi untuk memakai voucer.',
            'voucher_pin.digits' => 'PIN Keamanan harus terdiri dari 6 digit angka.',
        ];
    }
}
