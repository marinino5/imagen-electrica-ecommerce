import { Chart } from 'chart.js';

// Los colores salen de las variables CSS definidas en _base.scss (paleta de marca).
export const css = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();
export const palette = () => Array.from({ length: 10 }, (_, i) => css(`--ie-chart-${i + 1}`));

export const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/** Valores por defecto de Chart.js: tipografía, colores y tooltips de la marca. */
export function applyDefaults() {
    Chart.defaults.font.family = css('--bs-body-font-family');
    Chart.defaults.color = css('--bs-secondary-color');
    Chart.defaults.animation = reducedMotion ? false : Chart.defaults.animation;
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.boxWidth = 8;
    Chart.defaults.plugins.legend.labels.boxHeight = 8;
    Chart.defaults.plugins.tooltip.backgroundColor = css('--bs-dark');
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.cornerRadius = 8;
}
