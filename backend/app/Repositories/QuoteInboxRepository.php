<?php

namespace App\Repositories;

use App\Enums\OrderType;
use App\Repositories\Concerns\EscapesLike;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Bandeja única de cotizaciones: las que llegan de clientes con cuenta (orders con type = quote) y las que llegan sin cuenta
 * (quote_requests) en un solo listado, con una etiqueta de origen. Se arma con UNION y se pagina sobre el resultado.
 */
class QuoteInboxRepository
{
    use EscapesLike;

    /** Estados «sin responder» de cada origen. */
    public const PENDING = ['order' => ['new', 'in_review'], 'free' => ['new']];

    /** @param array{q?: ?string, origin?: ?string, state?: ?string} $filters */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        $like = ($filters['q'] ?? null) ? $this->likePattern($filters['q']) : null;
        $origin = $filters['origin'] ?? null;
        $state = $filters['state'] ?? null;

        $orders = DB::table('orders')
            ->where('type', OrderType::Quote->value)
            ->selectRaw("'order' as source, id, code, coalesce(company_name, contact_name) as client, contact_name, email, phone, city, status, total, assigned_to, created_at");
        $free = DB::table('quote_requests')
            ->selectRaw("'free' as source, id, null as code, coalesce(company, name) as client, name as contact_name, email, phone, city, status, null as total, null as assigned_to, created_at");

        if ($like) {
            $orders->where(fn (Builder $q) => $q->where('code', 'like', $like)->orWhere('company_name', 'like', $like)->orWhere('contact_name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('nit', 'like', $like));
            $free->where(fn (Builder $q) => $q->where('company', 'like', $like)->orWhere('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('phone', 'like', $like)->orWhere('nit', 'like', $like));
        }

        if ($state === 'pending') {
            $orders->whereIn('status', self::PENDING['order']);
            $free->whereIn('status', self::PENDING['free']);
        } elseif ($state === 'answered') {
            $orders->whereIn('status', ['sent', 'accepted', 'rejected']);
            $free->whereIn('status', ['contacted', 'closed']);
        } elseif ($state === 'cancelled') {
            $orders->where('status', 'cancelled');
            $free->whereRaw('1 = 0');
        }

        $query = match ($origin) {
            'account' => $orders,
            'free' => $free,
            default => $orders->unionAll($free),
        };

        return DB::query()->fromSub($query, 'q')->orderByDesc('created_at')->orderByDesc('id')->paginate($perPage)->withQueryString();
    }

    /** Cotizaciones de las dos fuentes sin responder (para el dashboard). */
    public function pending(int $limit): \Illuminate\Support\Collection
    {
        $orders = DB::table('orders')->where('type', OrderType::Quote->value)->whereIn('status', self::PENDING['order'])
            ->selectRaw("'order' as source, id, code, coalesce(company_name, contact_name) as client, status, total, created_at");
        $free = DB::table('quote_requests')->whereIn('status', self::PENDING['free'])
            ->selectRaw("'free' as source, id, null as code, coalesce(company, name) as client, status, null as total, created_at");

        return DB::query()->fromSub($orders->unionAll($free), 'q')->orderBy('created_at')->limit($limit)->get();
    }

    public function countPending(): int
    {
        return DB::table('orders')->where('type', OrderType::Quote->value)->whereIn('status', self::PENDING['order'])->count()
            + DB::table('quote_requests')->whereIn('status', self::PENDING['free'])->count();
    }
}
