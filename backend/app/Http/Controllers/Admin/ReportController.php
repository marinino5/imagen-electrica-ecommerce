<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReportPreset;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReportRequest;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
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

    /** Pestaña «Comercial» de Análisis: qué se cotiza, vende y consulta, conversión y suscriptores. */
    public function commercial(ReportRequest $request): View
    {
        return view('admin.reports.commercial', [
            'report' => $this->reports->commercial($request->period()),
            'presets' => ReportPreset::cases(),
            'preset' => $request->preset(),
            'exportParams' => $this->exportParams($request),
        ]);
    }

    /** CSV con los indicadores y rankings del periodo. */
    public function export(ReportRequest $request): StreamedResponse
    {
        $period = $request->period();

        return $this->streamCsv($this->reports->csvRows($period), $this->reports->csvFilename($period));
    }

    /** CSV del análisis comercial. */
    public function commercialExport(ReportRequest $request): StreamedResponse
    {
        $period = $request->period();

        return $this->streamCsv($this->reports->commercialCsvRows($period), $this->reports->commercialCsvFilename($period));
    }

    /** PDF del análisis financiero. */
    public function pdf(ReportRequest $request): Response
    {
        $period = $request->period();

        return Pdf::loadView('pdf.report-financial', ['report' => $this->reports->build($period)])->setPaper('a4')
            ->download($this->reports->pdfFilename('financial', $period));
    }

    /** PDF del análisis comercial. */
    public function commercialPdf(ReportRequest $request): Response
    {
        $period = $request->period();

        return Pdf::loadView('pdf.report-commercial', ['report' => $this->reports->commercial($period, ReportService::TOP_SIZE)])->setPaper('a4')
            ->download($this->reports->pdfFilename('commercial', $period));
    }

    /** @param list<list<string|int>> $rows */
    private function streamCsv(array $rows, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            // BOM para que Excel reconozca las tildes; ";" porque Excel en español separa así
            fwrite($out, "\xEF\xBB\xBF");

            foreach ($rows as $row) {
                fputcsv($out, $row, ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
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
