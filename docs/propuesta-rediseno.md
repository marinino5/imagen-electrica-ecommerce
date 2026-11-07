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
