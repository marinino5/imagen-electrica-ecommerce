<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * CRUD de marcas (solo administrador). Por ahora solo existe la ruta del
 * listado; el CRUD completo se construye en su propia rama.
 */
class BrandController extends Controller
{
    public function index(): View
    {
        return view('admin.coming-soon', ['title' => 'Marcas']);
    }
}
