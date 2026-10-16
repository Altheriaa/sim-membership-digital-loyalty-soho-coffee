<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class MenuItemRequest extends FormRequest
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
            'menu_category_id' => ['required', 'integer', Rule::exists('menu_categories', 'id')],
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'integer', 'min:0', 'max:100000000'],
            'is_available' => ['boolean'],
            'image' => ['nullable', File::image()->max(2 * 1024)],
            'remove_image' => ['boolean'],
        ];
    }
}
