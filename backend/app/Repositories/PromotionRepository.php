<?php

namespace App\Repositories;

use App\Models\Promotion;
use App\Repositories\Concerns\EscapesLike;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PromotionRepository
{
    use EscapesLike;

    /** @return LengthAwarePaginator<int, Promotion> */
    public function paginate(?string $search, int $perPage): LengthAwarePaginator
    {
        return Promotion::query()
            ->with(['line:id,name,slug', 'product:id,sku,name'])
            ->when($search, function ($query, string $term) {
                $like = $this->likePattern($term);
                $query->where(fn ($q) => $q->where('title', 'like', $like)
                    ->orWhereHas('line', fn ($l) => $l->where('name', 'like', $like)));
            })
            ->orderByDesc('ends_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Promociones vigentes hoy, con lo necesario para mostrarlas: la línea (categoría, marca y
     * referencias activas para calcular el precio "desde") y la referencia si la hay.
     *
     * @return Collection<int, Promotion>
     */
    public function current(): Collection
    {
        return Promotion::query()
            ->current()
            ->with([
                'line' => fn ($q) => $q->where('is_active', true),
                'line.category:id,name,slug,parent_id', 'line.category.parent:id,name,slug',
                'line.variants' => fn ($q) => $q->where('is_active', true)->select(['id', 'line_id', 'sku', 'price', 'price_source', 'stock', 'sale_mode', 'is_active']),
                'product:id,line_id,sku,name,price,sale_mode,is_active',
            ])
            ->orderBy('ends_at')
            ->orderBy('id')
            ->get()
            ->filter(fn (Promotion $p) => $p->line !== null && ($p->product === null || $p->product->is_active))
            ->values();
    }

    public function create(array $data): Promotion
    {
        return Promotion::create($data);
    }

    public function update(Promotion $promotion, array $data): Promotion
    {
        $promotion->update($data);

        return $promotion;
    }

    public function delete(Promotion $promotion): void
    {
        $promotion->delete();
    }
}
