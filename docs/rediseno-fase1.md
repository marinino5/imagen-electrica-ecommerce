# Rediseño Fase 1: notas por página

Revisión visual a 375 y 1440 px (capturas en `docs/capturas/fase1/`). Qué se veía genérico y qué se hizo.

| Página | Qué se veía genérico | Cambio |
|---|---|---|
| Inicio / hero | Dos videos de IA, mensaje general ("soluciones eléctricas") y controles pequeños | 3 diapositivas con mensaje propio (motores y motorreductores, automatización, extractores e iluminación), tag, CTA por línea, controles de 44 px. Los videos están pendientes de material real con licencia (ver `recursos.md`) |
| Catálogo | Seis filtros apilados en celular; una tarjeta por fila | Búsqueda visible, "Filtrar (n)" en panel inferior táctil, orden aparte, 2 columnas, tarjetas con jerarquía y enlace a la ficha |
| Ficha de producto | No existía: solo vista rápida | Ruta `/productos/:slug` con galería, marca, SKU, disponibilidad, precio con IVA o cotización, cantidad, especificaciones, garantía, relacionados y barra fija en celular |
| Carrito | Resumen al final de una columna larga | Resumen fijo abajo en celular, cantidades de 44 px, `inputmode` y `autocomplete` |
| Crear cuenta / Ingresar | Sin ayuda directa | Botón de WhatsApp de María Ruth bajo el formulario (formulario ya va primero en celular). Foto industrial real pendiente |
| Mi cuenta | Lista de pedidos sin contexto | Tarjetas de resumen, línea de estados, edición de datos (los que acepta `PUT /me`), "Volver a pedir", direcciones guardadas marcadas como no disponibles |
| Global móvil | Menú con 5 enlaces sin categorías | Encabezado compacto (logo, búsqueda, carrito), menú a pantalla completa con categorías y contacto, barra inferior (Inicio, Productos, Buscar, Carrito, Cuenta), WhatsApp sobre la barra |
| Contacto | Conversación de ejemplo inventada junto al título; sin foto | Portada violeta con tres botones grandes (WhatsApp, llamar, correo), línea de atención de María Ruth y foto real con arco naranja. El mapa busca "Bucaramanga, Santander, Colombia" |
| Nosotros | Tarjetas de "razones" de 300 px apiladas | Tarjetas más bajas en celular. La fachada real y los espacios de foto pendientes (equipo e interior) no se tocaron |
| Cómo comprar | 10 700 px de alto en celular por las mini pantallas de cada paso | En celular los pasos quedan en lista vertical (número, título y texto) y las entregas en tarjetas horizontales: 7 250 px |
| Inicio: aplicaciones | Íconos sin foto; 5 tarjetas de 220 px en una columna | 6 aplicaciones con foto real (iluminación sin foto), 2 columnas en celular |

Superdesign: no hay acceso a Superdesign en esta sesión, así que no se exploraron variantes con esa herramienta. Las decisiones se tomaron sobre capturas reales.
