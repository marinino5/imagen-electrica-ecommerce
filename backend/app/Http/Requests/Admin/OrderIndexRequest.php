<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Filtros del listado de solicitudes, todos aplicables desde la URL:
 * ?type=quote&status=new,in_review&overdue=1&q=texto
 * Un valor desconocido se ignora (en vez de dar error) para que un enlace
 * viejo no deje la página inutilizable.
 */
class OrderIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['q' => ['nullable', 'string', 'max:100']];
    }

    /** @return array{type: ?string, status: array<int, string>, overdue: bool, q: ?string} */
    public function filters(): array
    {
        $statuses = collect(explode(',', (string) $this->query('status')))
            ->map(fn (string $value) => OrderStatus::tryFrom(trim($value))?->value)
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [
            'type' => $this->enum('type', OrderType::class)?->value,
            'status' => $statuses,
            'overdue' => $this->boolean('overdue'),
            'q' => trim((string) $this->validated('q')) ?: null,
        ];
    }
}
