<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\DeliveryMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Envío del carrito. El servidor decide si sale pedido, cotización o ambos. */
class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // El rol cliente lo controla el middleware de la ruta
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $customer = $this->user()?->customer;
        $needsAddress = $this->input('delivery_method') !== DeliveryMethod::Pickup->value && blank($customer?->address);

        return [
            'items' => ['required', 'array', 'min:1', 'max:100'],
            // Solo productos activos y no eliminados
            'items.*.product_id' => [
                'required', 'integer', 'distinct',
                Rule::exists('products', 'id')->where('is_active', true)->whereNull('deleted_at'),
            ],
            'items.*.quantity' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:1000000'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
            'delivery_method' => ['required', Rule::enum(DeliveryMethod::class)],
            // Si el cliente no tiene dirección guardada, hay que enviarla para domicilios
            'address' => [Rule::requiredIf($needsAddress), 'nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'items.required' => 'El carrito está vacío.',
            'items.min' => 'El carrito está vacío.',
            'items.*.product_id.exists' => 'Uno de los productos ya no está disponible.',
            'items.*.product_id.distinct' => 'Un producto no puede repetirse en el carrito: suma las cantidades en una sola línea.',
            'address.required' => 'Escribe la dirección de entrega.',
        ];
    }
}
