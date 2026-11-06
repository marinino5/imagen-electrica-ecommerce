<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PromotionRequest;
use App\Http\Requests\Admin\SearchRequest;
use App\Models\ProductLine;
use App\Models\Promotion;
use App\Services\PromotionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** CRUD de promociones del momento (solo administrador). */
class PromotionController extends Controller
{
    public function __construct(private readonly PromotionService $promotions) {}

    public function index(SearchRequest $request): View
    {
        $search = trim((string) $request->validated('q'));

        return view('admin.promotions.index', [
            'search' => $search,
            'promotions' => $this->promotions->paginate($search),
        ]);
    }

    public function create(): View
    {
        return view('admin.promotions.form', [
            'promotion' => new Promotion(['is_active' => true, 'starts_at' => today(), 'ends_at' => today()->addDays(14)]),
            'lines' => $this->lineOptions(),
        ]);
    }

    public function store(PromotionRequest $request): RedirectResponse
    {
        $promotion = $this->promotions->create($request->validated());

        return redirect()->route('admin.promotions.index')->with('status', "Promoción «{$promotion->title}» creada.");
    }

    public function edit(Promotion $promotion): View
    {
        return view('admin.promotions.form', [
            'promotion' => $promotion->load('product:id,sku'),
            'lines' => $this->lineOptions(),
        ]);
    }

    public function update(PromotionRequest $request, Promotion $promotion): RedirectResponse
    {
        $this->promotions->update($promotion, $request->validated());

        return redirect()->route('admin.promotions.index')->with('status', "Promoción «{$promotion->title}» actualizada.");
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        $this->promotions->delete($promotion);

        return redirect()->route('admin.promotions.index')->with('status', "Promoción «{$promotion->title}» eliminada.");
    }

    /** Líneas activas para el selector del formulario. */
    private function lineOptions()
    {
        return ProductLine::query()->where('is_active', true)->orderBy('sort_order')->get(['id', 'name']);
    }
}
