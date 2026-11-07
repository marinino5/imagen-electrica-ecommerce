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
