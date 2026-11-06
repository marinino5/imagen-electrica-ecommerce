# Rediseño Fase 1B: página por página

Sistema visual: la maqueta aprobada `docs/maquetas/catalogo.html` (lavanda con grano, isla de navegación, doble bisel, botones píldora con icono en círculo, etiquetas con punto naranja, Barlow Condensed 800, curva `cubic-bezier(.32,.72,0,1)`). Cada página abre con una portada editorial dividida (texto a la izquierda, composición a la derecha) y no repite plantilla.

Skills de diseño consultadas: `frontend-design` (evitar plantillas: una sola pieza memorable por página, movimiento solo cuando responde a una acción o enseña un cambio, textos que dicen qué pasa). Las otras skills de diseño de la lista no aportaron reglas distintas a las de la maqueta, que manda cuando hay diferencia.

Las referencias de abajo vienen de búsquedas en internet; son guías de proveedores y agencias, no estudios formales, y se toman como ideas, no como autoridad.

## Catálogo
- Los filtros de catálogos técnicos (voltaje, potencia, corriente) crecen mucho: Baymard recomienda listas de filtros buscables y plegables. Idea tomada: buscador arriba, familias en acordeón que se contrae a la activa y chips por rangos de HP y de A que solo muestran lo que existe en la selección.
- Unilog y Folio3 coinciden en agrupar las variantes en una sola familia de producto y elegirlas con botones, no con una lista de SKU. Idea tomada: tarjeta por línea (no por SKU) con el resumen "desde $", rango de HP y número de referencias; las variantes se eligen en la ficha.
- Guías de carrito B2B: tener el resumen siempre a la vista evita perder lo agregado. Idea tomada: mini carrito fijo al final de la columna de filtros.
- Celular: hoja inferior con pestañas Líneas y Filtros y un botón que dice cuántas fichas verá la persona ("Ver 33 fichas").

## Ficha
- En catálogos técnicos la persona busca "la referencia exacta": Unilog y Folio3 recomiendan variantes como botones dentro de una ficha, sin lista larga de SKU. Idea tomada: chips de potencia de menor a mayor, chips de velocidad que se apagan si la combinación no existe y, cuando hay gemelas, chips de referencia con lo que las distingue.
- La placa de datos (bloque ink que imita la placa de un motor) resume la variante elegida y la tabla completa sirve de selector: tocar una fila cambia la variante. La referencia elegida vive en la URL (`?ref=SKU`) para compartirla.
- Precio, stock y SKU se actualizan al instante; "Cotizar volumen" manda la referencia al bloque de cotización del carrito.
- Miniaturas: la API aún no tiene más de una imagen por línea, así que la galería muestra una sola imagen y no se inventan vistas. El bisel y el componente ya admiten varias.
- Celular: píldora ink fija con precio, variante y botón.
