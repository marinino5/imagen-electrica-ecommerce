# Imágenes y fotos pendientes

Lo que **no** se pudo conseguir con licencia y calidad suficientes. La tienda nunca deja un renglón vacío ni usa la foto de otra marca como si fuera la del producto.

## Resumen (nivel 2)

- **351 de 351 referencias tienen imagen** (todas comparten la foto oficial recortada de su línea; el fabricante usa la misma foto para todas las variantes). Detalle por SKU en `docs/imagenes-referencias.csv`.
- **33 de 34 líneas con imagen oficial**; el CSV también lista las 8 líneas sin referencias (se cotizan).
- ~~Falta 1 foto oficial: Compresor vertical~~ **Resuelto en el rediseño 4**: foto oficial ABAC (`abac-compresor-vertical.webp`). Antes la tarjeta usaba una ilustración de marca en el mismo formato (`cliente/public/img/catalogo/compresor-ilustracion.svg`).

## Pendientes de producto

| Línea | Qué falta | Qué se intentó | Qué necesito |
|---|---|---|---|
| Compresor vertical | Foto oficial del fabricante | No se conoce la marca del equipo (`brand_label` vacío en el inventario). Búsquedas de «compresor de pistón vertical 2 a 15 HP»: las fotos de stock traen otra marca visible (p. ej. Kittory) o no son verticales. `https://www.york.com.co` (error SSL) y `https://jci.com` (403) en la entrega anterior | **La marca (y el modelo)**: con eso se descarga del sitio oficial y se pasa por `normalizar-producto.py` |
| Fotos propias por referencia (SKU) | Una foto distinta por cada variante | `https://www.weg.net/catalog/weg/CO/es/search/?text=10046366`, `https://www.weg.net/catalog/weg/MX/es/search/?text=10046366`, `https://www.weg.net/catalog/weg/CO/es/c/GLOBAL_WDC_CO_AUT_STARTERS`: **403 «Access Denied»** también con Chrome real (probado hoy con pausas de 3 s). No se esquiva la protección. `static.weg.net/medias/downloadcenter/` responde 200 pero solo sirve los PDF de catálogo, de los que ya salieron las fotos de línea | Si WEG entrega las fotos por SKU (distribuidor oficial), se asignan con la misma migración |
| Motores de otras marcas | Foto de ABB / Nord / Nidec | La línea usa una foto WEG W22 de otro ángulo. Nord publica 250 px con rótulos (`https://media.nord.com/res/1210x726-WEBP/167081.webp`); `new.abb.com` no respondió | Fotos de catálogo de esas marcas |

**Resueltas en este nivel** (antes pendientes): *Extractores AirMax GL* (foto oficial de AIRAL: `https://www.airal.com.co/airmaxgl`), *THHN / THWN-2* (rollo oficial de Centelsa by Nexans: `https://www.nexans.co/.rest/eservice/dam/v1/image/259435`, de la ficha de Nexans Colombia) y *START Highbay LED* (Sylvania Colombia: `https://sylvania-colombia.com/media/c2xp4nhs/gc101-st-producto-05.jpg`; `sylvania-lighting.com` y `product.sylvania-group.com` siguen respondiendo 403). `https://www.centelsa.com` y `https://tienda.centelsa.com` no abren (tiempo de espera agotado).

## Pendientes de la empresa (Nosotros)

| Pieza | Estado |
|---|---|
| Foto interior del almacén | Pendiente: «Nuestro almacén por dentro» muestra un diseño gráfico de marca sin texto de relleno |
| Foto del equipo | Pendiente: «Nuestro equipo» muestra un diseño gráfico de marca sin texto de relleno |

Cuando lleguen: pasarlas por `cliente/scripts/gradar-foto.py <origen> cliente/public/img/fotos/<nombre>.webp --aspecto 4:3`, ponerlas en el `<figure className="ab-slot">` de `cliente/src/pages/Nosotros.jsx` y anotar la fuente en `docs/creditos-media.md`.

## Sitios que bloquearon la descarga automática

- `weg.net` (todas sus rutas) responde «Access Denied» incluso con Chrome real y en modo visible; Siemens responde «Human Verification». Se usaron los catálogos PDF oficiales de `static.weg.net`, que son públicos.
- Algunos enlaces de catálogos de buscadores devuelven 404: se reemplazaron por catálogos vigentes de las mismas líneas.

## Logos sin archivo

Sylvania, York, EMI y Centelsa: no se encontró un archivo oficial. Se muestra el nombre de la marca.

## Videos

Video de motor industrial, motorreductor o extractores: no existen en Coverr ni en Pixabay (solo renders y ventiladores domésticos). Las diapositivas de Motores, Motorreductores y Ventilación usan la foto oficial del producto sobre una foto de ambiente.

## Cómo asignar una imagen nueva

1. Procesarla: `python3 cliente/scripts/normalizar-producto.py <origen> cliente/public/img/catalogo/<nombre>` (recorta el fondo con rembg y deja 1000 × 1000 al 78 %, con la sombra única; genera también `-sm`).
2. Agregarla a `backend/database/data/line-images.json` (`"slug-de-la-linea": "/img/catalogo/<nombre>.webp"`).
3. Correr `php artisan db:seed --class=LineImageSeeder` (llena vacíos) o crear una migración de datos con `new LineImageSeeder(replaceLegacy: true)`; **probarla antes contra MySQL 8 estricto** (ver `docs/despliegue.md`).
4. Anotar la fuente en `docs/creditos-media.md`.


## Pendiente tras el rediseño 4
- **Nosotros, galería horizontal:** faltan las fotos del **interior del almacén**, del **mostrador** y del **equipo**; hoy muestran un panel «Próximamente» con el logo en marca de agua.
- **Extractor HCM-150N:** la foto disponible es de 500 px; pedir una de mayor resolución a Soler & Palau.
- **Energía solar:** paneles e inversores existen como categorías pero no tienen líneas en el catálogo; la tarjeta SolarWEG lleva a `/cotizar`.
