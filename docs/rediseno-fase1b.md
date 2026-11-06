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

## Carrito y cotización
- Guías de carrito y checkout coinciden en tres cosas: resumen visible todo el tiempo, estimar el envío en el carrito y no pasar de unos pocos pasos. Idea tomada: pasos Revisa, Entrega y Confirma en la portada, resumen sticky a la derecha (fijo abajo en celular) y entrega con tarjetas seleccionables. El costo del envío local y nacional no se inventa: dice "lo confirma tu asesor".
- En B2B es común un "Pedir cotización" dentro del carrito que ya lleva los productos, para no escribirlos otra vez. Idea tomada: bloque propio de cotización sin cuenta, con los renglones del carrito y un formulario corto (nombre, WhatsApp, correo, ciudad; empresa y NIT opcionales).
- Sugerencias de la misma familia que los productos del carrito, con las mismas tarjetas del catálogo.
- Confianza: envíos, asesoría y "pago seguro: próximamente" (no se afirma lo que todavía no existe).

## Inicio
- Ideas tomadas de las guías de sitios industriales B2B: abrir con una propuesta clara y una acción principal (buscar una referencia o cotizar), mostrar prueba social/cifras justo debajo y llevar a las líneas con tarjetas grandes. Hero con 3 diapositivas, buscador dentro de la portada y una tarjeta de cifras que se monta sobre el borde.
- Líneas en bento asimétrico (2 grandes y 4 pequeñas) con la imagen sobre fondo radial y `mix-blend-mode: multiply`; destacados en carril con las mismas tarjetas del catálogo; aplicaciones como mosaico de fotos en bisel (iluminación sale del mosaico hasta tener foto real).
- Se quita "Así de fácil" del Inicio: vive en Cómo comprar. La banda final ink de cotización sin cuenta reemplaza a la del pie en esta página para no repetirla.

## Nosotros
- Las líneas de tiempo de empresas con trayectoria funcionan por épocas: nodos que se eligen y un panel que cambia (Ceros, Framer). Idea tomada: nodos navegables con teclado (flechas) y un panel de bisel; la línea naranja avanza hasta el hito activo.
- La empresa aún no entregó hitos, así que solo hay datos confirmados al inicio ("más de 25 años en Bucaramanga") y al final (la tienda en línea). Los tres hitos del medio son espacios marcados "Por confirmar": no se inventan fechas.
- Fachada real en bisel con dos insignias flotantes; espacios reservados (con borde punteado) para la foto del equipo y del interior, sin representar nada falso.
- Razones en cascada en Z (rotaciones leves en escritorio, apiladas en celular) y marcas en bento de chips en bisel con WEG destacada.
