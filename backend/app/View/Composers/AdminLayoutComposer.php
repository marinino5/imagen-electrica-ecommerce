<?php

namespace App\View\Composers;

use App\Services\DashboardService;
use Illuminate\View\View;

/**
 * Datos de la barra superior que necesitan todas las páginas del panel:
 * saludo según la hora, fecha en español y el número de la campana.
 */
class AdminLayoutComposer
{
    public function __construct(private readonly DashboardService $dashboard) {}

    public function compose(View $view): void
    {
        $now = now();

        $view->with([
            'greeting' => match (true) {
                $now->hour < 12 => 'Buenos días',
                $now->hour < 19 => 'Buenas tardes',
                default => 'Buenas noches',
            },
            // "viernes, 24 de octubre de 2026"
            'todayLabel' => $now->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY'),
            'newQuotesCount' => $this->dashboard->countNewQuotes(),
        ]);
    }
}
