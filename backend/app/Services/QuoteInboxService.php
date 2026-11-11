<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\QuoteRequestStatus;
use App\Repositories\QuoteInboxRepository;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;

/** Bandeja única de cotizaciones (con y sin cuenta): quita al controlador la mezcla de las dos fuentes. */
class QuoteInboxService
{
    public const PER_PAGE = 15;

    public function __construct(private readonly QuoteInboxRepository $repository) {}

    /**
     * @param  array{q?: ?string, origin?: ?string, state?: ?string}  $filters
     * @return LengthAwarePaginator<int, object> cada fila con code, status_label, origin_label, url, overdue y created_at (Carbon)
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $cutoff = CarbonImmutable::now()->subHours(OrderService::QUOTE_RESPONSE_HOURS);
        $page = $this->repository->paginate($filters, self::PER_PAGE);

        $page->getCollection()->transform(fn (object $row) => $this->present($row, $cutoff));

        return $page;
    }

    /** Filas sin responder, la más antigua primero (para «Requiere tu atención»). */
    public function pending(int $limit = 6): \Illuminate\Support\Collection
    {
        $cutoff = CarbonImmutable::now()->subHours(OrderService::QUOTE_RESPONSE_HOURS);

        return $this->repository->pending($limit)->map(fn (object $row) => $this->present($row, $cutoff));
    }

    public function countPending(): int
    {
        return $this->repository->countPending();
    }

    private function present(object $row, CarbonImmutable $cutoff): object
    {
        $isOrder = $row->source === 'order';
        $row->created_at = CarbonImmutable::parse($row->created_at);
        $row->code = $isOrder ? $row->code : sprintf('SOL-%04d', $row->id);
        $row->origin_label = $isOrder ? 'Con cuenta' : 'Sin cuenta';
        $row->status_label = $isOrder ? OrderStatus::from($row->status)->label() : QuoteRequestStatus::from($row->status)->label();
        $row->pending = in_array($row->status, QuoteInboxRepository::PENDING[$row->source], true);
        $row->overdue = $row->pending && $row->created_at->lt($cutoff);
        $row->url = $isOrder ? route('admin.orders.show', $row->id) : route('admin.quote-requests.show', $row->id);

        return $row;
    }
}
