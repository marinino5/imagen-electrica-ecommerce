# Medios pendientes (fotos y videos de calidad que faltan)

Lo que la tienda necesita y todavía no tiene con la calidad suficiente. Mientras tanto hay un reemplazo (segunda columna).

| Dónde | Qué falta | Reemplazo actual | Cómo resolverlo |
|---|---|---|---|
| Hero del Inicio · diapositiva «Motorreductores» | Foto o video de motorreductores con buena luz y estabilidad (el video propio `propio-reductores` salía con saltos y baja calidad) | Producto oficial recortado (WEG Cestari) sobre fondo violeta de marca | Grabar con trípode o estabilizador, a 1080p o más, 10 s, luz pareja y sin letreros de precio; o una foto fija horizontal ≥ 1600 px de una nave con reductores. Guardar los originales en `cliente/media-fuente/propio/` y procesarlos con `cliente/scripts/procesar-propio.sh` |
| Hero del Inicio · diapositiva «Motores» | — (ya tiene una nave luminosa de stock, Pexels 34207364) | Foto libre (Pexels), ver `docs/licencias-imagenes.md` | Cambiar por una foto propia de la sala de motores cuando exista |
| Videos propios (Nosotros → «Nuestra tienda») | Versiones de mejor calidad | Los tres clips actuales (`propio-reductores`, `propio-control`, `propio-compresor`) siguen en Nosotros, no en el hero | Regrabar con estabilizador |
| Logos de marca | EBCHQ, Relpol, Match Drive, Cellux, Lexmana, Ilumax, VCP, Hanyoung, Ventum, Ferrara | Nombre de la marca en texto | Pedir el logo oficial a cada fabricante o proveedor |
| Catálogo | 131 referencias sin foto | Diseño de marca | `docs/inventario/fotos-para-tomar.md` |

## Capturas de «Cómo comprar»

Las pantallas del celular en `Cómo comprar` (`public/img/como-comprar/*.webp`, paso «Revisa tu carrito» y «Recibe tu confirmación») se tomaron antes del pago por pasos para invitados y muestran el carrito anterior. Regenerarlas con `cliente/scripts/capturar-mockups.mjs`, cambiando el envío simulado de `POST /orders` a `POST /checkout` y llenando los tres pasos del formulario.

## Imágenes ilustrativas (reemplazar por la foto real)

| Dónde | Qué es | Pendiente |
|---|---|---|
| Línea «Batería Magna 12 V 5 Ah» y portada de «Herramientas y Repuestos» | Batería de 12 V de stock (Pixabay 2870614): no es la Magna y su etiqueta dice otra marca | Fotografiar la Magna 12 V 5 Ah |
| Portada de «Compresores» | Foto del distribuidor (Homecenter Colombia) del Ferrara FR2-22300L, de 270 L: es de la misma familia que el de la tienda (100 L) pero no el mismo modelo | Foto oficial del fabricante del modelo exacto que se vende |
