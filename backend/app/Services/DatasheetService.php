<?php

namespace App\Services;

use App\Enums\DatasheetPriceKind;
use App\Enums\ProductUnit;
use App\Enums\SaleMode;
use App\Exceptions\BusinessRuleException;
use App\Models\Product;
use App\Repositories\DatasheetDownloadRepository;
use App\Repositories\ProductRepository;
use App\Support\Money;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Ficha técnica con cotización en PDF: producto, referencia elegida, especificaciones y valor (el mismo precio que muestra la
 * tienda). Cada PDF generado queda registrado con su número de documento (IE-FT-2026-00123) para verlo en el panel.
 */
class DatasheetService
{
    public const PER_PAGE = 20;

    /** Unidad de la última parte de la clave de specs («potencia_w» → W). Misma tabla que cliente/src/lib/format.js. */
    private const UNITS = [
        'v' => 'V', 'a' => 'A', 'hp' => 'HP', 'kw' => 'kW', 'w' => 'W', 'hz' => 'Hz', 'rpm' => 'rpm', 'm' => 'm', 'mm' => 'mm',
        'c' => '°C', 'awg' => 'AWG', 'lm' => 'lm', 'k' => 'K', 'cfm' => 'CFM', 'psi' => 'psi', 'bar' => 'bar', 'l' => 'L', 'kg' => 'kg',
        'va' => 'VA', 'ah' => 'Ah', 'kv' => 'kV', 'kohm' => 'kΩ',
    ];

    public function __construct(
        private readonly ProductRepository $products,
        private readonly DatasheetDownloadRepository $downloads,
    ) {}

    /** @return LengthAwarePaginator<int, \App\Models\DatasheetDownload> */
    public function paginate(?string $search, int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return $this->downloads->paginate($search ? trim($search) : null, $perPage);
    }

    /**
     * Genera el PDF y lo registra.
     *
     * @return array{content: string, filename: string, number: string}
     *
     * @throws BusinessRuleException si la cantidad no corresponde a la unidad del producto
     */
    public function generate(string $sku, float $quantity, ?string $ip): array
    {
        $product = $this->products->findActiveBySkuOrFail($sku);
        $quantity = round($quantity, 2);

        // Solo el cable se vende en fracciones (por metro), igual que el carrito
        if ($product->unit !== ProductUnit::Metro && floor($quantity) !== $quantity) {
            throw new BusinessRuleException("«{$product->name}» se vende por {$product->unit->value}: la cantidad debe ser un número entero.");
        }

        $kind = $this->priceKind($product);
        $prices = $kind === DatasheetPriceKind::Quote ? null : $this->prices($product, $quantity);

        // El número lleva el id del registro: se crea y luego se completa (misma transacción)
        $download = DB::transaction(function () use ($product, $quantity, $kind, $prices, $ip) {
            $download = $this->downloads->create([
                'product_id' => $product->id,
                'sku' => $product->sku,
                'product_name' => $product->line?->name ?? $product->name,
                'variant_label' => $product->variant_label,
                'quantity' => $quantity,
                'price_kind' => $kind,
                'unit_price' => $prices['unit'] ?? null,
                'total' => $prices['total'] ?? null,
                'ip' => $ip,
            ]);

            return $this->downloads->update($download, ['document_number' => sprintf('IE-FT-%d-%05d', now()->year, $download->id)]);
        });

        $days = (int) config('shop.datasheet_valid_days');
        $pdf = Pdf::loadView('pdf.datasheet', [
            'number' => $download->document_number,
            'date' => now()->timezone(config('app.timezone')),
            'validUntil' => now()->timezone(config('app.timezone'))->addDays($days),
            'days' => $days,
            'product' => $product,
            'title' => $product->line?->name ?? $product->name,
            'image' => $this->imageData($product->image_url),
            'rows' => $this->specRows($product),
            'kind' => $kind,
            'quantity' => $quantity,
            'prices' => $prices,
            'quoteUrl' => config('shop.frontend_url').'/cotizar',
            'site' => ['phone' => '+57 316 821 9758', 'email' => 'imagenelectrica@gmail.com', 'address' => 'Cra 18 No 24-32, Bucaramanga, Santander, Colombia'],
        ])->setPaper('a4');

        return ['content' => $pdf->output(), 'filename' => "{$download->document_number}.pdf", 'number' => $download->document_number];
    }

    /** Venta directa con precio = el de la tienda; con precio de referencia, igual pero con la nota; sin precio, solo cotización. */
    private function priceKind(Product $product): DatasheetPriceKind
    {
        if ($product->sale_mode !== SaleMode::Direct || $product->price === null) {
            return DatasheetPriceKind::Quote;
        }

        return $product->price_source !== null ? DatasheetPriceKind::Reference : DatasheetPriceKind::Direct;
    }

    /**
     * El precio incluye IVA: el total es precio × cantidad y de ahí se desglosa la base y el IVA con el tax_rate del producto.
     *
     * @return array{unit: float, subtotal: float, tax: float, total: float, rate: float}
     */
    private function prices(Product $product, float $quantity): array
    {
        $unit = (float) $product->price;
        $rate = (float) $product->tax_rate;
        $total = round($unit * $quantity, 2);
        $subtotal = round($total / (1 + $rate / 100), 2);

        return ['unit' => $unit, 'subtotal' => $subtotal, 'tax' => round($total - $subtotal, 2), 'total' => $total, 'rate' => $rate];
    }

    /** @return list<array{0: string, 1: string}> */
    private function specRows(Product $product): array
    {
        $specs = $product->specs ?? [];
        $num = fn ($v) => str_replace('.', ',', (string) $v);
        $brand = $product->brand && $product->brand->slug !== 'sin-marca' ? $product->brand->name : null;

        $rows = [
            ['Línea', $product->line?->name],
            ['Referencia', $product->variant_label ?? $product->name],
            ['SKU', $product->sku],
            ['Tipo', $specs['tipo'] ?? null],
            ['Marca', $brand],
            ['Serie', $specs['serie'] ?? null],
            ['Potencia', isset($specs['potencia_hp']) ? $num($specs['potencia_hp']).' HP' : null],
            ['Velocidad', isset($specs['velocidad_rpm']) ? $specs['velocidad_rpm'].' RPM' : null],
            ['Corriente', isset($specs['corriente_a']) ? $num($specs['corriente_a']).' A' : null],
            ['Tensión', isset($specs['voltaje_v']) ? $specs['voltaje_v'].' V' : null],
            ['Protección', $specs['proteccion_ip'] ?? null],
        ];

        $skip = ['tipo', 'serie', 'potencia_hp', 'velocidad_rpm', 'corriente_a', 'voltaje_v', 'proteccion_ip'];
        foreach ($specs as $key => $value) {
            if (in_array($key, $skip, true) || $value === null || $value === '') {
                continue;
            }
            [$label, $shown] = $this->formatSpec((string) $key, $value);
            $rows[] = [$label, $shown];
        }
        $rows[] = ['Categoría', $product->category?->name];

        return array_values(array_filter($rows, fn ($r) => $r[1] !== null && $r[1] !== ''));
    }

    /** «potencia_w» + 12 → ['Potencia', '12 W'] (misma regla que formatSpec en la tienda). */
    private function formatSpec(string $key, mixed $value): array
    {
        $parts = explode('_', $key);
        $last = end($parts);
        $hasUnit = count($parts) > 1 && isset(self::UNITS[$last]);
        $words = implode(' ', $hasUnit ? array_slice($parts, 0, -1) : $parts);
        $shown = is_numeric($value) ? number_format((float) $value, is_float($value + 0) && floor((float) $value) !== (float) $value ? 2 : 0, ',', '.') : (string) $value;

        return [mb_strtoupper(mb_substr($words, 0, 1)).mb_substr($words, 1), $hasUnit ? $shown.' '.self::UNITS[$last] : $shown];
    }

    /**
     * Imagen del producto como PNG incrustado (dompdf no lee WebP): se toma de cliente/public si existe (desarrollo y pruebas) o de la
     * tienda publicada. Si no se consigue, el PDF sale sin imagen en vez de fallar.
     */
    private function imageData(?string $path): ?string
    {
        if (! $path || ! str_starts_with($path, '/') || ! function_exists('imagecreatefromstring')) {
            return null;
        }

        return Cache::remember('datasheet-image:'.md5($path), now()->addDay(), function () use ($path) {
            $local = base_path('../cliente/public'.$path);
            $bytes = is_file($local) ? file_get_contents($local) : null;

            if ($bytes === null) {
                try {
                    $response = Http::timeout(5)->get(config('shop.frontend_url').$path);
                    $bytes = $response->successful() ? $response->body() : null;
                } catch (\Throwable) {
                    $bytes = null;
                }
            }

            $source = $bytes ? @imagecreatefromstring($bytes) : false;
            if (! $source) {
                return null;
            }

            // Sobre fondo blanco (el recorte es transparente) y como máximo 600 px
            [$w, $h] = [imagesx($source), imagesy($source)];
            $scale = min(1, 600 / max($w, $h));
            [$nw, $nh] = [max(1, (int) round($w * $scale)), max(1, (int) round($h * $scale))];
            $canvas = imagecreatetruecolor($nw, $nh);
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $nw, $nh, $w, $h);
            ob_start();
            imagepng($canvas);

            return 'data:image/png;base64,'.base64_encode((string) ob_get_clean());
        });
    }

    /** Texto de dinero para la vista. */
    public static function cop(float|int|null $value): string
    {
        return Money::cop($value);
    }
}
