<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandRequest;
use App\Http\Requests\Admin\SearchRequest;
use App\Models\Brand;
use App\Services\BrandService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** CRUD de marcas (solo administrador). */
class BrandController extends Controller
{
    public function __construct(private readonly BrandService $brands) {}

    public function index(SearchRequest $request): View
    {
        $search = trim((string) $request->validated('q'));

        return view('admin.brands.index', [
            'search' => $search,
            'brands' => $this->brands->paginate($search),
        ]);
    }

    public function create(): View
    {
        return view('admin.brands.form', ['brand' => new Brand(['is_active' => true])]);
    }

    public function store(BrandRequest $request): RedirectResponse
    {
        $brand = $this->brands->create($request->validated());

        return redirect()->route('admin.brands.index')->with('status', "Marca «{$brand->name}» creada.");
    }

    public function edit(Brand $brand): View
    {
        return view('admin.brands.form', ['brand' => $brand]);
    }

    public function update(BrandRequest $request, Brand $brand): RedirectResponse
    {
        $this->brands->update($brand, $request->validated());

        return redirect()->route('admin.brands.index')->with('status', "Marca «{$brand->name}» actualizada.");
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        try {
            $this->brands->delete($brand);
        } catch (BusinessRuleException $e) {
            return redirect()->route('admin.brands.index')->with('error', $e->getMessage());
        }

        return redirect()->route('admin.brands.index')->with('status', "Marca «{$brand->name}» eliminada.");
    }
}
