/**
 * Filtro de periodo de Reportes: los periodos fijos se aplican al elegirlos;
 * "Rango personalizado" muestra las fechas y espera el botón Aplicar.
 */
export function initReportFilter() {
    const form = document.querySelector('[data-report-filter]');

    if (!form) {
        return;
    }

    const select = form.querySelector('[data-report-period]');
    const dateFields = form.querySelectorAll('[data-report-custom]');

    select.addEventListener('change', () => {
        const custom = select.value === 'custom';

        dateFields.forEach((field) => { field.hidden = !custom; });

        if (!custom) {
            form.submit();
        }
    });
}
