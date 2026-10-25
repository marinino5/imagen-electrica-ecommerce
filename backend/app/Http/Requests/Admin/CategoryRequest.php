<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/** Crear y editar categorías (en edición, la categoría viene de la ruta). */
class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // El acceso (solo admin) lo controla el middleware de la ruta
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'parent_id' => [
                'nullable',
                'integer',
                // Solo dos niveles: el padre debe ser una categoría de primer nivel
                Rule::exists('categories', 'id')->whereNull('parent_id'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            /** @var Category|null $category */
            $category = $this->route('category');
            $parentId = $this->input('parent_id');

            if (! $category || ! $parentId) {
                return;
            }

            if ((int) $parentId === $category->id) {
                $validator->errors()->add('parent_id', 'Una categoría no puede ser su propio padre.');
            } elseif ($category->children()->exists()) {
                $validator->errors()->add('parent_id', 'Esta categoría tiene subcategorías, por eso debe ser de primer nivel.');
            }
        }];
    }
}
