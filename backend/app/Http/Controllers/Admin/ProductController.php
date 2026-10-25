<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductUnit;
use App\Enums\SaleMode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductIndexRequest;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product;
use App\Services\BrandService;
use App\Services\CategoryService;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** CRUD de productos (solo administrador). */
class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $products,
        private readonly CategoryService $categories,
        private readonly BrandService $brands,
    ) {}

    public function index(ProductIndexRequest $request): View
    {
        $filters = $request->filters();

        return view('admin.products.index', [
            'filters' => $filters,
            'products' => $this->products->paginate($filters),
            'categories' => $this->categories->treeOptions(),
            'brands' => $this->brands->options(),
            'lowStockThreshold' => ProductService::LOW_STOCK_THRESHOLD,
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', $this->formData(new Product([
            'unit' => ProductUnit::Unidad,
            'sale_mode' => SaleMode::Direct,
            'tax_rate' => 19,
            'stock' => 0,
            'is_active' => true,
        ])));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $product = $this->products->create($request->validated());

        return redirect()->route('admin.products.index')->with('status', "Producto «{$product->name}» creado.");
    }

    public function show(Product $product): View
    {
        return view('admin.products.show', [
            'product' => $product->load(['brand:id,name', 'category:id,name']),
            'lowStockThreshold' => ProductService::LOW_STOCK_THRESHOLD,
        ]);
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', $this->formData($product));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->products->update($product, $request->validated());

        return redirect()->route('admin.products.index')->with('status', "Producto «{$product->name}» actualizado.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->products->delete($product);

        return redirect()->route('admin.products.index')
            ->with('status', "Producto «{$product->name}» eliminado. Puedes recuperarlo con el filtro «Eliminados».");
    }

    public function restore(Product $product): RedirectResponse
    {
        $this->products->restore($product);

        return redirect()->route('admin.products.index')->with('status', "Producto «{$product->name}» restaurado.");
    }

    /** @return array<string, mixed> */
    private function formData(Product $product): array
    {
        return [
            'product' => $product,
            'categories' => $this->categories->treeOptions(),
            'brands' => $this->brands->options(),
            'units' => ProductUnit::cases(),
            'saleModes' => SaleMode::cases(),
        ];
    }
}
