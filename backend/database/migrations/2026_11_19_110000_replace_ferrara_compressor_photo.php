<?php

use App\Models\Product;
use App\Models\ProductLine;
use Illuminate\Database\Migrations\Migration;

/**
 * La línea del compresor Ferrara pasa de la foto propia (compresor envuelto en plástico, recorte sucio) a la imagen del distribuidor
 * recortada en el formato único de la tienda. Solo reemplaza la foto vieja del proyecto: si el equipo puso otra, no se toca. Idempotente.
 */
return new class extends Migration
{
    private const OLD = '/img/tienda/compresor-ferrara.webp';

    private const NEW = '/img/catalogo/ferrara-compresor.webp';

    public function up(): void
    {
        $line = ProductLine::where('slug', 'compresor-vertical')->first();

        if (! $line || $line->image_url !== self::OLD) {
            return;
        }

        $line->update(['image_url' => self::NEW]);
        Product::withTrashed()->where('line_id', $line->id)->where('image_url', self::OLD)->update(['image_url' => self::NEW]);
    }

    public function down(): void
    {
        // La foto no se revierte
    }
};
