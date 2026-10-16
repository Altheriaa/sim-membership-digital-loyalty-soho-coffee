<?php

namespace App\Http\Requests;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'phone' => $this->filled('phone') ? PhoneNumber::normalize($this->string('phone')) : null,
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'phone' => [
                'required',
                'string',
                'regex:'.PhoneNumber::PATTERN,
                Rule::unique('members', 'phone')->ignore($this->route('member')),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama panggilan wajib diisi.',
            'name.max' => 'Nama panggilan maksimal 50 karakter.',
            'phone.regex' => 'Nomor WhatsApp tidak dikenali. Ketik nomor HP Indonesia, misalnya 0812 3456 7890.',
            'phone.unique' => 'Nomor ini sudah terdaftar sebagai member. Cari nomornya untuk memakai akun yang ada.',
        ];
    }
}
