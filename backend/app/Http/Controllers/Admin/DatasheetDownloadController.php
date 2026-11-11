<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DatasheetService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** «Fichas descargadas»: las fichas con cotización en PDF que generó la tienda (admin y comercial). */
class DatasheetDownloadController extends Controller
{
    public function __construct(private readonly DatasheetService $datasheets) {}

    public function index(Request $request): View
    {
        $search = $request->validate(['q' => ['nullable', 'string', 'max:100']])['q'] ?? null;

        return view('admin.datasheets.index', ['search' => $search, 'downloads' => $this->datasheets->paginate($search)]);
    }
}
