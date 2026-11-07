# Sistema visual de la tienda (cliente React)

Documenta lo que **ya está construido** en `cliente/src/styles/` (no es una propuesta). Los valores viven en `variables.css` y `rd.css`; las vistas nunca llevan colores sueltos.

## Color

| Token | Valor | Uso |
|---|---|---|
| `--primary` | `#513e84` | Encabezados de acento, enlaces, estados activos, botón «Solicitar cotización» (contorno/sólido) |
| `--primary-dark` | `#463671` | Hover del violeta |
| `--ink` | `#1e1631` | Texto, títulos y secciones oscuras; es el texto de todo botón naranja |
| `--night` | `#170f29` | Violeta casi negro: capas sobre fotos, pie, fondo oscuro del reverso de las tarjetas de sector |
| `--accent` | `#f26a00` | Llamado principal («Agregar al carrito», etiquetas «-X %»); siempre con texto `--ink`, nunca blanco |
| `--accent-title` / `--accent-text` | `#dc5a00` / `#b84800` | Naranja para títulos grandes y para texto pequeño sobre claro (contraste AA) |
| `--accent-light` | `#ffb27a` | Naranja claro sobre fondos oscuros |
| `--lavender` / `--lavender-2` / `--lav-3` | `#f6f4f9` / `#e9e3f4` / `#dcd3ec` | Fondos suaves, bordes, chips, contornos |
| `--lav-soft` | `#f4f1fa` | **Lavanda muy suave** de las secciones alternas y del fondo de las imágenes de producto |
| `--muted` | `#5d5672` | Texto secundario (AA sobre blanco y lavanda) |
| `--success` / `--success-tint` | `#1e7a3a` / `#e3f4e8` | «En bodega» |

**Fondos y ritmo.** Ninguna página queda en blanco plano: `body` pinta un degradado blanco → `--lav-soft` en la primera pantalla. Las secciones alternan `.bg-white` (blanco casi plano), `.bg-lav` (degradado blanco → lavanda), `.bg-soft` (lavanda plana) y una franja oscura con foto (`.adv`, pie). Texto blanco solo sobre `--primary`, `--ink` o `--night`. Gráficos del panel: solo `chart-*` (azul, verde, rojo, amarillo, gris); nunca el morado ni el naranja de marca.

## Tipografía

- Títulos: **Barlow Condensed** (600–800), mayúscula solo en encabezados grandes (`.rd-h2`, `clamp(2.2rem, 4.2vw, 3.9rem)`, `line-height: .98`). Palabra clave en naranja con subrayado que se dibuja (`Hl`).
- Texto: **Inter**. Nombres de producto y texto corrido en minúscula normal; cifras con `tabular-nums`.
- Etiqueta de sección: 12.5 px, 700, `letter-spacing: .16em`, con línea naranja de 36 × 3 px y número (`SectionLabel`).

## Espaciado, forma y sombra

- Contenedor `.wrap`: 1280 px máx., gutter 16 px (el logo del encabezado se alinea a este borde). Secciones: `padding-block: clamp(64px, 8vw, 112px)`.
- Radios: `--rd-radius` **8 px** para tarjetas, imágenes, paneles y botones de interfaz; `--radius-pill` para botones y chips; el arco de las cápsulas de foto es el único radio grande.
- Sombras teñidas de violeta: `--shadow-sm`, `--shadow`, `--shadow-lg`. La elevación de tarjetas (`.lift`) es sutil: 3 px y una sombra corta (`0 14px 30px -18px`), solo con puntero fino.
- Encabezado: 84 px en escritorio, 68 px en celular; logo de 56 px (40 px en celular).

## Imagen de producto (formato único)

Catálogo, miniaturas, mega-menú, bento y ficha usan la misma imagen: **WebP con fondo transparente, 1000 × 1000 px** (más una versión `-sm` de 480 px), producto como máximo al **78 %** del lienzo, centrado y apoyado en la misma línea base (`y = 880`), con la **misma sombra de contacto** suave. Se genera con `cliente/scripts/normalizar-producto.py` (rembg + Pillow) y vive en `cliente/public/img/catalogo/`. Siempre sobre un fondo `radial-gradient(#fff → --lav-soft)`; nunca con `mix-blend-mode`.

Fotos de ambiente (`cliente/public/img/fotos/`, `aplicaciones/`): todas pasan por `cliente/scripts/gradar-foto.py` (tono ligeramente frío, contraste 1.07, saturación .93). Los sectores se recortan a 4:5 con punto focal.

## Componentes

- **Botones:** «Agregar al carrito» naranja (texto `--ink`), «Solicitar cotización» contorno `--primary`. Altura mínima 44 px (58 px en la ficha). Presión: `scale(.97–.98)` en 160 ms.
- **Tarjeta de línea** (`.pc`): imagen recortada grande sobre lavanda suave, marca con logo, nombre, hasta 3 chips sin duplicados, «Desde» y botón.
- **Tarjeta de oferta** (`.deal`): producto recortado y, debajo, precio anterior tachado, precio nuevo grande, etiqueta naranja «-X %» y vigencia; sin promoción lleva «Tendencia».
- **Sector** (`.sx`): tarjeta 4:5 que es un botón (`aria-pressed`) y gira 180° en 500 ms con perspectiva 1200 px; reverso oscuro con 3 equipos y enlace. Con `prefers-reduced-motion` cambia por fundido.
- **Ficha:** dos columnas (imagen con zoom / decisión de compra en el orden marca → nombre → descripción → referencia → precio → disponibilidad → cantidad → botones) y pestañas accesibles (`role="tablist"`).
- **Mega-menú:** 8 familias en 4 columnas, miniatura 72 px y lista vertical de categorías.
- **Acceso:** tarjeta centrada de 460 px sobre el degradado, con un arco naranja fino de adorno.

## Movimiento

Curva única `--ease-out: cubic-bezier(.22, 1, .36, 1)`. Interfaz entre 160 y 300 ms; revelados de 450–600 ms. Solo `transform` y `opacity`. El revelado **no depende del scroll**: lo que está en pantalla o por encima se muestra al montar, hay IntersectionObserver con `rootMargin` del 12 % y un respaldo a los 1,5 s. Hover solo con puntero fino. `prefers-reduced-motion` desactiva giros, parallax y flotado.
