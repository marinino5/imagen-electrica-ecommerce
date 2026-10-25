// Bootstrap activa por sí solo los componentes con atributos data-bs-*
// (menú lateral en celular, menús desplegables).
import 'bootstrap';

import { initConfirmForms } from './panel/confirm';
import { initLoginForm } from './panel/login';
import { initSidebar } from './panel/sidebar';
import { initSpecsEditor } from './panel/specs-editor';
import { initToasts } from './panel/toasts';

initSidebar();
initToasts();
initConfirmForms();
initSpecsEditor();
initLoginForm();

// Chart.js solo se descarga en las páginas que tienen gráficos.
const charts = document.querySelectorAll('canvas[data-chart]');

if (charts.length > 0) {
    import('./panel/dashboard-charts').then(({ renderCharts }) => renderCharts(charts));
}
