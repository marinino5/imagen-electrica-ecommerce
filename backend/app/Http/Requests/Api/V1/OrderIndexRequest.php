<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Http\Requests\Concerns\ReadsPerPage;
use App\Services\OrderService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Filtros y paginación de «mis pedidos». */
class OrderIndexRequest extends FormRequest
{
    use ReadsPerPage;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'type' => ['nullable', Rule::enum(OrderType::class)],
            'status' => ['nullable', Rule::enum(OrderStatus::class)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.OrderService::CUSTOMER_MAX_PER_PAGE],
        ];
    }

    /** @return array{type: ?string, status: ?string} */
    public function filters(): array
    {
        return ['type' => $this->validated('type'), 'status' => $this->validated('status')];
    }

    public function perPageValue(): int
    {
        return $this->perPage(OrderService::CUSTOMER_PER_PAGE, OrderService::CUSTOMER_MAX_PER_PAGE);
    }
}
