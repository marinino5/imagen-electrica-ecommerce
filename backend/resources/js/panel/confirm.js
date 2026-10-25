/** Pide confirmación antes de enviar los formularios con data-confirm (eliminar, cancelar...). */
export function initConfirmForms() {
    document.addEventListener('submit', (event) => {
        const message = event.target.dataset?.confirm;

        if (message && !window.confirm(message)) {
            event.preventDefault();
        }
    });
}
