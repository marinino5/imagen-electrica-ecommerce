# Propuesta de rediseño (revisión completa del sitio)

Este documento es el paso previo del rediseño: qué recomendó cada skill, las 6 referencias elegidas con la idea concreta que se toma de cada una y cómo se aplica en cada página. Donde no pude comprobar algo en vivo, lo digo.

**Identidad que no se negocia** (la fijó el encargo y `CLAUDE.md`): blanco a lavanda, violeta y naranja de marca, arcos naranjas, Barlow Condensed + Inter. Moderno y con vida, sin saturar de morado. Una imagen nunca aparece en dos lugares.

## 1. Qué recomendó cada skill

| Skill | Recomendación clave | Cómo se aplicó |
|---|---|---|
| `frontend-design` | Partir del sujeto (distribuidora industrial en Bucaramanga) y no de plantillas; una sola cosa memorable, el resto sobrio; evitar el acento en una sola palabra, las etiquetas en mayúsculas sobre todo título y los numerales decorativos | La cosa memorable es el hero a pantalla completa con el cableado real y el vidrio oscuro con el mensaje. Los números `01`–`08` de las familias se conservan porque **sí** son una secuencia (el portafolio) |
| `high-end-visual-design` | Doble bisel, botones píldora con el icono en círculo interno, curva `cubic-bezier(0.32,0.72,0,1)`, `100dvh`, animar solo `transform` y `opacity`, blur solo en elementos fijos | Ya existían (`.bz`, `Pill`, `--ease-sys`). El hero usa `100svh`; el vidrio del hero es la única excepción al blur solo-en-fijos |
| `redesign-existing-projects` | Escanear, diagnosticar y mejorar con el stack actual, sin migrar; evitar tres columnas iguales, tarjetas con borde y sombra en todo, secciones planas sin profundidad | Se mantuvo React + CSS propio. Bento asimétrico en vez de seis tarjetas iguales; sección de asesoría con arcos; fichas sin foto con diseño en lugar de recuadros vacíos |
| `design-taste-frontend` | Dials 8/6/4 (variación, movimiento, densidad), Grid antes que flex-math, estados de carga/vacío/error, sin Inter ni morado | Se respetaron los dials y los estados. **Se descartó** la prohibición de Inter y del morado: son la marca. El morado se redujo (la sección de asesoría pasó a fondo claro; solo quedan la banda de cotizar y el pie oscuros) |
| `anti-ui-slop` | Diseñar desde el producto, definir los estados y pasar una revisión final con captura | Capturas en tres anchos en `docs/capturas/revision/`; cada pantalla tiene carga, vacío y error |
| `emil-design-eng` | Decidir primero si algo debe animarse y con qué frecuencia; UI por debajo de 300 ms con `ease-out` fuerte; `scale(0.97)` al presionar; hover solo con puntero fino; respetar `prefers-reduced-motion` | El loop de marcas se pausa con hover, foco y botón; el contador anima una vez; las categorías de cada familia suben solo con hover/foco; nada se anima con teclado ni en acciones repetidas |
| `ui-ux-pro-max` | Sistema de diseño por tipo de producto, prioridad de accesibilidad y toque de 44 px | Su salida (Enterprise SaaS, navy + azul, Rubik/Nunito Sans) **no encaja** con la marca y se descartó; se conservó su lista de comprobación: contraste 4,5:1, foco visible, 375/768/1024/1440 px |
| `web-design-guidelines` | Reglas de la guía de interfaces web: `aria-label` en botones de icono, `:focus-visible`, `scroll-margin-top` en anclas, `tabular-nums`, `text-wrap: balance`, pausa para el movimiento automático, `width/height` en imágenes | Aplicadas: ver `docs/capturas/revision/auditoria.md`. No se aplicó «Title Case» (es una regla del inglés; en español se usa mayúscula inicial) |

**Superdesign:** no está disponible en este entorno (ni como skill ni como herramienta), así que no se maquetó con él. Las pantallas se diseñaron con esta propuesta, se construyeron y se revisaron con capturas reales en 375, 768 y 1440 px. Si quieres los bocetos aprobados en Superdesign antes de seguir, hay que hacerlos a mano.

## 2. Las 6 referencias y la idea que se toma de cada una

La etiqueta **[visto]** indica que abrí la página y confirmé lo descrito; **[no verificado]** que no pude abrirla desde aquí y la idea viene de conocimiento general del patrón.

| Referencia | Idea concreta | Estado |
|---|---|---|
| **McMaster-Carr** | El catálogo es un árbol navegable de categorías, con miniatura y número de productos, y el encabezado siempre trae búsqueda, teléfono y contacto. Densidad alta, casi sin adorno | [visto] 28 categorías con miniatura, listas densas de enlaces, teléfono y correo en el encabezado, tarjetas de descubrimiento con el conteo de productos |
| **RS Components** | Cada familia es un bloque con imagen y nombre que, al pedirlo, despliega sus subcategorías («Show 32 subcategories») | [visto] bloques destacados con imagen, enlace y desplegable de subcategorías; banners con «Shop now» |
| **Schneider Electric** | Líneas insignia en tarjetas, soluciones por industria o aplicación, y accesos rápidos al selector de productos y a WhatsApp | [visto] «Productos diseñados para usted», soluciones por industria, «Inicia el selector de productos» y canal de WhatsApp |
| **Grainger** | La ficha lista cada referencia en una tabla con disponibilidad y precio, y el botón de compra en la misma fila | [no verificado] la página devolvió un error de servidor al consultarla |
| **WEG** | Ficha técnica con datos clave en bloques y documentos a mano; el catálogo ordena por familia de producto | [no verificado] `weg.net` responde 403 a cualquier consulta automática |
| **ABB** | Sobriedad: casi todo blanco, un solo color de acento y aire entre bloques; navegación por familia | [no verificado] `new.abb.com` no respondió |

**Galerías (Awwwards, Godly, Land-book):** la búsqueda no devolvió páginas de esas galerías filtradas por comercio electrónico industrial o B2B, y no puedo navegarlas con filtros desde aquí. **No tomé ideas de ellas** y no las cuento entre las 6.

## 3. Cómo se aplica cada idea en cada página

| Página | McMaster-Carr | RS Components | Schneider Electric | Grainger | WEG | ABB |
|---|---|---|---|---|---|---|
| **Inicio** | Cada familia lleva su número real de fichas; la banda de marcas está justo bajo el hero | Bento de las 8 familias: portada, contador y, al pasar el mouse o enfocar, las categorías que contiene | «Soluciones por aplicación» y la sección de asesoría con botón de WhatsApp como canal principal | Las promociones muestran precio de lista y precio de oferta juntos | Las cifras reales (referencias, fichas, familias) viven solo aquí, en el vidrio del hero | Fondo claro en promociones y asesoría; un solo acento (naranja) para las acciones |
| **Productos** | Árbol de familias → categorías con contadores arriba, visible sin scroll; encabezado compacto con búsqueda | Las categorías se despliegan al elegir la familia; marca con «Ver más» | Búsquedas frecuentes como atajos | Filtros de disponibilidad y de forma de compra | Potencia y corriente solo donde son datos técnicos de la familia (motores y variadores, control) | Tarjetas blancas con aire, sin el widget del carrito en la columna |
| **Ficha** | Referencia, SKU y precio alineados en columnas | Las familias relacionadas llevan su propia imagen | Botón «Cotizar» distinto de «Agregar» según se pueda comprar | Tabla de referencias con etiqueta «En bodega: N» o «Por pedido», precio y botón por fila | Datos técnicos en tarjetas con icono; los campos vacíos no se muestran | Fila elegida con borde y fondo naranja suave, no color oscuro |
| **Cómo comprar** | Pasos cortos con el verbo primero | Resumen de 4 pasos en el Inicio con enlace a la guía | Camino de cotización y de compra separados, con WhatsApp como atajo | El paso 3 dice exactamente lo que ocurre hoy: un asesor confirma pago y entrega | Los filtros del paso 1 coinciden con los de verdad | Ilustraciones de la interfaz (chat y seguimiento) en vez de recuadros vacíos |
| **Nosotros** | Servicios en lista corta y sin cifras repetidas | Tarjetas de servicio con icono | Atención en tienda con cómo llegar | Qué hace la empresa por el cliente, en lenguaje de compra | La fachada real como imagen principal | Una sola portada clara y sobria |

## 4. Decisiones que cambian lo que veías

- **El hero ya no es una caja con cifras pegada.** Video (o diseño de marca) a pantalla completa, texto sobre degradado y el mensaje «Una distribuidora de Bucaramanga que conoce cada referencia que vende» en vidrio oscuro con contador animado.
- **Cifras fijas, fuera.** Nada de `|| 351` ni «8 familias» escrito a mano: salen de `/families` (`meta.references`, `meta.lines`, `meta.families`). Si la API no responde, no se muestra ninguna cifra.
- **Ficha sin foto = diseño, no recuadro vacío.** Muestra el arco de marca, el icono de su familia y su dato clave («9–95 A»), así dos fichas de la misma familia no se ven iguales.
- **Fotos de ambiente y videos de motores/extractores:** no hay material con licencia y calidad suficiente; ver `docs/imagenes-pendientes.md`.


## 5. Rediseño 3 (esta entrega): qué se tomó de dónde

Todo se construyó con el sistema de `cliente/src/styles/rd.css` y los componentes de `cliente/src/components/ui/` (`Reveal`, `Parallax`, `Arc`, `Hl`, `SectionLabel`, `Counter`).

**Lo que sí se pudo revisar y lo que no.** `weg.net` y Siemens bloquean la navegación automatizada, ABB no cargó, y Awwwards, Godly, Framer y ThemeForest no se recorrieron con filtros. Las ideas de esas referencias vienen del patrón que describió el encargo, no de una revisión en vivo. Superdesign no existe en este entorno y no se maquetó con él.

| Idea | Origen (encargo) | Dónde se usó |
|---|---|---|
| Paneles verticales de foto que se expanden con el mouse | WEG | Inicio, «Soluciones por aplicación» |
| Tarjetas en cápsula (un lado redondeado) con foto y texto | WEG | Inicio (bloques de marca), Cómo comprar, Nosotros |
| Fila de sectores en scroll horizontal con texto sobre degradado | ABB | Inicio, «Sectores» |
| Fondo claro, mucho aire y un solo acento | ABB / Schneider | Todas las páginas |
| Mega-menú con las familias, su foto y sus categorías | Schneider / ABB | Encabezado |
| Pie con pocas columnas, aire, suscripción y medios de pago | stradabrand.co (solo la idea) | Pie de todas las páginas |
| Imagen y texto que entran desde lados contrarios | Efecto elegido por la usuaria | Inicio, Cómo comprar, Nosotros, Contacto |
| Parallax suave, máscara de abajo hacia arriba, arcos que se dibujan, subrayado que se dibuja | Encargo | Inicio, Nosotros, Cómo comprar |

**Sobre las skills.** No se cargaron una por una con la herramienta de skills: las decisiones de diseño salen de las guías ya resumidas en la sección 1 de este documento (`frontend-design`, `high-end-visual-design`, `emil-design-eng`, `ui-ux-pro-max`, `web-design-guidelines`) y de la auditoría con axe-core. La auditoría con `web-quality-audit` no se corrió.


## 6. Rediseño nivel 2: qué se tomó de dónde

Sistema documentado en `DESIGN.md`. Flujo seguido: Taste Skill (dirección y referencias) → Impeccable (crítica, composición, acabado) → frontend-design + ui-ux-pro-max (implementación) → Emil (movimiento) → anti-ui-slop (patrones genéricos) → Playwright (capturas en `docs/capturas/revision-4/` y pruebas de interacción).

**Skills usadas y qué aportó cada una** (las tres nuevas se leyeron antes de usarlas; Impeccable se instaló en el proyecto con `npx impeccable install`, sin ganchos):

| Skill | Qué aportó | Dónde se ve |
|---|---|---|
| `design-taste-frontend` (Taste) | Evitar el «3 tarjetas iguales» y las composiciones centradas; estados completos; `transform`/`opacity` solamente; sombras teñidas. **Descartó** (por marca): la prohibición del violeta y de Inter | Tarjeta de sector con reverso; familias parejas; ficha asimétrica |
| `impeccable` (critique, layout, typeset, quieter, polish, audit) | Crítica de lo genérico (ver abajo), detector mecánico de «side-tab» y rejillas decorativas, reducción de efectos | Elevación de tarjetas más sutil; sin franjas laterales de color |
| `emil-design-eng` | Presión `scale(.97–.98)` en 160 ms, interfaz entre 160 y 300 ms con `ease-out`, hover solo con puntero fino, foco que acompaña a la cara visible | Flip de sectores, botones de la ficha, mega-menú |
| `anti-ui-slop` | Cubrir estados (carga, vacío, error) y quitar piezas sin sentido de producto | Quitar medios de pago, la cuadrícula «bento» desigual, las fotos de oficina |
| `frontend-design`, `ui-ux-pro-max` | Jerarquía clara en la ficha, contraste AA, objetivos táctiles de 44 px, 375/768/1440 px | Ficha, filtros, acceso |

**Referencias de sitios.** Se sigue usando lo ya documentado de WEG, Siemens, ABB y Schneider (secciones 2 y 3). `weg.net` y Siemens siguen bloqueando la navegación automática, así que esas ideas vienen del patrón y no de una revisión en vivo.

**htmlrev.com.** Se revisó el listado (no se abrieron las vistas previas ni la licencia de cada plantilla): el sitio afirma que sus plantillas son gratuitas para uso personal y comercial, pero cada una enlaza a su fuente. En la categoría Bootstrap aparecen plantillas de servicios industriales y de construcción (Industro, Electra, Spectrum, Apex, Boldiz). **No se copió código, diseño ni recurso de ninguna**; solo se confirmó que el patrón «cifras grandes sobre fondo oscuro con acento naranja» es habitual en el sector, y ya estaba en Nosotros. No se reutilizó ningún archivo de htmlrev.

**Figma MCP:** está conectado (herramientas `mcp__claude_ai_Figma__*`), pero no se usó: ninguna sección necesitó comparar composiciones fuera del navegador.

| Idea | Fuente | Página |
|---|---|---|
| Tarjeta que gira y muestra «para qué se usa» con 3 equipos recomendados | Soluciones por industria de Schneider Electric y WEG (patrón), flip 3D de Taste Skill | Inicio, Sectores y aplicaciones |
| Imagen de producto recortada en un formato único (mismo tamaño visual en cuadrícula) | Catálogos de McMaster-Carr y RS Components (miniaturas parejas) | Todo el sitio |
| Precio anterior tachado, precio nuevo grande y vigencia | Grainger (precio de lista junto al precio) | Inicio, Ofertas |
| Filtros mínimos: buscador, cómo comprar y marca; familias en acordeón | McMaster-Carr (árbol navegable) y ABB (sobriedad) | Catálogo |
| Ficha en jerarquía: imagen + decisión de compra, pestañas debajo | Grainger (tabla de referencias con compra en la fila), RS Components | Ficha |
| Ficha técnica dentro de la tienda y PDF propio sin datos del fabricante | Decisión de negocio (el PDF de WEG trae contactos y está en portugués) | Ficha |
| Capturas reales de la tienda en un celular | Práctica común en B2B SaaS; más fiel que una foto genérica | Cómo comprar |
| Cifras grandes y animadas sobre fondo oscuro | WEG (datos clave) | Nosotros |
| Tarjeta de acceso centrada con un arco fino | Marca (arcos del logo) | Ingresar y Crear cuenta |

### Crítica de Impeccable y lo que se corrigió

| Qué se veía genérico o «hecho por IA» | Corrección |
|---|---|
| Fondo plano blanco en todo el sitio | Degradado de marca blanco → lavanda y alternancia de blanco, lavanda suave y franja oscura con foto |
| Bento de familias con tamaños desiguales (Motores enorme, otras diminutas) | Cuadrícula pareja con la misma imagen recortada y el mismo margen |
| Dos secciones casi iguales (Sectores y Aplicaciones) con imágenes de 40 px de ancho | Una sola sección con 6 tarjetas 4:5 que giran |
| Marcas pegadas al hero | Franja propia con título, antesala del cierre |
| Fichas con tres bloques apilados (selector, precio, datos, tabla, fotos) | Jerarquía de 9 pasos + 3 pestañas; precio y botones protagonistas |
| Foto principal de ficha = foto de stock | Siempre el producto oficial recortado; la foto de ambiente solo en «Dónde se usa» |
| Fotos de oficina y de gente posando (asesora con diadema, recepción, mensajero en una puerta) | Fotos industriales que coinciden con el texto, o capturas reales de la tienda |
| Franjas laterales de color en avisos, categorías activas y cifras (detector de Impeccable) | Contorno suave, estado activo con contador naranja, línea corta sobre la cifra |
| Elevación de tarjetas de 4 px con sombra grande al pasar el mouse | 3 px, sombra corta, 220 ms |
| Mega-menú con categorías que se juntaban en una línea | Lista vertical, una por renglón |
| Panel de acceso con foto de stock | Tarjeta sobria de 460 px con tres beneficios en una línea |


## 6. Rediseño 4 (ajustes detallados sobre `fc75d4c`): qué aportó cada skill

**Cuáles se usaron de verdad y cuáles no.** Se cargaron y se aplicaron `design-taste-frontend`, `emil-design-eng` y `anti-ui-slop`. **No se ejecutaron como pasadas separadas** `impeccable` (critique, layout, typeset, bolder, quieter, polish), `frontend-design` ni `ui-ux-pro-max`: el encargo trajo la composición, las proporciones y la tipografía ya detalladas por página, y la crítica y el pulido se hicieron mirando cada página en el navegador a 375 y 1440 px y corrigiendo. Las reglas de `design-taste-frontend` que contradicen la marca (prohibir Inter y el morado) se descartaron, como en la ronda anterior. Superdesign sigue sin existir en este entorno.

| Página | Qué cambió | Qué aportó cada skill |
|---|---|---|
| Sistema (`ie.css`) | Tokens con roles, tres fondos de sección, bloque extendido, pedestal, tarjetas de canal | **design-taste:** el cierre del Inicio dejó de ser un bloque oscuro centrado y pasó a composición asimétrica 46/54; evitar tres tarjetas iguales en fila (las de canal tienen pesos distintos). **emil:** curva única `cubic-bezier(.22,1,.36,1)`, solo `transform` y `opacity`, `scale(.98)` al presionar, hover solo con puntero fino, `prefers-reduced-motion` con fundido de 200 ms |
| Encabezado | Logo recortado (64/52/44 px), barra que baja de 84 a 68 px, botón «Cotizar» | **emil:** el cambio de altura dura 240 ms con `ease-out` y no se anima con teclado |
| Inicio | Hero sin círculo (brillo, arco, sombra, píldora), Asesoría y Entrega con bloque extendido, sectores 4 + 3, marcas sin franja, cierre claro | **design-taste:** hero asimétrico con el degradado solo del lado del texto. **emil:** la entrada del producto es de 600 ms y el arco se redibuja al cambiar de diapositiva; el flip de Sectores se conserva |
| Catálogo | Banner «vitrina» con cinco productos en escalera | **design-taste:** profundidad en tres alturas en vez de productos flotando al azar. **emil:** parallax máximo de 8 px por capa solo con mouse; entrada escalonada de 80 ms |
| Ficha | Panel con pedestal, tarjeta sticky, pestañas con indicador, ficha técnica a ancho completo | **design-taste:** estados de carga, vacío y error se conservan; etiquetas arriba del campo. **emil:** el indicador de pestañas se desliza con `transform` en 240 ms |
| Cómo comprar, Contacto, Acceso | Heros divididos, canales reutilizados, formulario sobre `bg-lila-arc`, franja violeta | **design-taste:** rompe la simetría (foto con bloque a un lado, texto al otro). **anti-ui-slop:** nada de texto de relleno ni cifras inventadas; el horario no aparece hasta que la empresa lo confirme |
| Nosotros | Página nueva: «25» en contorno, galería horizontal fijada, hitos | **design-taste:** única de todo el sitio. **emil:** el relleno del «25» dura 1,2 s una sola vez; la galería horizontal se desactiva en celular. **anti-ui-slop:** las fotos que faltan muestran «Próximamente» y no una foto de stock |
| `/cotizar` | Formulario libre de 2 pasos con adjunto | **design-taste:** validación en línea bajo cada campo, etiqueta arriba, estados de envío y error. **emil:** el formulario se despliega en 300 ms; la validación no anima |

**Pasada final (anti-ui-slop):** se revisó cada pantalla renderizada en 375 y 1440 px, sin errores de consola, sin imágenes rotas, sin desbordes horizontales y sin fondos blancos lisos. Se corrigió lo observado: arco de la ficha cortado (`non-scaling-stroke` rompe `stroke-dasharray`), tarjeta del mapa pegada al borde, texto blanco de WhatsApp que salía lila, indicador de pestañas sin medir y fotos de «Dónde se usa» que no cargaban dentro de un panel oculto.

---

# Ajustes finales (sobre `cb89924`)

Cada página recibió un **recurso firma** propio y una **entrada propia**, y todas las secciones claras llevan «fondos con vida». Aquí queda, por página, qué encontró el `critique` de impeccable, qué referencia externa inspiró la solución y qué skill aportó cada decisión.

## Cómo se trabajó

- **Skills cargadas y ejecutadas** (no solo leídas): `design-taste-frontend` (dirección: varianza 8, movimiento 6, densidad 4; nada de tres columnas iguales ni de héroes centrados), `impeccable` (críticas, ver abajo), `frontend-design` + `ui-ux-pro-max` (implementación y chequeos de contraste, objetivos táctiles de 44 px, hoja inferior, formularios), `emil-design-eng` (curvas, duraciones, origen del popover, `scale(.97)` al presionar, `prefers-reduced-motion`) y `anti-ui-slop` (última pasada).
- **impeccable, `critique`**: Evaluación A (revisión de diseño, en un subagente aislado, leyendo código) y Evaluación B (`impeccable detect` sobre los JSX cambiados: **0 hallazgos**). Nota de método: la A se hizo sobre el código, sin overlay de navegador. Puntuación heurística global de la A: **29/40** (7 y 10 `n/a`, superficie «Persuade»). Pasadas aplicadas: `layout` (ritmo y estructura por página), `typeset` (nombres de producto en minúscula normal; titulares con pesos mezclados), `bolder` (hero con video, circuito, tarjeta de visita), `quieter` (se quitaron barra de progreso al 100 %, panel violeta de acceso, doble titular, seis salidas secundarias) y `polish` (hover, foco, estados y `reduced-motion` de lo nuevo).
- **Investigación externa** (solo ideas, nada copiado): se pudo leer `se.com/co/es` (tarjetas con imagen y título como zona clicable, categorías con foto cuadrada, accesos directos a WhatsApp y a contacto por región) y el listado de `awwwards.com/websites/industrial` (scroll con GSAP, secciones a pantalla completa, paletas industriales). `weg.net` responde 403 y `siemens.com` solo dio la estructura general (hero con pregunta, tarjetas con etiqueta de tipo y elevación sutil); `new.abb.com` agotó el tiempo; `htmlrev.com` es un directorio de plantillas sin ideas útiles; la búsqueda en Godly no devolvió resultados. Las ideas de «línea que se dibuja con el scroll» vienen de las referencias de dibujo SVG por `stroke-dashoffset` que listan los buscadores (Runway, Codrops).

## Prohibiciones que se cumplen

| Regla | Cómo |
|---|---|
| Sin fondos blancos o lavanda lisos | `Bgfx` en cada sección clara (ver tabla de combinaciones). La verificación recorre las secciones y no encuentra ninguna sin capa de fondo |
| Misma estructura en dos páginas | Inicio: bloque extendido violeta; Cómo comprar: circuito; Nosotros: capa violeta + marcos; Contacto: tarjeta de visita; Cuenta: tarjeta única |
| Misma animación de entrada en dos páginas | Inicio: desliza de lado · Cómo comprar: enciende la línea (scroll) · Nosotros: máscara de abajo hacia arriba 700 ms · Contacto: fundido con escala 0,98 → 1 · Cuenta: sube 16 px con fundido |

## Fondos con vida (`Bgfx`, `bgfx.css`)

`a` dibujo técnico (motor, engranaje, contactor, plano; trazo de 1 px, violeta-500 al 7 %, 60 % del ancho, cortado por el borde) · `b` malla de color (lila-200 + naranja al 10 %, esquinas opuestas) · `c` arco naranja de 1,5 px que se dibuja · `d` cuadrícula de plano de 32 px con máscara radial · `e` banda diagonal lila-100 a 6° · `f` esquineros en L de 24 px (clase `.tc`). Nunca más de dos por sección, salvo el panel de acceso, donde el encargo pide tres (malla + motor al 6 % + arco).

| Sección | Combinación | Sección | Combinación |
|---|---|---|---|
| Inicio · Portafolio | a (engranaje) + b | Cómo comprar · Hero | a (contactor) + e |
| Inicio · Ofertas | d + c | Cómo comprar · Circuito | d + b (+ f en las tarjetas) |
| Inicio · Asesoría y entrega | e + b | Cómo comprar · Entregas | e + c |
| Inicio · Sectores | a (contactor) + d | Cómo comprar · Preguntas | a (motor) + d |
| Inicio · Marcas | b + c | Nosotros · Hero | a (engranaje) + c |
| Inicio · Cierre | a (plano) + c del propio fondo | Nosotros · Equipo y almacén | b + e |
| Catálogo · Banner | a (plano) + b | Nosotros · Marcas | d + c |
| Catálogo · Listado | d + c | Contacto · Hero | b + a (motor) |
| Ficha | b + e | Contacto · Formulario | d + c |
| Carrito | d + e | Visítanos (Nosotros / Contacto) | a (plano) + b / e + a (engranaje) |
| Mi cuenta | b + a (engranaje) | Cotizar | a (contactor) + c del propio fondo |
| Política de datos | a (plano) + d | Ingresar / Crear cuenta | b + a (motor, 6 %) + c |

## Por página

### Inicio
- **critique (impeccable):** el `h1` del hero cambiaba cada 9 s con `aria-live` en el título, el hero acumulaba más de nueve controles, el producto no aparecía en celular y la primera diapositiva dejaba la cara del trabajador detrás del producto. Las tarjetas mostraban un solo botón y nombres en mayúsculas condensadas.
- **Referencia:** `se.com/co/es` (tarjeta entera clicable con imagen y título; acceso directo a WhatsApp), `awwwards.com/websites/industrial` (video a pantalla completa con velo de color).
- **Skill:** `design-taste-frontend` (hero asimétrico, texto a la izquierda y activo a la derecha), `impeccable layout/bolder` (secuencia video → imagen → video → imagen; velo violeta + tinte `multiply` al 18 %), `emil-design-eng` (entrada lateral de 56 px, 700 ms, solo `transform`/`opacity`).
- **Decisiones:** el texto del hero entra deslizándose de lado (entrada firma del Inicio). Las tarjetas (catálogo y Inicio) tienen **dos acciones**: «Ver ficha» en contorno y el botón circular naranja de 44 px con carrito y «+». Compra rápida: una referencia → directo; varias → popover en escritorio (origen en el botón, 160 ms, `ease-out`) u hoja inferior en celular (280 ms, curva de cajón); toast abajo a la derecha, pulso del contador (1 → 1,15 → 1 en 300 ms) y check 1,2 s. Las líneas solo de cotización muestran un documento y abren la cotización con la línea precargada. Sin números de inventario: si no alcanza, «Cotizar esta cantidad».

### Cómo comprar — recurso «circuito»
- **critique:** la línea de tiempo con foto y texto alternados era la receta de plantilla; el bloque `prefers-reduced-motion` apuntaba a clases que ya no existían; los números 01/02/03 repetían la receta de `SectionLabel`; `.phone` estaba definido dos veces (en `como-comprar.css` y en `contacto.css`).
- **Referencia:** técnicas de dibujo SVG con `stroke-dashoffset` ligado al scroll (Codrops, Runway) y la idea de «recorrido» de los sitios de ingeniería eléctrica de la lista.
- **Skill:** `design-taste-frontend` («Scroll Progress Path»), `impeccable layout` (bornes con tarjetas de radio 24 px, esquineros en L), `ui-ux-pro-max` (mockups pequeños, no a media pantalla), `emil-design-eng` (los bornes se encienden con `transition` de 300 ms, no con keyframes).
- **Decisiones:** una línea naranja de 3 px serpentea (curvas alternas de 46 px en escritorio, vertical a la izquierda en celular), se mide sobre el DOM real y se «enciende» con el scroll con un punto luminoso en la punta; cada borne (círculo violeta-950 de 64 px, aro naranja, número en Barlow, dos lengüetas) y su tarjeta se activan cuando la corriente llega. Imágenes: portátil con el buscador (captura real nueva, sin la foto de cableado automotriz), ficha en celular, asesor por WhatsApp (conversación) y despacho de cajas. El escenario de cada mockup lleva cuadrícula de plano y una mancha naranja. Hero dividido, sin cuadro difuminado; un cable naranja abraza la foto y se enciende al cargar. Se quitaron los números de sección y se eliminó el CSS duplicado de `.phone`.

### Nosotros — recurso «galería editorial»
- **critique:** 3 de 5 diapositivas eran «Próximamente» dentro de una galería fijada de 100vh; las etiquetas empezaban en «03»; el `h1` era solo un número decorativo; el marco en arco cortaba el letrero; `nosotros.css` estaba casi todo muerto.
- **Referencia:** maquetas editoriales de capas (foto con un bloque de color desplazado) de los listados de plantillas; `awwwards.com/websites/industrial` (cifras grandes y pocas).
- **Skill:** `impeccable typeset` (cifras en Barlow 800 con el «+» en naranja), `frontend-design` (una sola cosa memorable: la fachada con su capa), `emil-design-eng` (revelado con `clip-path` de abajo hacia arriba, 700 ms).
- **Decisiones:** solo dos cifras, «+25 años en el sector» y «+1.000 productos» (sin fundación ni fechas). Fachada en marco 16:10, radio 28 px, 56 % del ancho, con el letrero completo; detrás, un rectángulo violeta-700 desplazado 24 px abajo a la derecha; tarjeta flotante «Bucaramanga, Santander». Dos espacios reservados 4:3 («Nuestro equipo» y «Nuestro almacén por dentro»): panel lila-200 con cuadrícula, logo al 8 %, título y «Foto próximamente»; al pasar `photo` y `alt` a `PhotoSlot` la foto reemplaza el panel sin tocar el diseño. Ninguna foto repetida. Marcas con logos, sin tocar. Visítanos: mapa en contenedor de 28 px, tarjeta flotante y 120 px de margen inferior.

### Contacto — recurso «tarjeta de visita»
- **critique:** doble encabezado («Escríbenos» dos veces) con `id="h-ayuda"` duplicado; texto blanco sobre naranja (≈3:1) en el canal de WhatsApp; el botón decía «Enviar mensaje» pero solo prepara el mensaje.
- **Referencia:** `se.com/co/es` (canales directos a WhatsApp y a contacto en una sola zona) y las tarjetas de presentación de los listados de Godly/Awwwards (franja de color, logo y datos en columnas).
- **Skill:** `impeccable layout/clarify`, `ui-ux-pro-max` (texto `ink` sobre naranja, objetivos de 44 px), `emil-design-eng` (fundido con escala 0,98 → 1, 600 ms).
- **Decisiones:** los tres canales forman una gran tarjeta blanca con franja naranja de 8 px, logo y columnas; el botón de WhatsApp lleva texto `ink`. Formulario y mapa debajo, con el mapa redondeado y 120 px de margen inferior. «Preparar mensaje» en lugar de «Enviar mensaje».

### Ingresar y Crear cuenta — recurso «panel limpio»
- **critique:** la parte más intercambiable (tarjeta centrada); la franja violeta era `aria-hidden` y solo aparecía desde 1024 px; «Ingresar» aparecía cuatro veces; barra de progreso al 100 % en un formulario de un paso; seis salidas secundarias; props muertas en `AuthLayout`.
- **Referencia:** formularios sobrios de `se.com` (una acción principal, el resto en texto pequeño).
- **Skill:** `impeccable quieter` (se quitaron el panel violeta, los beneficios y la barra), `design-taste-frontend` (varianza baja aquí a propósito), `emil-design-eng` (la tarjeta sube 16 px con fundido).
- **Decisiones:** tarjeta de 460 px; fondo con malla de color, silueta de un motor en línea al 6 % y arco naranja fino; «No necesitas cuenta» sube debajo del título; bajo la tarjeta, cuatro logos en gris con «Distribuidores de WEG, ABB, Siemens y Fluke».

### Disponibilidad y bodega
- **critique:** el dato de inventario era la decisión más repetida y la menos útil en B2B; ocupaba 5 lugares (ficha técnica, tabla de referencias, bajo el precio, chips, textos).
- **Decisión:** se quitó de toda la vista. La validación de stock sigue en el backend (`InventoryService`); si la cantidad no alcanza, la tienda ofrece «Cotizar esta cantidad» sin mostrar números. Tabla de referencias: Referencia · SKU · Precio · Agregar/Cotizar. Las fotos `bodega-*` pasaron a `almacen-*`.

---

# Ajustes de detalle (sobre `4446cbb`)

Misma forma de trabajo: `impeccable critique` (Evaluación A en un subagente aislado, leyendo código; Evaluación B con `impeccable detect`, **0 hallazgos** sobre los JSX cambiados), `design-taste-frontend` y `emil-design-eng` al implementar y `anti-ui-slop` como última pasada. Nota de método: la Evaluación A de esta ronda se hizo con las páginas ya reconstruidas (no antes de tocarlas); las correcciones que pidió se aplicaron antes del commit y las que no se aplicaron están al final.

## Qué encontró el critique y qué se hizo

| Página | Hallazgo (impeccable) | Referencia | Skill | Decisión |
|---|---|---|---|---|
| Inicio | Velo solo horizontal: en celular el texto ocupa todo el ancho y el lado derecho queda casi sin velo; el botón Pausa no pausaba el video; `aria-live` en el título anunciaba cada diapositiva | `se.com/co` (tarjeta entera clicable) y la hoja que sube sobre el hero (estilo WEG, citado en el encargo) | design-taste (hero asimétrico, sin tinte de color), emil (hoja con sombra hacia arriba; flecha 45° + 4 px, imagen −6 px en 240 ms), impeccable `polish` | Velo del encargo (`rgba(20,16,36,.9) → transparent`), sin multiply ni `hue-rotate`; en celular también desde abajo; Pausa detiene el video; sin `aria-live`; línea naranja de 2 × 120 px; la hoja de familias (−56 px, radio 40 px) y tarjetas con enlace `::after` que cubre toda la tarjeta |
| Catálogo | La flecha «no llevaba a ningún lado» porque el `::before` del enlace quedaba dentro del cuerpo de la tarjeta; cabeceras de familia ausentes; dos `aria-current` a la vez | Cabeceras apiladas tipo agenda de contactos (iOS) y la franja de familia de `se.com` | design-taste (densidad media, sin cajas en cajas), emil (`transform`/`opacity`, `scale(.97)`), ui-ux-pro-max (44 px, hoja inferior) | Agrupado por familia con cabeceras `position: sticky` (la nueva empuja a la anterior), scrollspy en la columna (barra naranja de 3 px + abre sola sus categorías) y en los chips del celular; tarjetas de alto uniforme (imagen ≤ 240 px, nombre a 2 líneas, chips en una línea con «+N»); franja editorial de 220 px, una por familia |
| Cuenta | Tarjeta blanca sobre fondo casi blanco (intercambiable) | Formularios sobrios de `se.com`; riel vertical de producto (marquee) de los listados de Awwwards | impeccable `bolder` sobre el fondo, `quieter` sobre el contenido | Dos zonas (55 / 45): tarjeta sobre cuadrícula y «riel de producto» con dos carriles de 40 s en sentidos opuestos (pausa con el mouse); errores dentro de la tarjeta con borde naranja de 3 px (lo pidió el encargo) e icono; enlace de WhatsApp en una sola línea |
| Recuperar contraseña | No existía | Flujo estándar de Laravel (Password Broker) | laravel-specialist: Controller → FormRequest → Service → Repository, límite de peticiones, pruebas | `/recuperar` y `/restablecer`; respuesta idéntica exista o no el correo; enlace de un solo uso que vence en 60 min; revoca los tokens de sesión; correo con la marca; 12 pruebas |
| Contacto | Mapa grande y formulario siempre visibles; mosaicos intercambiables | Tarjeta de presentación (franja de color + columnas) | impeccable `layout`, emil (altura animada 300 ms con `grid-template-rows`) | «Centro de atención»: hero oscuro, 4 mosaicos, formulario solo para «Estado de mi pedido» (sin sesión) y «Garantías y devoluciones», tarjeta de visita sobre banda diagonal, dirección con miniatura de mapa estática. El formulario cerrado queda `inert` |
| Nosotros | Cifras repetidas, tarjetas intercambiables, mapa a sangre | Muro de producto: mosaico asimétrico de los sitios industriales de Awwwards | design-taste (varianza 8: grilla asimétrica con baldosa 2 × 2), emil (revelado por máscara escalonado) | Titular nuevo, muro de 12 productos con baldosa central, tres pilares con foto (60 %), marcos reservados, marcas sin tocar y cierre con arco naranja grande y sin mapa |
| Carrito | `broken` quedaba en `true` para siempre: si la ruta guardada fallaba mientras cargaba el catálogo, no se reintentaba con la foto actual | — | impeccable `harden` | La miniatura sale de la línea actual del catálogo; la guardada es el último recurso; 88 px sobre pedestal lila-100 y radio de 16 px |

## Hallazgos del critique que NO se aplicaron (y por qué)

- **Eyebrows y numeración «01 — Portafolio»:** se quitaron de las páginas rehechas, pero siguen en Inicio y Catálogo porque son parte de la identidad que fijó la rúbrica (`DESIGN.md`).
- **Quitar «Foto próximamente» de Nosotros y las tres tarjetas iguales:** el encargo pide explícitamente los marcos reservados y los tres pilares.
- **Borde izquierdo de 3 px en las alertas y franja naranja de 8 px en la tarjeta de visita:** son especificaciones del encargo.
- **Apilado fijo del celular (≈ 248 px de cabeceras fijas):** el encargo pide chips fijos y cabeceras de 56 px; se redujo la altura de los chips (44 px) pero no se quitó nada.
- **Foco/trampa de foco de la hoja de filtros y del selector de compra rápida:** pendiente (accesibilidad); la compra rápida cierra con Escape y devuelve el foco al botón.
