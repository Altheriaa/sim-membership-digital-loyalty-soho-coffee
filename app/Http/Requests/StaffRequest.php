<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Role and status stay fixed when staff edit their own account, so nobody can lock themselves out.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var User|null $staff */
        $staff = $this->route('user');
        $editingSelf = $staff?->is($this->user()) ?? false;

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($staff)],
            'role' => $editingSelf
                ? ['prohibited']
                : ['required', Rule::enum(Role::class)->only(Role::assignableByAdmin())],
            'is_active' => $editingSelf ? ['prohibited'] : ['boolean'],
            'password' => [$staff === null ? 'required' : 'nullable', 'string', Password::min(8)],
        ];
    }
}
