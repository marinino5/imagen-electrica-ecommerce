<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

/**
 * Productos (solo administrador). El CRUD completo se construye en su propia
 * rama; por ahora existen el listado vacío y la ficha de lectura a la que
 * llevan el buscador y "Stock bajo".
 */
class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.coming-soon', ['title' => 'Productos']);
    }

    public function show(Product $product): View
    {
        return view('admin.products.show', [
            'product' => $product->load(['brand:id,name', 'category:id,name']),
        ]);
    }
}
