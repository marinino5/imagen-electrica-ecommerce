/**
 * Al buscar o filtrar un listado (envío del formulario o clic en un chip) la tabla se cubre con un esqueleto de carga hasta que la nueva
 * página llega: la persona ve que algo está pasando en vez de una pantalla congelada. No hace falta JavaScript para que el filtro funcione.
 */
export function initSkeleton() {
    const card = document.querySelector('[data-skeleton-target]');

    if (!card) {
        return;
    }

    const show = () => {
        if (card.querySelector('.skeleton-rows')) {
            return;
        }

        const rows = document.createElement('div');
        rows.className = 'skeleton-rows';
        rows.setAttribute('aria-hidden', 'true');
        rows.innerHTML = Array.from({ length: 6 }, () => '<div class="skeleton-row"><span></span><span></span><span></span></div>').join('');
        const status = document.createElement('span');
        status.className = 'visually-hidden';
        status.setAttribute('role', 'status');
        status.textContent = 'Cargando resultados…';

        card.querySelector('.table-responsive, .empty-state, .card-body + *')?.classList.add('is-loading');
        card.append(rows, status);
    };

    card.querySelectorAll('form[role="search"]').forEach((form) => form.addEventListener('submit', show));
    card.querySelectorAll('.chip').forEach((chip) => chip.addEventListener('click', (event) => {
        if (!event.metaKey && !event.ctrlKey) {
            show();
        }
    }));
}

/** La tecla «/» lleva el foco al buscador global (como en las herramientas de trabajo), salvo si ya se está escribiendo en un campo. */
export function initSearchShortcut() {
    document.addEventListener('keydown', (event) => {
        const target = event.target;
        const typing = target instanceof HTMLElement && (target.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName));

        if (event.key === '/' && !typing && !event.metaKey && !event.ctrlKey) {
            const input = document.getElementById('global-search');

            if (input) {
                event.preventDefault();
                input.focus();
                input.select();
            }
        }
    });
}
