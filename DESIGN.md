# Sistema visual de la tienda (cliente React)

Documenta lo que **ya está construido** en `cliente/src/styles/` (no es una propuesta). Los valores viven en `variables.css`, `rd.css` y `ie.css`; las vistas nunca llevan colores sueltos. **`ie.css` (rediseño 4) manda sobre lo anterior** donde se contradicen: ver la sección «Rediseño 4» al final.

## Color

| Token | Valor | Uso |
|---|---|---|
| `--primary` | `#4A3580` | Encabezados de acento, enlaces, estados activos, botón «Solicitar cotización» (contorno/sólido) |
| `--primary-dark` | `#3A2A68` | Hover del violeta |
| `--ink` | `#1C1630` | Texto, títulos y secciones oscuras; es el texto de todo botón naranja |
| `--night` | `#170f29` | Violeta casi negro: capas sobre fotos, pie, fondo oscuro del reverso de las tarjetas de sector |
| `--accent` | `#f26a00` | Llamado principal («Agregar al carrito», etiquetas «-X %»); siempre con texto `--ink`, nunca blanco |
| `--accent-title` / `--accent-text` | `#dc5a00` / `#b84800` | Naranja para títulos grandes y para texto pequeño sobre claro (contraste AA) |
| `--accent-light` | `#ffb27a` | Naranja claro sobre fondos oscuros |
| `--lavender` / `--lavender-2` / `--lav-3` | `#f6f4fb` / `#e6e2f3` / `#d6d0ea` | Fondos suaves, bordes, chips, contornos |
| `--lav-soft` | `#f3f1fa` | **Lavanda muy suave** de las secciones alternas y del fondo de las imágenes de producto |
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
- Encabezado: 84 px en escritorio (baja a 68 px al hacer scroll, 240 ms) y 68 px en celular. Logo recortado al borde del dibujo (`logo-ajustado.png`): 64 px visibles en escritorio (52 px con scroll) y 44 px en celular. Botón «Cotizar» (contorno violeta-500) junto al carrito.

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


## Rediseño 4 (`ie.css`, `ie-*.css`)

### Tokens y roles

Comprobados contra el logo (píxeles del PNG: naranja ≈ `#F86800` tras cuantizar, violeta ≈ `#4A3580`) y contra `CLAUDE.md`. Donde el valor pedido no coincidía con el de marca, manda la marca.

| Token | Valor | Rol | Nota |
|---|---|---|---|
| `--ie-violet-950` | `#1C1630` | Footer y texto de máximo contraste | = `--ink`; coincide con el ≈`#1C1630` pedido |
| `--ie-violet-700` | `#4A3580` | Bloques extendidos y titulares oscuros | = `--primary` (violeta neutro, afín al pie `#1C1630`) |
| `--ie-violet-500` | `#4A3580` | Iconos, bordes activos, enlaces | nuevo |
| `--ie-lila-200` | `#e6e2f3` | Pedestales, bordes de tarjeta, filas alternas | = `--lavender-2` |
| `--ie-lila-100` | `#f3f1fa` | Fondos de sección | = `--lav-soft` |
| `--ie-lila-50` | `#faf9fd` | Fondos de tarjeta suaves y de campos | nuevo |
| `--ie-orange-500` | `#f26a00` | CTAs, arcos, etiquetas, subrayados | naranja del logo |
| `--ie-orange-100` | `#ffe6d3` | Etiquetas de descuento y filas seleccionadas | = `--orange-tint` |

**Fondos de sección** (nunca `#fff` liso): `bg-lila-fade` (blanco → lila-100), `bg-lila-soft` (lila-100 + puntos de 1,5 px cada 24 px al 5 %) y `bg-lila-arc` (fade + arco naranja de 1,5 px que sale por el borde derecho; `--arc-top` ajusta dónde). Las clases viejas `bg-white`, `bg-lav`, `bg-soft` y `bg-warm` ahora apuntan a estas tres.

### Tipografía
H1 Barlow Condensed 700, 40–88 px, interlineado .92, mayúsculas, tracking −0,5 px (`.ie-h1`). H2 44–56 px. Titulares de pesos mezclados con `Mix`: primera parte en 300 y segunda en 700. Etiqueta de sección Inter 600, 12 px, tracking 2 px, línea naranja de 28 × 2 px. Cuerpo Inter 17 px / 1,6, máximo 62 caracteres. Subrayado de la palabra clave: 4 px, separado 6 px, crece en 600 ms.

### Componentes nuevos
- **Bloque extendido** (`Xb`): rectángulo violeta-700 que nace en el borde de la pantalla y llega al 38 % de la foto; la foto (radio 28 px, sombra `0 24px 48px`) entra desde su lado, el texto desde el contrario y el arco naranja se dibuja. Tiempos: bloque 700 ms, foto 650 ms (+120), texto 550 ms (+240), arco 900 ms (+400). Variantes `side="l|r"`, `arch` y `col`. Con `prefers-reduced-motion` solo hay fundido de 200 ms. Se usa en Inicio (Asesoría, Entrega y cierre), Cómo comprar, Contacto y Nosotros.
- **Pedestal** (`Pedestal`, `.ped`): elipse lila-200 de 14 px de alto (70 % del producto) con borde superior blanco al 60 % y sombra de contacto.
- **Tarjetas de canal** (`ChannelCards`): WhatsApp naranja sólido con **texto blanco grande y en negrita** (3,07:1, AA solo para texto grande; es la excepción pedida a la regla «naranja con texto ink»), Correo y Llamada en blanco con borde lila-200 que pasa a naranja al hover.
- **Visítanos** (`VisitFull`): mapa a ancho completo con tarjeta blanca flotante.
- **Revelado** (`useInView`): umbral 0,15, margen −8 %; lo que ya está en pantalla al cargar se muestra sin animar; respaldo a los 1,5 s.
- **Movimiento global:** tarjetas −4 px y escala 1,04 en 240 ms; flecha de los botones +4 px; presión `scale(.98)`. Sin rebotes, giros (salvo el flip de Sectores) ni parpadeos; ninguna entrada pasa de 900 ms (los bucles de marcas y mapa son continuos).

### Pantallas
Inicio: hero con producto sin círculo (brillo naranja, arco de 2 px, sombra elíptica, píldora), sectores 4 + 3 con SolarWEG, marcas sin franja blanca (logos al 45 %) y cierre claro con pie de esquinas curvas de 48 px. Catálogo: banner «vitrina» de 520 px con cinco productos en escalera. Ficha: panel de imagen con pedestal y arco, tarjeta de compra sticky, pestañas con indicador deslizante, especificaciones y ficha técnica a ancho completo. Acceso: franja violeta con tres productos. Nosotros: «25» en contorno que se rellena, galería horizontal fijada e hitos con línea que se llena. `/cotizar`: cotización libre en dos pasos.


## Ajustes finales (recurso firma por página)

- **Fondos con vida** (`Bgfx`, `styles/bgfx.css`): `a` dibujo técnico de línea (motor, engranaje, contactor, plano; 1 px, violeta-500 al 7 %), `b` malla de color (lila-200 + naranja al 10 %), `c` arco naranja de 1,5 px que se dibuja, `d` cuadrícula de plano de 32 px con máscara radial, `e` banda diagonal lila-100 a 6°, `f` esquineros en L de 24 px (`.tc`). Máximo dos por sección y nunca la misma combinación en dos seguidas (tabla en `docs/propuesta-rediseno.md`).
- **Inicio:** hero video → imagen → video → imagen, con tinte violeta-700 al 18 % (`multiply`) y velo `linear-gradient(90deg, …)`. Entrada: el texto se desliza de lado. Tarjetas con «Ver ficha» en contorno y botón circular naranja de 44 px (`QuickAdd`: directo, popover o hoja inferior).
- **Cómo comprar:** «circuito» (línea naranja de 3 px que serpentea y se enciende con el scroll; bornes de 64 px con tarjetas de radio 24 px y esquineros).
- **Nosotros:** «galería editorial» (fachada 16:10 con capa violeta desplazada 24 px, dos cifras, dos marcos 4:3 reservados). Entrada: máscara de abajo hacia arriba en 700 ms.
- **Contacto:** «tarjeta de visita» (franja naranja de 8 px, logo y tres canales en columnas). Entrada: fundido con escala 0,98 → 1.
- **Ingresar / Crear cuenta:** «panel limpio» (tarjeta de 460 px, malla de color + motor al 6 % + arco fino, fila de 4 logos en gris). Entrada: la tarjeta sube 16 px con fundido.
- **Mapa «Visítanos»:** dentro de un contenedor de radio 28 px, con tarjeta flotante y 120 px de margen inferior.
- **Sin inventario a la vista:** no se muestra «disponibilidad», «en bodega» ni unidades; el backend sigue validando y la tienda ofrece «Cotizar esta cantidad».

## Ajustes de detalle

- **Inicio:** el velo del hero es solo `linear-gradient(90deg, rgba(20,16,36,.9) → transparent 80 %)` (sin tinte ni `hue-rotate`); línea naranja de 2 × 120 px junto al texto; la sección de familias sube 56 px sobre el hero (radio 40 px, sombra hacia arriba). Tarjeta de familia = un solo enlace (`::after`).
- **Catálogo:** «Todo el catálogo» agrupado por familia con cabecera fija (72 px, violeta-950 al 95 %; 56 px en celular), scrollspy en columna (264 px, sticky a 96 px) y chips. Tarjeta de alto uniforme; franja editorial de 220 px, una por familia.
- **Cuenta:** tarjeta de 460 px a la izquierda (55 %) y «riel de producto» a la derecha (45 %, dos carriles de 40 s en sentidos opuestos). Recuperar contraseña en `/recuperar` y `/restablecer`.
- **Contacto:** hero oscuro de 420 px, cuatro mosaicos 2 × 2, formulario solo para dos motivos (altura animada 300 ms), tarjeta de visita sobre banda diagonal y dirección con mapa estático (sin iframe).
- **Nosotros:** muro de producto (12 baldosas + central 2 × 2), tres pilares con foto al 60 %, cierre con arco naranja grande y sin mapa. Las imágenes del catálogo se recortan con alpha matting (ver `docs/auditoria-imagenes.md`).


## Fase 1A: barra superior, fondos por tipo y cuadros de color (`fase1.css`)

- **Barra superior por página.** Heros oscuros (Inicio, Contacto): transparente con texto y logo blancos. Heros claros: blanca al 100 % con texto oscuro. Con scroll (o con el mega-menú abierto): blanco al 85 % con desenfoque de 16 px. La lista de páginas oscuras es `DARK_HERO` en `Header.jsx`; `Layout.jsx` quita el margen superior en esas páginas.
- **Cinco fondos de sección** (`.bgt.bgt-a` … `.bgt-e`, nunca dos seguidos iguales): **A** blanco con textura de puntos; **B** lila-100; **C** degradado diagonal violeta-700 → violeta-500 con tarjetas blancas encima y texto blanco; **D** naranja cálido (`--ie-orange-50` → `--ie-orange-100`) con arco naranja; **E** violeta-950, uno por página y nunca pegado al pie. Secuencias: Inicio A·C·D·B·A·B·D; Catálogo B·A; Cómo comprar A·B·A·C·D·A·B; Contacto E·A·D·B; Nosotros A·B·A·D·B·A·D; Ingresar E (banda superior) + B.
- **Cuadros de color** (`Squares.jsx`): máximo 2 por sección, rectángulos redondeados de 120–240 px en lila-200, naranja-100 o violeta-500 al 15 % detrás de fotos y tarjetas, con parallax leve (`--sq-c` lo escribe `useScrollFrame`). Con movimiento reducido no se mueven.
- **Ingresar / Crear cuenta / Recuperar**: dos bandas (violeta-950 arriba con la «e» del logo en contorno al 6 % y un arco naranja; lila-100 abajo), tarjeta de 520 px a caballo con sombra profunda y tres productos que asoman detrás (solo desde 768 px). En celular, banda de 160 px y la tarjeta debajo.
- **Nosotros · órbita de producto**: 12 productos de 64 a 160 px flotan (±6 px, 4 a 6 s) alrededor del bloque central en fondo C y se acercan a su órbita mientras la sección entra, cada uno a su velocidad.
- **Saltos de scroll**: `scroll-behavior` quedó en `auto` (el salto es instantáneo) y `useInView` fuerza a mostrar todo lo que toque la pantalla 180 ms después de que el scroll se detenga; los bloques extendidos (`Xb`) respetan `instant` y la rejilla de Sectores ya no espera a un revelado.
