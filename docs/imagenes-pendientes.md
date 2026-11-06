# Imágenes y videos pendientes

Lo que **no** se pudo conseguir con licencia y calidad suficientes. En su lugar la tienda muestra un diseño gráfico de marca (arcos, icono grande o el dato clave de la ficha); nunca texto de relleno ni material genérico.

## Videos y fotos de ambiente

| Pieza | Dónde iría | Qué se buscó | Resultado |
|---|---|---|---|
| Video de motor industrial | Hero, diapositiva Motores | Coverr: `electric motor`, `motor`, `rotating machinery`, `turbine`. Pixabay: `electric motor`, `industrial motor` | Nada adecuado (renders de pistones, tractor, motos). Ver `docs/opciones-video/` |
| Video de extractores industriales | Hero, diapositiva Ventilación | Coverr: `exhaust fan`, `industrial fan`, `extractor`, `ventilator`, `air vent`. Pixabay: `industrial exhaust fan`, `ventilation fan`, `roof ventilator` | Solo ventiladores domésticos o de cubierta lejana |
| Foto del asesor atendiendo | Inicio (Por qué elegirnos) y Cómo comprar (paso 3 de la cotización) | Coverr: `customer service`, `call center`, `phone call`, `headset`, `cashier` | Solo personas al teléfono en oficinas y cajeros de ropa; ninguno en un entorno de material eléctrico. En su lugar: diseño de marca y una ilustración de la conversación |
| Foto de entrega de pedido | Cómo comprar (paso 4 de la cotización) | Coverr: `delivery`, `courier`, `package box`, `warehouse` | Un reparto de comida en bicicleta, una caja con marca de Amazon (no se puede usar: lleva marca) y almacenes de ropa. En su lugar: ilustración del seguimiento |
| Foto de mostrador de material eléctrico | Nosotros | Coverr: `shop counter`, `hardware store`, `store owner`, `small business` | 0 resultados de ferretería; solo cafeterías y tiendas de ropa. En su lugar: tarjeta «Atención en tienda» con la dirección. La fachada real (`img/fachada.webp`) queda en la portada |

Coverr solo ofrece **videos** (el endpoint de fotos responde 404). La clave está en `cliente/.env` bajo el nombre `COVERR_API_BASE_URL`, aunque es una clave: renómbrala a `COVERR_API_KEY`.

## Imágenes de producto por línea

Meta: una imagen por línea desde el sitio oficial de la marca, fondo blanco, cuadrada, WebP de máximo 1200 px, en `cliente/public/img/productos/<familia>/<marca>-<linea>.webp` y asignada con el manifiesto `backend/database/data/line-images.json` (se corre con `php artisan db:seed --class=LineImageSeeder`, que solo llena imágenes vacías).

**Ninguna se pudo descargar.** Qué pasó:

| Marca | Líneas | Motivo |
|---|---|---|
| WEG | 27 de las 34 fichas: contactores, relés térmicos, relés de control, guardamotores, arrancadores directos, minibreakers, totalizadores, pulsadores, borneras, accesorios, CFW100/300/500/900, SSW05/SSW07, W01, W12, W21, W22 (monofásico, IEC, NEMA y a prueba de explosión), PLC200/500 y motores de otras marcas (comparte ficha con WEG) | `weg.net` responde 403 a cualquier descarga automática (también la herramienta de lectura web). No se esquivó el bloqueo. Las únicas imágenes accesibles están dentro de folletos PDF en `static.weg.net` o en distribuidores, y el encargo pide fuentes oficiales con imagen de cada línea |
| WEG Cestari | WCG01, WCG20 | `wegcestari.com.br` responde, pero sin URLs de imagen por línea en el HTML inicial; falta navegar el catálogo a mano |
| Siemens | Contactores (comparte ficha con WEG), accesorios | La página pública solo ofrece fotos de tarjetas de campaña recortadas con rostros, no el producto aislado |
| Fluke | Instrumentos de medición Fluke | `media.fluke.com` entrega las imágenes de categoría en 150 × 100 px; a 1200 px se ven borrosas |
| ABB | Motores de otras marcas (un motor ABB) | `new.abb.com` no respondió a la descarga |
| AIRAL / EMI | AirMax GL y TTT500 | La portada de `airal.com.co` no incluye imágenes de producto; están dentro de cada página de producto |
| Centelsa by Nexans | THHN / THWN-2 | `nexans.com` no publica el THHN; Centelsa tiene su propio sitio, que no respondió |
| Sylvania | START Highbay LED | `sylvania-lighting.com` responde 403 |
| York | Compresor vertical (ficha sin marca asignada) | No se identificó el producto exacto para pedir su imagen |

Mientras tanto, cada ficha sin foto muestra el diseño de marca con su dato clave (por ejemplo «9–95 A»), y no la foto de otra línea.

**Cómo desbloquearlo:** descargar a mano las imágenes desde los sitios oficiales (o pedirlas a los distribuidores de cada marca), procesarlas a WebP cuadrado sobre blanco, copiarlas a `cliente/public/img/productos/<familia>/`, agregar su URL de origen a `docs/creditos-media.md` y escribir la entrada `"slug-de-la-linea": "/img/productos/..."` en el manifiesto.
