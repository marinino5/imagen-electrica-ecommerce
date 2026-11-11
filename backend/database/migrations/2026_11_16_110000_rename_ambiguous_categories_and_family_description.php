<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;

/**
 * Nombres sin ambigüedad en el catálogo: «Cintas» (material eléctrico) pasa a «Cintas aislantes», para no confundirla con «Cintas de
 * enmascarar»; «Mando y maniobra» pasa a «Interruptores y mandos manuales», para no confundirla con «Controles de maniobra y
 * protección» ni «Accesorios de maniobra». La descripción de «Herramientas y Repuestos» pasa a una frase positiva.
 * Solo renombra lo que todavía tiene el nombre anterior (no pisa un cambio hecho en el panel). Idempotente.
 */
return new class extends Migration
{
    private const OLD_DESCRIPTION = 'Insumos, herramientas y repuestos que no son material eléctrico.';

    private const NEW_DESCRIPTION = 'Cintas, baterías, conexiones y repuestos para el día a día de tu taller o planta.';

    public function up(): void
    {
        foreach ([
            ['cintas', 'Cintas', 'Cintas aislantes', 'cintas-aislantes'],
            ['mando-y-maniobra', 'Mando y maniobra', 'Interruptores y mandos manuales', 'interruptores-y-mandos-manuales'],
        ] as [$oldSlug, $oldName, $name, $slug]) {
            $category = Category::where('slug', $oldSlug)->where('name', $oldName)->first();
            if ($category && ! Category::where('slug', $slug)->exists()) {
                $category->update(['name' => $name, 'slug' => $slug]);
            }
        }

        Category::where('slug', 'herramientas-y-repuestos')->whereNull('parent_id')
            ->where(fn ($q) => $q->where('description', self::OLD_DESCRIPTION)->orWhere('description', 'like', 'Cintas de enmascarar, bater%'))
            ->update(['description' => self::NEW_DESCRIPTION]);
    }

    public function down(): void
    {
        // Nombres corregidos: no se revierten
    }
};
