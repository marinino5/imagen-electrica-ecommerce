/**
 * Editor de especificaciones como pares clave-valor. Cada fila son dos campos
 * (specs[n][key], specs[n][value]); el botón agrega filas nuevas a partir
 * del <template> y el botón de cada fila la quita.
 */
export function initSpecsEditor() {
    const editor = document.querySelector('[data-specs-editor]');

    if (!editor) {
        return;
    }

    const rows = editor.querySelector('[data-specs-rows]');
    const template = editor.querySelector('template');
    let next = rows.children.length;

    const addRow = () => {
        rows.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', next++));
    };

    editor.querySelector('[data-specs-add]').addEventListener('click', addRow);

    rows.addEventListener('click', (event) => {
        event.target.closest('[data-specs-remove]')?.closest('[data-specs-row]').remove();
    });
}
