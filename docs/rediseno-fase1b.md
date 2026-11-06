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

## Cómo comprar
- Las guías de checkout y de B2B piden pocos pasos (cuatro) y que cada uno se vea. Idea tomada: recorrido de 4 pasos con una línea naranja que se llena con el scroll (la única animación de scroll de la página) y, en cada paso, una captura real de la tienda en un marco de navegador en bisel, no un dibujo.
- El interruptor Compra en línea / Cotización vive en la portada y cambia el recorrido, la lista de requisitos y las preguntas. En cotización los pasos "El asesor responde" y "Aprueba y paga" no tienen pantalla (pasan fuera de la tienda), así que llevan un icono en lugar de una captura inventada.
- Entregas: tres tarjetas y el mapa de puntos de Colombia con Bucaramanga marcada. El costo de domicilio y envío nacional no se inventa: lo confirma el asesor.
- Preguntas frecuentes en dos columnas, reescritas para el nuevo flujo (cotizar no necesita cuenta; comprar en línea sí).

## Contacto
- Las guías de páginas de contacto B2B piden varios canales juntos, a la vista y sin obligar a llenar un formulario primero. Idea tomada: tres tarjetas de acción justo debajo de la portada (WhatsApp, llamar, correo), el formulario inteligente después y el mapa al final.
- Portada sobre lavanda con un aro naranja grande y el teléfono en bisel con la conversación de ejemplo animada (burbujas una por una, marcada "Ejemplo"): enseña qué va a pasar al escribir.
- Sin nombres propios: la línea principal va primero y las otras dos solo con su número.

## Crear cuenta e Ingresar
- Los flujos de registro B2B funcionan mejor cuando muestran qué se gana y dejan una salida para quien no quiere registrarse. Idea tomada: a la izquierda título grande, el ejemplo animado de "Así verás tus pedidos" (línea de estados) y tres beneficios en tarjetas de bisel; a la derecha el formulario con pasos y un aviso visible: "¿Solo quieres cotizar? No necesitas cuenta".
- Se quita la foto del pasillo de tableros. En celular el formulario va primero.

## Mi cuenta
- Portada con saludo y un bento de tarjetas (pedidos, cotizaciones y una tarjeta ink con lo que está en curso y su último estado), en lugar de una fila de tres cajas iguales.
- Cada pedido es una tarjeta de bisel con la línea de estados (la misma de Crear cuenta), "Volver a pedir" a la vista y el detalle plegable. "Volver a pedir" busca la referencia vigente por SKU para tomar precio, stock y línea actuales.
- Datos editables solo donde el backend lo permite (`PUT /me`); las direcciones guardadas siguen marcadas como no disponibles.

## Pie y encabezado
- Encabezado: isla flotante tipo píldora a 12 px del borde (ink al 88 % con blur), carrito naranja con contador en círculo; en celular ocupa todo el ancho y debajo va la barra inferior. Menú móvil a pantalla completa con líneas, cuenta y contacto.
- Pie: la banda naranja de cotizar se integró como la primera fila de una sola pieza de doble bisel en ink (aro naranja decorativo, divisor fino) con el pie completo debajo: marca y redes, tienda, líneas (las familias reales de la API), empresa y contacto sin nombres, con la barra legal (política de datos, enlace a la SIC y razón social/NIT si existen). En celular los bloques son acordeones. En el Inicio la fila de cotizar no se repite porque la página ya cierra con su propia banda.
- Se retira el último encabezado de franja violeta (Política de datos pasa a la portada del sistema, igual que la 404).
