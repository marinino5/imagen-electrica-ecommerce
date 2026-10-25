/**
 * Mejoras del formulario de login: mostrar u ocultar la contraseña, aviso de
 * bloqueo de mayúsculas y botón con estado de carga. Sin JavaScript el
 * formulario sigue funcionando normal.
 */
export function initLoginForm() {
    const form = document.querySelector('[data-login-form]');

    if (!form) {
        return;
    }

    const password = form.querySelector('[data-password-input]');
    const toggle = form.querySelector('[data-password-toggle]');
    const capsWarning = form.querySelector('[data-caps-warning]');
    const submit = form.querySelector('[data-submit-button]');

    toggle.addEventListener('click', () => {
        const visible = password.type === 'password';

        password.type = visible ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', String(visible));
        toggle.setAttribute('aria-label', visible ? 'Ocultar contraseña' : 'Mostrar contraseña');
        toggle.querySelector('.bi').className = visible ? 'bi bi-eye-slash' : 'bi bi-eye';
        password.focus();
    });

    // getModifierState solo existe en eventos de teclado y mouse
    const updateCapsLock = (event) => {
        if (typeof event.getModifierState === 'function') {
            capsWarning.hidden = !event.getModifierState('CapsLock');
        }
    };

    password.addEventListener('keydown', updateCapsLock);
    password.addEventListener('keyup', updateCapsLock);
    password.addEventListener('blur', () => {
        capsWarning.hidden = true;
    });

    // Evita doble envío y muestra que se está procesando
    form.addEventListener('submit', () => {
        submit.disabled = true;
        submit.setAttribute('aria-busy', 'true');
        submit.querySelector('[data-loading-spinner]').hidden = false;
        submit.querySelector('[data-button-text]').textContent = 'Ingresando…';
    });
}
