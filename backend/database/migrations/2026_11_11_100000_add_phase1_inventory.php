<?php

use App\Models\Product;
use App\Models\ProductLine;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\LineImageSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Migración de datos (Fase 1B): publica en la base ya desplegada las referencias nuevas del inventario y ajusta el stock.
 *
 *  1. Stock de las 12 referencias de la hoja «Stock distinto» del Excel: pasa del valor que tenía la tienda al del Excel, y solo
 *     si todavía tiene el valor anterior (así no se pisa un ajuste hecho después en el panel).
 *  2. Líneas y referencias nuevas (familias 09 «Material eléctrico» y 10 «Otros productos», y las variantes de WCG01 y WCG20):
 *     salen de database/data/catalogo.json con el mismo CatalogSeeder de siempre, que es idempotente (busca por slug y por sku).
 *     Todas las referencias nuevas se venden por cotización: no traen precio hasta que la empresa lo entregue
 *     (`php artisan catalog:load-prices`).
 *  3. La foto de cada línea nueva (line-images.json).
 *
 * Las instalaciones nuevas no pasan por aquí: sin líneas, esta migración no hace nada y las siembra DatabaseSeeder con los mismos datos.
 * Idempotente: se puede correr dos veces o reanudar tras un fallo a medias (MySQL no revierte cambios de datos).
 */
return new class extends Migration
{
    /** [sku, stock de la tienda, stock del Excel] como lista (no clave numérica: MySQL estricto compara texto con texto). */
    private const STOCK = [
        ['12220436', 13, 11], ['12429371', 7, 6], ['12930985', 6, 1], ['10076389', 7, 5], ['10076409', 5, 0], ['10076433', 13, 12],
        ['11631355', 1, 0], ['11631358', 2, 1], ['10261745', 51, 50], ['14870191', 1, 0], ['17245357', 1, 0], ['18108471', 2, 0],
    ];

    public function up(): void
    {
        if (! ProductLine::query()->exists()) {
            return;
        }

        foreach (self::STOCK as [$sku, $before, $after]) {
            Product::query()->where('sku', (string) $sku)->where('stock', $before)->update(['stock' => $after]);
        }

        (new CatalogSeeder)->run();
        (new LineImageSeeder)->run();
    }

    public function down(): void
    {
        // Datos publicados: no se revierten
    }
};
