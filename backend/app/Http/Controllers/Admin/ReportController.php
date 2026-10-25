<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReportPreset;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReportRequest;
use App\Services\ReportService;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Reportes de ventas, ganancia y cotizaciones (solo administrador). */
class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    public function index(ReportRequest $request): View
    {
        $period = $request->period();

        return view('admin.reports.index', [
            'report' => $this->reports->build($period),
            'presets' => ReportPreset::cases(),
            'preset' => $request->preset(),
            'exportParams' => $this->exportParams($request),
        ]);
    }

    /** CSV con los indicadores y rankings del periodo. */
    public function export(ReportRequest $request): StreamedResponse
    {
        $period = $request->period();
        $rows = $this->reports->csvRows($period);

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            // BOM para que Excel reconozca las tildes; ";" porque Excel en español separa así
            fwrite($out, "\xEF\xBB\xBF");

            foreach ($rows as $row) {
                fputcsv($out, $row, ';');
            }

            fclose($out);
        }, $this->reports->csvFilename($period), ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array<string, string> */
    private function exportParams(ReportRequest $request): array
    {
        $custom = $request->preset() === ReportPreset::Custom;

        return array_filter([
            'period' => $request->preset()->value,
            'from' => $custom ? $request->validated('from') : null,
            'to' => $custom ? $request->validated('to') : null,
        ]);
    }
}
