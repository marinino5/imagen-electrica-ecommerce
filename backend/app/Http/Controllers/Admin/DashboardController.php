<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReportRequest;
use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Inicio. El período sale del filtro global de la barra superior (el mismo de Análisis). */
    public function __invoke(ReportRequest $request, DashboardService $dashboard): View
    {
        return view('admin.dashboard', ['overview' => $dashboard->overview($request->period())]);
    }
}
