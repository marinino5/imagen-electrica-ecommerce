import {
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Legend,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';

import { applyDefaults, css, gridColor, sliceColors, statusColor } from './chart-theme';

// Solo se registran las piezas que usan los gráficos de Reportes.
Chart.register(
    ArcElement, BarController, BarElement, CategoryScale, DoughnutController,
    Legend, LinearScale, LineController, LineElement, PointElement, Tooltip,
);

const cop = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 });
const compact = new Intl.NumberFormat('es-CO', { notation: 'compact', maximumFractionDigits: 1 });

const moneyTick = (value) => `$ ${compact.format(value)}`;

/** Ventas (barras azules) y ganancia (líneas verdes); el año anterior, los mismos colores al 35 %. */
function monthlyChart(canvas, data) {
    const sales = css('--chart-sales');
    const profit = css('--chart-profit');
    const salesPrevious = css('--chart-sales-previous');
    const profitPrevious = css('--chart-profit-previous');

    return new Chart(canvas, {
        data: {
            labels: data.labels,
            datasets: [
                {
                    type: 'bar', label: 'Ventas', data: data.sales,
                    backgroundColor: sales, borderRadius: 4, maxBarThickness: 22, order: 2,
                },
                {
                    type: 'bar', label: 'Ventas año anterior', data: data.sales_previous,
                    backgroundColor: salesPrevious, borderRadius: 4, maxBarThickness: 22, order: 2,
                },
                {
                    type: 'line', label: 'Ganancia', data: data.profit,
                    borderColor: profit, backgroundColor: profit, borderWidth: 2.5,
                    pointRadius: 3, tension: 0.3, order: 1,
                },
                {
                    type: 'line', label: 'Ganancia año anterior', data: data.profit_previous,
                    borderColor: profitPrevious, backgroundColor: profitPrevious, borderWidth: 2.5, borderDash: [6, 4],
                    pointRadius: 2, tension: 0.3, order: 1,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                x: { grid: { display: false }, border: { display: false } },
                y: {
                    beginAtZero: true,
                    ticks: { callback: moneyTick },
                    grid: { color: gridColor() },
                    border: { display: false },
                },
            },
            plugins: {
                legend: { position: 'bottom', align: 'end' },
                tooltip: { callbacks: { label: (item) => `${item.dataset.label}: ${cop.format(item.parsed.y)}` } },
            },
        },
    });
}

/** Dona: ventas por categoría. */
function categoriesChart(canvas, { labels, data, has_other: hasOther }) {
    return new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: sliceColors(labels, hasOther),
                borderColor: css('--bs-body-bg'),
                borderWidth: 3,
                hoverOffset: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            layout: { padding: 4 },
            plugins: {
                legend: { position: window.innerWidth < 576 ? 'bottom' : 'right' },
                tooltip: { callbacks: { label: (item) => ` ${item.label}: ${cop.format(item.parsed)}` } },
            },
        },
    });
}

/** Barras horizontales: ventas por marca. */
function brandsChart(canvas, { labels, data, has_other: hasOther }) {
    return new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [{ label: 'Ventas', data, backgroundColor: sliceColors(labels, hasOther), borderRadius: 4, maxBarThickness: 22 }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { beginAtZero: true, ticks: { callback: moneyTick }, grid: { color: gridColor() }, border: { display: false } },
                y: { grid: { display: false }, border: { display: false } },
            },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (item) => ` ${cop.format(item.parsed.x)}` } },
            },
        },
    });
}

/** Barras agrupadas: cotizaciones enviadas y aceptadas por mes. */
function quotesChart(canvas, data) {
    return new Chart(canvas, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                { label: 'Enviadas', data: data.sent, backgroundColor: statusColor('sent'), borderRadius: 4, maxBarThickness: 24 },
                { label: 'Aceptadas', data: data.accepted, backgroundColor: statusColor('accepted'), borderRadius: 4, maxBarThickness: 24 },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { display: false }, border: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: gridColor() }, border: { display: false } },
            },
            plugins: { legend: { position: 'bottom', align: 'end' } },
        },
    });
}

const builders = { monthly: monthlyChart, categories: categoriesChart, brands: brandsChart, quotes: quotesChart };

export function renderReportCharts(canvases) {
    applyDefaults();

    canvases.forEach((canvas) => {
        builders[canvas.dataset.reportChart]?.(canvas, JSON.parse(canvas.dataset.chartData));
    });
}
