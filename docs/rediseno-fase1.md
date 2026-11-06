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
| Contacto, Nosotros, Cómo comprar | Ya rediseñadas en el commit anterior | Solo contactos definitivos con nombre; sin cambios de composición |

Superdesign: no hay acceso a Superdesign en esta sesión, así que no se exploraron variantes con esa herramienta. Las decisiones se tomaron sobre capturas reales.
