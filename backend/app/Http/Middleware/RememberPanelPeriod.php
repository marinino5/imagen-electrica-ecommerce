<?php

namespace App\Http\Middleware;

use App\Enums\ReportPreset;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

/**
 * Filtro de período global del panel. El período que se elige en la barra superior (`?period=last_7|last_30|this_month|…&from=&to=`)
 * se recuerda en la sesión y se aplica a Inicio y a Análisis sin tener que volver a elegirlo. Si la página se abre sin período, se usa
 * el recordado. Solo se recuerda un período válido (un rango personalizado con fechas buenas).
 */
class RememberPanelPeriod
{
    private const KEY = 'panel.period';

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && ! $request->expectsJson()) {
            if ($request->query->has('period')) {
                $picked = $request->query->all();
                $valid = Validator::make($picked, [
                    'period' => ['required', Rule::enum(ReportPreset::class)],
                    'from' => ['nullable', 'required_if:period,custom', 'date_format:Y-m-d'],
                    'to' => ['nullable', 'required_if:period,custom', 'date_format:Y-m-d', 'after_or_equal:from'],
                ])->passes();

                if ($valid) {
                    $request->session()->put(self::KEY, array_filter(['period' => $picked['period'], 'from' => $picked['from'] ?? null, 'to' => $picked['to'] ?? null]));
                }
            } elseif ($saved = $request->session()->get(self::KEY)) {
                $request->query->add($saved);
            }
        }

        return $next($request);
    }
}
