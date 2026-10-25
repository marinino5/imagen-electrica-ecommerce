<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Requests\Admin\SearchRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** CRUD de categorías (solo administrador). */
class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categories) {}

    public function index(SearchRequest $request): View
    {
        $search = trim((string) $request->validated('q'));

        return view('admin.categories.index', [
            'search' => $search,
            'categories' => $this->categories->paginate($search),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.form', [
            'category' => new Category(['is_active' => true, 'sort_order' => 0]),
            'parents' => $this->categories->parentOptions(),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $category = $this->categories->create($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('status', "Categoría «{$category->name}» creada.");
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', [
            'category' => $category->load('parent:id,name,slug'),
            'parents' => $this->categories->parentOptions($category),
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $this->categories->update($category, $request->validated());

        return redirect()->route('admin.categories.index')
            ->with('status', "Categoría «{$category->name}» actualizada.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        try {
            $this->categories->delete($category);
        } catch (BusinessRuleException $e) {
            return redirect()->route('admin.categories.index')->with('error', $e->getMessage());
        }

        return redirect()->route('admin.categories.index')
            ->with('status', "Categoría «{$category->name}» eliminada.");
    }
}
