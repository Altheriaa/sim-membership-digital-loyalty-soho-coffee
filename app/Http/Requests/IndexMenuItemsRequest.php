<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexMenuItemsRequest extends FormRequest
{
    /**
     * Invalid filters send the visitor back to the unfiltered list, not to the previous page.
     */
    protected $redirectRoute = 'admin.menu-items.index';

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
            'category' => ['nullable', 'integer', Rule::exists('menu_categories', 'id')],
            'search' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function categoryId(): ?int
    {
        return $this->integer('category') ?: null;
    }

    public function search(): ?string
    {
        $term = trim($this->string('search')->toString());

        return $term === '' ? null : $term;
    }
}
