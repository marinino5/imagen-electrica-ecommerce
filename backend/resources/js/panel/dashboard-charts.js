import {
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Legend,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip,
} from 'chart.js';

import { applyDefaults, css, gridColor, palette, statusColor } from './chart-theme';

// Solo se registran las piezas de Chart.js que usan la dona y las barras.
Chart.register(ArcElement, BarController, BarElement, CategoryScale, DoughnutController, Legend, LineController, LineElement, LinearScale, PointElement, Tooltip);

// Pedidos y cotizaciones son dos series de la misma paleta de categorías
const colorsByType = { purchase: 0, quote: 1 };

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
        ctx.fillStyle = css('--chart-ink');
        ctx.font = `700 2.25rem ${css('--bs-heading-font-family') || 'sans-serif'}`;
        ctx.fillText(String(total), x, y - 8);
        ctx.fillStyle = css('--chart-text');
        ctx.font = `500 0.8rem ${css('--bs-body-font-family')}`;
        ctx.fillText('solicitudes', x, y + 20);
        ctx.restore();
    },
};

/** Dona: solicitudes por estado. */
function statusChart(canvas, { labels, data, statuses }) {

    return new Chart(canvas, {
        type: 'doughnut',
        plugins: [centerTotal],
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: statuses.map(statusColor),
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
                backgroundColor: palette()[colorsByType[dataset.type]],
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
                    grid: { color: gridColor() },
                    border: { display: false },
                },
            },
            plugins: {
                legend: { position: 'bottom', align: 'end' },
            },
        },
    });
}

/** Línea vertical que sigue al cursor sobre la gráfica, para leer el día completo con su tooltip. */
const crosshair = {
    id: 'crosshair',
    afterDatasetsDraw(chart) {
        const active = chart.tooltip?.getActiveElements?.() ?? [];

        if (active.length === 0) {
            return;
        }

        const { ctx, chartArea } = chart;
        const x = active[0].element.x;

        ctx.save();
        ctx.strokeStyle = css('--chart-text');
        ctx.globalAlpha = 0.35;
        ctx.setLineDash([4, 4]);
        ctx.beginPath();
        ctx.moveTo(x, chartArea.top);
        ctx.lineTo(x, chartArea.bottom);
        ctx.stroke();
        ctx.restore();
    },
};

/** Ventas (barras, en pesos) y cotizaciones recibidas (línea, eje derecho) del período elegido; por mes si el período es largo. */
function salesChart(canvas, { labels, sales, quotes, monthly }) {
    const [blue, green] = palette();
    const money = (value) => `$ ${Number(value).toLocaleString('es-CO')}`;

    return new Chart(canvas, {
        plugins: [crosshair],
        data: {
            labels,
            datasets: [
                { type: 'bar', label: 'Ventas', data: sales, backgroundColor: blue, borderRadius: 4, maxBarThickness: monthly ? 36 : 18, yAxisID: 'y', order: 2, hoverBackgroundColor: blue },
                { type: 'line', label: 'Cotizaciones', data: quotes, borderColor: green, backgroundColor: green, borderWidth: 2, pointRadius: labels.length > 40 ? 0 : 2, pointHoverRadius: 5, tension: 0.3, yAxisID: 'y1', order: 1 },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                x: { grid: { display: false }, border: { display: false }, ticks: { maxTicksLimit: window.innerWidth < 576 ? 5 : 10, maxRotation: 0 } },
                y: { beginAtZero: true, grid: { color: gridColor() }, border: { display: false }, ticks: { callback: (v) => (v >= 1000000 ? `${v / 1000000} M` : v >= 1000 ? `${v / 1000} mil` : v) } },
                y1: { beginAtZero: true, position: 'right', grid: { display: false }, border: { display: false }, ticks: { precision: 0 } },
            },
            plugins: {
                legend: { position: 'bottom', align: 'end' },
                tooltip: { callbacks: { label: (item) => (item.dataset.yAxisID === 'y' ? `Ventas: ${money(item.parsed.y)}` : `Cotizaciones: ${item.parsed.y}`) } },
            },
        },
    });
}

const builders = { status: statusChart, daily: dailyChart, sales: salesChart };

export function renderCharts(canvases) {
    applyDefaults();

    canvases.forEach((canvas) => {
        const build = builders[canvas.dataset.chart];
        build?.(canvas, JSON.parse(canvas.dataset.chartData));
    });
}
