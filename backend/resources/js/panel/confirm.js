/**
 * Pide confirmación antes de enviar un formulario con data-confirm (eliminar...)
 * o al pulsar un botón de envío con data-confirm (cancelar, rechazar...).
 */
export function initConfirmForms() {
    document.addEventListener('submit', (event) => {
        const message = event.submitter?.dataset.confirm ?? event.target.dataset?.confirm;

        if (message && !window.confirm(message)) {
            event.preventDefault();
        }
    });
}
