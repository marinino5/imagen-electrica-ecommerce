import { Tooltip } from 'bootstrap';

const STORAGE_KEY = 'admin.sidebar.collapsed';
const root = document.documentElement;

// localStorage puede fallar (modo privado, datos bloqueados): el menú sigue funcionando
function remember(collapsed) {
    try {
        localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
    } catch {
        // Sin almacenamiento solo se pierde la preferencia al recargar
    }
}

/**
 * Barra lateral contraíble (escritorio). Con la barra contraída solo se ven
 * los íconos, así que el nombre de cada sección aparece como tooltip.
 */
export function initSidebar() {
    const toggle = document.querySelector('[data-sidebar-toggle]');

    if (!toggle) {
        return;
    }

    const links = document.querySelectorAll('[data-sidebar-tooltip]');

    const syncTooltips = () => {
        const collapsed = root.classList.contains('sidebar-collapsed');

        links.forEach((link) => {
            if (collapsed) {
                Tooltip.getOrCreateInstance(link, { placement: 'right' });
            } else {
                Tooltip.getInstance(link)?.dispose();
            }
        });

        toggle.setAttribute('aria-expanded', String(!collapsed));
    };

    toggle.addEventListener('click', () => {
        const collapsed = root.classList.toggle('sidebar-collapsed');
        remember(collapsed);
        syncTooltips();
    });

    syncTooltips();
}
