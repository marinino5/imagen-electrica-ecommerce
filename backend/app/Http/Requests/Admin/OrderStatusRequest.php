<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Solo valida el formato; si la transición es válida lo decide OrderService. */
class OrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Admin y comercial: lo controla el middleware de la ruta
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function newStatus(): OrderStatus
    {
        return OrderStatus::from($this->validated('status'));
    }
}
