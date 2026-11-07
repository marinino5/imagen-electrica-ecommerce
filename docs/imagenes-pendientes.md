# Imágenes y videos pendientes

Lo que **no** se pudo conseguir con licencia y calidad suficientes. La tienda lo resuelve con el diseño de marca de la línea (arcos y su dato clave), nunca con un renglón vacío ni con la foto de otra marca.

## Líneas sin foto oficial (4 de 34)

| Línea | Qué se intentó | Resultado |
|---|---|---|
| Extractores AirMax GL y TTT500 (EMI · AIRAL) | https://www.airal.com.co, https://www.airal.com.co/productos-avicola, búsqueda de fichas y catálogos | El sitio de AIRAL no publica imágenes de producto por referencia; no hay catálogo PDF oficial localizado |
| THHN / THWN-2 (Centelsa by Nexans) | https://www.centelsa.com, https://www.centelsa.com.co, https://www.centelsa.com/es (tiempo de espera agotado); nexans.com no publica THHN | Sin imagen oficial. Solo hay catálogos de Centelsa alojados en distribuidores y tiendas, que no se usan |
| START Highbay LED (Sylvania) | https://www.sylvania-lighting.com y https://product.sylvania-group.com/... | Responden «Attention Required» de Cloudflare (403). No se esquivó |
| Compresor vertical | https://www.york.com.co (error SSL), https://jci.com (403) | No se identificó el producto exacto ni hay catálogo oficial de compresores de pistón |

La línea «Motores de otras marcas» usa una foto WEG de otro ángulo porque NORD publica sus imágenes de producto en 250 px con rótulos (https://media.nord.com/res/1210x726-WEBP/167081.webp y similares) y ABB no respondió (`new.abb.com`, error HTTP/2).

## Sitios que bloquearon la descarga automática

- `weg.net` (todas sus rutas) responde «Access Denied» incluso con Chrome real; Siemens responde «Human Verification». Se usaron los catálogos PDF oficiales de `static.weg.net`, que sí son públicos.
- Algunos enlaces de catálogos que aparecen en buscadores devuelven 404 (por ejemplo W01 rolled steel y el catálogo W22 europeo): se reemplazaron por catálogos vigentes de las mismas líneas.

## Logos sin archivo

Sylvania, York, EMI y Centelsa: no se encontró un archivo oficial. Se muestra el nombre de la marca.

## Videos y fotos de ambiente

| Pieza | Resultado |
|---|---|
| Video de motor industrial, motorreductor o banda, extractores en primer plano | No existen en Coverr ni en Pixabay (solo renders de pistones, motos, ventiladores domésticos). Las diapositivas de Motores, Motorreductores y Ventilación usan la foto oficial del producto |
| Foto de asesor en mostrador de material eléctrico, pedido en bodega | Coverr no tiene fotos (el endpoint responde 404). Se usan fotos de Pixabay de una asesora con diadema, una recepción, cajas empacadas y un mensajero |
| Ingeniero con casco recortado en PNG | No hay herramienta de recorte en este entorno. La foto del técnico se muestra en forma de arco con CSS |
| Foto real de compresores y de cables en uso | Sin foto de la familia Compresores: la tarjeta usa el diseño de marca |

## Cómo desbloquearlo

1. Pedir a cada distribuidor oficial la imagen de la línea (o descargarla a mano desde su sitio).
2. Procesarla a WebP cuadrado sobre blanco, copiarla a `cliente/public/img/productos/<familia>/`.
3. Agregarla a `backend/database/data/line-images.json` y correr `php artisan db:seed --class=LineImageSeeder` (solo llena vacíos).
4. Anotar la fuente en `docs/creditos-media.md`.
