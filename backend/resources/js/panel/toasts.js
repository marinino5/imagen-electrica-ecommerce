import { Toast } from 'bootstrap';

/** Muestra las notificaciones que el servidor dejó en la página (session status / error). */
export function initToasts() {
    document.querySelectorAll('.toast[data-autoshow]').forEach((element) => {
        Toast.getOrCreateInstance(element, { delay: 5000 }).show();
    });
}
