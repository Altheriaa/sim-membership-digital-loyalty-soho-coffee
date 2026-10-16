<?php

namespace App\Http\Requests;

use App\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexStaffRequest extends FormRequest
{
    /**
     * Invalid filters send the visitor back to the unfiltered list, not to the previous page.
     */
    protected $redirectRoute = 'admin.staff.index';

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
            'role' => ['nullable', Rule::enum(Role::class)],
            'search' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function role(): ?Role
    {
        return $this->enum('role', Role::class);
    }

    public function search(): ?string
    {
        $term = trim($this->string('search')->toString());

        return $term === '' ? null : $term;
    }
}
