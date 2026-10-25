<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * CRUD de categorías (solo administrador). Por ahora solo existe la ruta del
 * listado; el CRUD completo se construye en su propia rama.
 */
class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.coming-soon', ['title' => 'Categorías']);
    }
}
