<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Promotion;
use App\Repositories\PromotionRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PromotionService
{
    public const PER_PAGE = 10;

    public function __construct(private readonly PromotionRepository $repository) {}

    /** @return LengthAwarePaginator<int, Promotion> */
    public function paginate(?string $search): LengthAwarePaginator
    {
        return $this->repository->paginate($search ? trim($search) : null, self::PER_PAGE);
    }

    /** @return Collection<int, Promotion> Promociones vigentes hoy (si no hay, la tienda oculta la sección). */
    public function current(): Collection
    {
        return $this->repository->current();
    }

    public function create(array $data): Promotion
    {
        return $this->repository->create($this->normalize($data));
    }

    public function update(Promotion $promotion, array $data): Promotion
    {
        return $this->repository->update($promotion, $this->normalize($data));
    }

    public function delete(Promotion $promotion): void
    {
        $this->repository->delete($promotion);
    }

    /** El SKU del formulario se convierte en product_id; solo una de las dos formas de precio queda guardada. */
    private function normalize(array $data): array
    {
        $sku = trim((string) ($data['sku'] ?? ''));
        unset($data['sku']);
        $data['product_id'] = $sku === '' ? null : Product::query()->where('sku', $sku)->value('id');

        if (! empty($data['special_price'])) {
            $data['discount_percent'] = null;
        } else {
            $data['special_price'] = null;
        }

        return $data;
    }
}
