# Auditoría de la revisión (calidad web y accesibilidad)

Páginas: Inicio, Productos, una ficha (`/productos/w22-iec-carcasa-hierro`), Cómo comprar, Nosotros, Contacto, Carrito e Ingresar, a **375, 768 y 1440 px**. Capturas de página completa en esta carpeta (`<página>-<ancho>.jpg`) y datos crudos en `auditoria.json`. Se tomaron contra la API local con los datos del seeder (incluye las 2 promociones de ejemplo, que solo existen en local).

## Cómo se midió

- **a11y-audit:** `a11y_scanner.py` sobre `cliente/src` y `contrast_checker`. El escáner analiza archivo por archivo, así que sus 320 avisos son casi todos falsos positivos (pide `<main>` y enlace de salto en cada componente, cuando viven en `Layout`; marca `<select>` dentro de un `<label>` y cantidades con `aria-label`; el video del hero es decorativo y sin audio). Por eso se verificó además **en el navegador con axe-core 4.10** (reglas WCAG 2.0/2.1/2.2 A y AA) sobre las 24 combinaciones.
- **web-quality-audit:** Lighthouse no está instalado en este entorno; se comprobó a mano lo que cubre: `lang`, `<title>`, meta descripción, un solo `<h1>`, `alt` y `width/height` en imágenes, errores de consola, desbordamiento horizontal, objetivos táctiles menores de 24 px y que no aparezca «Espacio reservado» ni «Por confirmar».

## Resultado final

| Comprobación | Resultado |
|---|---|
| Violaciones axe (A y AA) | **0** en las 24 combinaciones |
| `<h1>` por página | 1 en todas |
| Desbordamiento horizontal | Ninguno |
| Errores de consola | Ninguno |
| Imágenes sin `alt` o sin `width/height` | 0 |
| Texto «Espacio reservado» / «Por confirmar» visible | No aparece |
| Objetivos menores de 24 px | Solo un enlace dentro de una frase en Ingresar (exento: enlace en línea) |

## Lo que reportó y se corrigió

| Hallazgo | Corrección |
|---|---|
| Títulos grandes con énfasis naranja `#f26a00` sobre lavanda: 2,81:1 (mínimo 3:1 para texto grande) | Nueva variable `--accent-title: #dc5a00` (3,5:1); el hero sobre video oscuro conserva el naranja pleno |
| Número `01`–`08` de las tarjetas de familia en celular: naranja sobre blanco, 3,07:1 (texto pequeño) | Pasa a `--primary` |
| Fondo de la hoja de filtros con clic y sin teclado | Es decorativo (`aria-hidden`); Escape ya cierra la hoja |
| Menú fijo tapaba las etiquetas de sección al hacer scroll | `scroll-margin-top` global en `[id]` |
| Botón de WhatsApp tapaba controles y el pie | 48 px (40 px en celular), sobre la barra inferior o de compra y con espacio reservado en el pie |
| Movimiento automático sin control de pausa | La banda de marcas se pausa con hover, foco y botón; el hero ya tenía botón de pausa y respeta `prefers-reduced-motion` |

## Contrastes de los colores nuevos

| Par | Razón |
|---|---|
| `muted` sobre blanco / lavanda 2 | 6,91 / 5,51 |
| Verde de «En bodega» sobre su fondo | 4,71 |
| Naranja claro sobre el vidrio oscuro del hero | 9,03 |
| `ink` sobre el naranja de los botones | 5,19 |
| Blanco sobre `primary` | 8,85 |

## Lo que no se pudo medir

- Puntajes de Lighthouse (rendimiento real, LCP, CLS) y lector de pantalla real: requieren herramientas que no hay en este entorno. Conviene correr Lighthouse en la URL de Cloudflare Pages después del despliegue.
- El video del hero no lleva pista de subtítulos: es un clip decorativo, sin audio y oculto a tecnologías de apoyo.
