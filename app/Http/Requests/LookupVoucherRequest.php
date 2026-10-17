<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LookupVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('code')) {
            $this->merge(['code' => strtoupper(trim($this->string('code')))]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'regex:/^KOP-[0-9A-Z]{6}$/'],
            'member_id' => ['required', 'integer', Rule::exists('members', 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode voucer wajib diisi.',
            'code.regex' => 'Format kode voucer tidak dikenali. Kode voucer berbentuk KOP-XXXXXX, contohnya KOP-A1B2C3.',
            'member_id.required' => 'Voucer hanya bisa dipakai untuk transaksi member. Pilih member dulu di panel member.',
            'member_id.exists' => 'Member tidak dikenali. Muat ulang halaman kasir lalu pilih member lagi.',
        ];
    }
}
