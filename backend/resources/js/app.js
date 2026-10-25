// Bootstrap activa por sí solo los componentes con atributos data-bs-*
// (menú lateral en celular, menú desplegable del usuario).
import 'bootstrap';

// Chart.js solo se descarga en las páginas que tienen el gráfico.
const requestsChart = document.getElementById('requests-by-status-chart');

if (requestsChart) {
    import('./dashboard-chart').then(({ renderRequestsByStatusChart }) => {
        renderRequestsByStatusChart(requestsChart);
    });
}
