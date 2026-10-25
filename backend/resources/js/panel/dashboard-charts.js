import {
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Legend,
    LinearScale,
    Tooltip,
} from 'chart.js';

import { applyDefaults, css, palette } from './chart-theme';

// Solo se registran las piezas de Chart.js que usan la dona y las barras.
Chart.register(ArcElement, BarController, BarElement, CategoryScale, DoughnutController, Legend, LinearScale, Tooltip);

const colorsByType = { purchase: '--bs-primary', quote: '--bs-accent' };

/**
 * Escribe el total en el centro de la dona. Usa el área real del gráfico,
 * así queda centrado aunque la leyenda esté a un lado.
 */
const centerTotal = {
    id: 'centerTotal',
    afterDraw(chart) {
        const { ctx, chartArea } = chart;
        const total = chart.data.datasets[0].data.reduce((sum, value) => sum + value, 0);
        const x = (chartArea.left + chartArea.right) / 2;
        const y = (chartArea.top + chartArea.bottom) / 2;

        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillStyle = css('--bs-body-color');
        ctx.font = `700 2.25rem ${css('--bs-heading-font-family') || 'sans-serif'}`;
        ctx.fillText(String(total), x, y - 8);
        ctx.fillStyle = css('--bs-secondary-color');
        ctx.font = `500 0.8rem ${css('--bs-body-font-family')}`;
        ctx.fillText('solicitudes', x, y + 20);
        ctx.restore();
    },
};

/** Dona: solicitudes por estado. */
function statusChart(canvas, { labels, data }) {
    const colors = palette();

    return new Chart(canvas, {
        type: 'doughnut',
        plugins: [centerTotal],
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: labels.map((_, i) => colors[i % colors.length]),
                borderColor: css('--bs-body-bg'),
                borderWidth: 3,
                hoverOffset: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            layout: { padding: 4 },
            plugins: {
                legend: { position: window.innerWidth < 576 ? 'bottom' : 'right' },
            },
        },
    });
}

/** Barras apiladas: solicitudes por día, separadas en pedidos y cotizaciones. */
function dailyChart(canvas, { labels, datasets }) {
    return new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: datasets.map((dataset) => ({
                label: dataset.label,
                data: dataset.data,
                backgroundColor: css(colorsByType[dataset.type]),
                borderRadius: 6,
                maxBarThickness: 36,
            })),
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { stacked: true, grid: { display: false }, border: { display: false } },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: css('--ie-grid-color') },
                    border: { display: false },
                },
            },
            plugins: {
                legend: { position: 'bottom', align: 'end' },
            },
        },
    });
}

const builders = { status: statusChart, daily: dailyChart };

export function renderCharts(canvases) {
    applyDefaults();

    canvases.forEach((canvas) => {
        const build = builders[canvas.dataset.chart];
        build?.(canvas, JSON.parse(canvas.dataset.chartData));
    });
}
