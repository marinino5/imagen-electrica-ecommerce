import { Chart } from 'chart.js';

// Los colores salen de las variables CSS definidas en _base.scss (paleta de marca).
export const css = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();

/** Colores de categorías y marcas, en orden (--chart-1 ... --chart-6). */
export const palette = () => Array.from({ length: 6 }, (_, i) => css(`--chart-${i + 1}`));

/** Un color por etiqueta; si el gráfico agrupa el resto en "Otros", esa última porción va en gris claro. */
export function sliceColors(labels, hasOther) {
    const colors = palette();

    return labels.map((_, i) => (hasOther && i === labels.length - 1 ? css('--chart-other') : colors[i % colors.length]));
}

/** Color de un estado (new, paid, cancelled...), el mismo de las insignias. */
export const statusColor = (status) => css(`--chart-status-${status}`);

export const gridColor = () => css('--chart-grid');

export const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/** Valores por defecto de Chart.js: tipografía, colores y tooltips de la marca. */
export function applyDefaults() {
    Chart.defaults.font.family = css('--bs-body-font-family');
    Chart.defaults.color = css('--chart-text');
    Chart.defaults.animation = reducedMotion ? false : Chart.defaults.animation;
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.boxWidth = 8;
    Chart.defaults.plugins.legend.labels.boxHeight = 8;
    Chart.defaults.plugins.tooltip.backgroundColor = css('--chart-ink');
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.cornerRadius = 8;
}
