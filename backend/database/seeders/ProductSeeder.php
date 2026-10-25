<?php

namespace Database\Seeders;

use App\Enums\ProductUnit;
use App\Enums\SaleMode;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * 20 productos con referencias de fabricante. Precios en COP con IVA incluido
 * (ilustrativos). El sku debe coincidir con el código de Siigo: los marcados
 * como PENDIENTE se reemplazan cuando se tenga el código real.
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id', 'slug');
        $brands = Brand::pluck('id', 'slug');

        foreach ($this->products() as $data) {
            [$category, $brand] = [$data['category'], $data['brand']];
            unset($data['category'], $data['brand']);

            Product::create($data + [
                'category_id' => $categories[$category],
                'brand_id' => $brands[$brand],
                'slug' => Str::slug($data['name']),
                'unit' => ProductUnit::Unidad,
                'tax_rate' => 19.00,
                'sale_mode' => SaleMode::Direct,
                'is_featured' => false,
            ]);
        }
    }

    private function products(): array
    {
        return [
            // Motores eléctricos — WEG W22 (código de catálogo NEMA)
            [
                'sku' => '00118ET3E143T-W22', 'category' => 'motores-trifasicos', 'brand' => 'weg',
                'name' => 'Motor trifásico W22 1 HP 1800 rpm',
                'short_description' => 'Motor de inducción de alta eficiencia, carcasa 143T, cerrado con ventilación exterior.',
                'specs' => ['potencia_hp' => 1, 'voltaje_v' => 220, 'polos' => 4, 'velocidad_rpm' => 1800, 'frecuencia_hz' => 60, 'carcasa' => '143T'],
                'price' => 1450000, 'stock' => 8, 'is_featured' => true,
            ],
            [
                'sku' => '00218ET3E145T-W22', 'category' => 'motores-trifasicos', 'brand' => 'weg',
                'name' => 'Motor trifásico W22 2 HP 1800 rpm',
                'short_description' => 'Motor de inducción de alta eficiencia, carcasa 145T.',
                'specs' => ['potencia_hp' => 2, 'voltaje_v' => 220, 'polos' => 4, 'velocidad_rpm' => 1800, 'frecuencia_hz' => 60, 'carcasa' => '145T'],
                'price' => 1980000, 'stock' => 5,
            ],
            [
                'sku' => '00518ET3E184T-W22', 'category' => 'motores-trifasicos', 'brand' => 'weg',
                'name' => 'Motor trifásico W22 5 HP 1800 rpm',
                'short_description' => 'Motor de inducción de alta eficiencia, carcasa 184T.',
                'specs' => ['potencia_hp' => 5, 'voltaje_v' => 220, 'polos' => 4, 'velocidad_rpm' => 1800, 'frecuencia_hz' => 60, 'carcasa' => '184T'],
                'price' => 3650000, 'stock' => 3, 'is_featured' => true,
            ],
            [
                'sku' => '01018ET3E215T-W22', 'category' => 'motores-trifasicos', 'brand' => 'weg',
                'name' => 'Motor trifásico W22 10 HP 1800 rpm',
                'short_description' => 'Motor de inducción de alta eficiencia, carcasa 215T. Precio según cantidad.',
                'specs' => ['potencia_hp' => 10, 'voltaje_v' => 220, 'polos' => 4, 'velocidad_rpm' => 1800, 'frecuencia_hz' => 60, 'carcasa' => '215T'],
                'price' => null, 'stock' => 1, 'sale_mode' => SaleMode::Quote,
            ],

            // Variadores y arrancadores
            [
                'sku' => '6SL3210-5BE21-5UV0', 'category' => 'variadores-de-frecuencia', 'brand' => 'siemens',
                'name' => 'Variador de frecuencia SINAMICS V20 1,5 kW 400 V',
                'short_description' => 'Variador compacto para bombas, ventiladores y bandas transportadoras.',
                'specs' => ['potencia_kw' => 1.5, 'voltaje_v' => 400, 'fases' => 3],
                'price' => 2350000, 'stock' => 4, 'is_featured' => true,
            ],
            [
                'sku' => 'VFD015EL21A', 'category' => 'variadores-de-frecuencia', 'brand' => 'delta',
                'name' => 'Variador de frecuencia VFD-EL 1,5 kW 230 V monofásico',
                'short_description' => 'Variador de entrada monofásica para motores trifásicos pequeños.',
                'specs' => ['potencia_kw' => 1.5, 'voltaje_v' => 230, 'fases' => 1],
                'price' => 1390000, 'stock' => 0, // Venta directa sin stock: entra como cotización
            ],
            [
                'sku' => 'CFW500A07P0T2NB20', 'category' => 'variadores-de-frecuencia', 'brand' => 'weg',
                'name' => 'Variador de frecuencia CFW500 7 A 220 V',
                'short_description' => 'Variador modular con funciones de PLC integradas.',
                'specs' => ['corriente_a' => 7, 'voltaje_v' => 220, 'fases' => 3],
                'price' => 1890000, 'stock' => 2,
            ],
            [
                'sku' => 'SSW070030T5SZ', 'category' => 'arrancadores-suaves', 'brand' => 'weg',
                'name' => 'Arrancador suave SSW07 30 A',
                'short_description' => 'Arrancador suave con bypass incorporado. Se importa bajo pedido.',
                'specs' => ['corriente_a' => 30, 'voltaje_max_v' => 575],
                'price' => null, 'stock' => 0, 'sale_mode' => SaleMode::OnRequest,
            ],
            [
                'sku' => '3RV2011-1KA10', 'category' => 'guardamotores', 'brand' => 'siemens',
                'name' => 'Guardamotor SIRIUS 3RV2 de 9 a 12,5 A',
                'short_description' => 'Interruptor de protección de motor, tamaño S00, bornes de tornillo.',
                'specs' => ['corriente_min_a' => 9, 'corriente_max_a' => 12.5],
                'price' => 345000, 'stock' => 15,
            ],

            // PLC
            [
                'sku' => '6ES7214-1AG40-0XB0', 'category' => 'plc-compactos', 'brand' => 'siemens',
                'name' => 'PLC SIMATIC S7-1200 CPU 1214C DC/DC/DC',
                'short_description' => 'Controlador compacto con 14 entradas y 10 salidas digitales.',
                'specs' => ['entradas_digitales' => 14, 'salidas_digitales' => 10, 'entradas_analogicas' => 2, 'alimentacion_v' => 24],
                'price' => 2980000, 'stock' => 2, 'is_featured' => true,
            ],
            [
                'sku' => '6ES7212-1BE40-0XB0', 'category' => 'plc-compactos', 'brand' => 'siemens',
                'name' => 'PLC SIMATIC S7-1200 CPU 1212C AC/DC/relé',
                'short_description' => 'Controlador compacto con 8 entradas digitales y 6 salidas a relé.',
                'specs' => ['entradas_digitales' => 8, 'salidas_digitales' => 6, 'entradas_analogicas' => 2, 'alimentacion_v' => 230],
                'price' => 1960000, 'stock' => 3,
            ],
            [
                'sku' => '6ED1052-1MD08-0BA1', 'category' => 'reles-programables', 'brand' => 'siemens',
                'name' => 'Relé programable LOGO! 12/24RCE',
                'short_description' => 'Módulo lógico con pantalla y servidor web integrado.',
                'specs' => ['entradas_digitales' => 8, 'salidas_digitales' => 4, 'alimentacion_v' => 24],
                'price' => 790000, 'stock' => 6,
            ],
            [
                'sku' => 'DVP14SS211R', 'category' => 'plc-compactos', 'brand' => 'delta',
                'name' => 'PLC DVP-SS2 de 14 puntos con salidas a relé',
                'short_description' => 'PLC delgado de 8 entradas y 6 salidas, ampliable con módulos.',
                'specs' => ['entradas_digitales' => 8, 'salidas_digitales' => 6, 'alimentacion_v' => 24],
                'price' => 640000, 'stock' => 4,
            ],

            // Ventilación — Soler & Palau
            [
                'sku' => 'TD-500/150', 'category' => 'extractores', 'brand' => 'soler-palau',
                'name' => 'Extractor en línea TD-500/150 Mixvent',
                'short_description' => 'Extractor helicocentrífugo para ducto de 150 mm.',
                'specs' => ['diametro_mm' => 150, 'voltaje_v' => 120],
                'price' => 560000, 'stock' => 6,
            ],
            [
                'sku' => 'HCT-45-4T', 'category' => 'ventiladores-axiales', 'brand' => 'soler-palau',
                'name' => 'Ventilador axial mural HCT-45-4T trifásico',
                'short_description' => 'Ventilador axial para muro, hélice de 450 mm. Precio según instalación.',
                'specs' => ['diametro_mm' => 450, 'polos' => 4, 'fases' => 3],
                'price' => null, 'stock' => 0, 'sale_mode' => SaleMode::Quote,
            ],

            // Iluminación — Sylvania (PENDIENTE: código Siigo)
            [
                'sku' => 'SYL-REF-LED-100W', 'category' => 'reflectores-led', 'brand' => 'sylvania',
                'name' => 'Reflector LED 100 W luz blanca',
                'short_description' => 'Reflector para exteriores, cuerpo de aluminio.',
                'specs' => ['potencia_w' => 100, 'temperatura_color_k' => 6500, 'grado_proteccion' => 'IP65'],
                'price' => 185000, 'stock' => 20,
            ],
            [
                'sku' => 'SYL-T8-18W-C10', 'category' => 'tubos-led', 'brand' => 'sylvania',
                'name' => 'Tubo LED T8 18 W 120 cm, caja por 10',
                'short_description' => 'Tubo LED de reemplazo para luminarias fluorescentes T8.',
                'specs' => ['potencia_w' => 18, 'longitud_mm' => 1200, 'temperatura_color_k' => 6500, 'unidades_por_caja' => 10],
                'price' => 165000, 'stock' => 12, 'unit' => ProductUnit::Caja,
            ],

            // Cables — Nexans (PENDIENTE: código Siigo)
            [
                'sku' => 'NEX-THHN-12AWG', 'category' => 'cables-thhn', 'brand' => 'nexans',
                'name' => 'Cable THHN/THWN-2 12 AWG cobre',
                'short_description' => 'Cable de cobre para instalaciones de baja tensión. Se vende por metro.',
                'specs' => ['calibre_awg' => 12, 'voltaje_v' => 600, 'temperatura_max_c' => 90],
                'price' => 3400, 'stock' => 1500, 'unit' => ProductUnit::Metro, 'is_featured' => true,
            ],
            [
                'sku' => 'NEX-THHN-10AWG-R100', 'category' => 'cables-thhn', 'brand' => 'nexans',
                'name' => 'Cable THHN/THWN-2 10 AWG cobre, rollo de 100 m',
                'short_description' => 'Rollo completo de cable de cobre para acometidas y circuitos de fuerza.',
                'specs' => ['calibre_awg' => 10, 'longitud_m' => 100, 'voltaje_v' => 600, 'temperatura_max_c' => 90],
                'price' => 560000, 'stock' => 10, 'unit' => ProductUnit::Rollo,
            ],

            // Motorreductores — Cestari (PENDIENTE: código Siigo)
            [
                'sku' => 'CES-SC-1HP-1-30', 'category' => 'sinfin-corona', 'brand' => 'cestari',
                'name' => 'Motorreductor sinfín corona 1 HP relación 1:30',
                'short_description' => 'Motorreductor de eje hueco para transportadores. Se cotiza según aplicación.',
                'specs' => ['potencia_hp' => 1, 'relacion_reduccion' => 30],
                'price' => null, 'stock' => 0, 'sale_mode' => SaleMode::Quote,
            ],
        ];
    }
}
