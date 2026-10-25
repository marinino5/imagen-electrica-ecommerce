import {
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    Legend,
    LinearScale,
    Tooltip,
} from 'chart.js';

// Solo se registran las piezas de Chart.js que usa un gráfico de barras.
Chart.register(BarController, BarElement, CategoryScale, LinearScale, Legend, Tooltip);

// Los colores salen de las variables CSS de Bootstrap (definidas en _variables.scss).
function cssVariable(name) {
    return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
}

const colorsByType = {
    purchase: '--bs-primary',
    quote: '--bs-accent',
};

/**
 * Gráfico de barras apiladas: una barra por estado, separada en pedidos y
 * cotizaciones. Los datos llegan desde DashboardService en data-chart.
 */
export function renderRequestsByStatusChart(canvas) {
    const { labels, datasets } = JSON.parse(canvas.dataset.chart);

    Chart.defaults.font.family = cssVariable('--bs-body-font-family');
    Chart.defaults.color = cssVariable('--bs-secondary-color');

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: datasets.map((dataset) => ({
                label: dataset.label,
                data: dataset.data,
                backgroundColor: cssVariable(colorsByType[dataset.type]),
                borderRadius: 4,
            })),
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { stacked: true, grid: { display: false } },
                y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } },
            },
            plugins: {
                legend: { position: 'bottom' },
            },
        },
    });
}
