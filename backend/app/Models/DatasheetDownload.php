<?php

namespace App\Models;

use App\Enums\DatasheetPriceKind;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Una ficha técnica con cotización descargada desde la tienda: producto, referencia, cantidad, fecha y número de documento. */
#[Fillable(['document_number', 'product_id', 'sku', 'product_name', 'variant_label', 'quantity', 'price_kind', 'unit_price', 'total', 'ip'])]
class DatasheetDownload extends Model
{
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
            'price_kind' => DatasheetPriceKind::class,
        ];
    }

    /** N:1 (nulo si el producto se eliminó) */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
