// Bootstrap activa por sí solo los componentes con atributos data-bs-*
// (menú lateral en celular, menús desplegables).
import 'bootstrap';

import { initConfirmForms } from './panel/confirm';
import { initLoginForm } from './panel/login';
import { initReportFilter } from './panel/report-filter';
import { initSidebar } from './panel/sidebar';
import { initSpecsEditor } from './panel/specs-editor';
import { initToasts } from './panel/toasts';

initSidebar();
initToasts();
initConfirmForms();
initSpecsEditor();
initLoginForm();
initReportFilter();

// Chart.js solo se descarga en las páginas que tienen gráficos.
const charts = document.querySelectorAll('canvas[data-chart]');

if (charts.length > 0) {
    import('./panel/dashboard-charts').then(({ renderCharts }) => renderCharts(charts));
}

const reportCharts = document.querySelectorAll('canvas[data-report-chart]');

if (reportCharts.length > 0) {
    import('./panel/report-charts').then(({ renderReportCharts }) => renderReportCharts(reportCharts));
}
