<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SearchRequest;
use App\Services\ProductSearchService;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(SearchRequest $request, ProductSearchService $search): View
    {
        $term = trim((string) $request->validated('q'));

        return view('admin.search', [
            'term' => $term,
            'minLength' => ProductSearchService::MIN_TERM_LENGTH,
            'products' => $search->search($term),
        ]);
    }
}
