# Auditoría de imágenes de producto

Revisión una por una de las 39 imágenes de `cliente/public/img/catalogo/` (34 líneas más las vistas alternas y las portadas de familia), abiertas a tamaño real sobre fondo oscuro (violeta-950) y claro. Criterios: resolución de origen, bordes (halos blancos, cortes dentados, sombras pegadas) y que no parezcan de juguete, renders falsos o producto doméstico.

**Resultado:** 11 imágenes cambiaron (2 con otra fuente oficial y 9 re-recortadas); 1 se conserva con reservas; el resto está OK en cuanto a bordes. **La resolución de origen es el límite que queda:** las fotos de WEG vienen de catálogos PDF cuyas imágenes miden entre 300 y 900 px, y `weg.net` (centro de medios) responde 403, así que no hay forma de bajar fotos a 300 dpi por SKU sin que la empresa las pida a WEG. Se probó extraer más resolución con PyMuPDF: las imágenes del PDF ya vienen a ese tamaño y renderizar la página a 300 dpi no agrega detalle.

## Método

- Recorte: `cliente/scripts/normalizar-producto.py` ahora usa **rembg con alpha matting**; `--blanco` borra el blanco y los reflejos del piso pegados al borde (hasta 70 px) y `--huecos` el blanco encerrado dentro del producto (lo que se ve a través de un ventilador). Después, erosión de 1 px y suavizado. Se comprobó cada resultado sobre fondo oscuro y claro.
- Cuando el PDF trae la máscara alfa del fabricante (`smask`) se usa esa en lugar de recortar (minibreakers).
- «Resolución efectiva» = lado mayor del producto a partir del cual la imagen deja de perder detalle al reducirla y volver a ampliarla (estimación propia; subestima las imágenes de superficies lisas). Los lienzos finales son de 1000 × 1000 px (más `-sm` de 480 px); «Producto en el lienzo» es el tamaño real de lo recortado.

| Imagen | Estado | Producto en el lienzo | Resolución efectiva (aprox.) | Qué se encontró / qué se hizo | Fuente |
|---|---|---|---|---|---|
| `abac-compresor-vertical` | OK | 351 × 774 | ≈ 700 px | Bordes limpios sobre fondo oscuro y claro | Catálogo oficial ABAC serie PRO |
| `airmax-gl-persianas` | OK | 766 × 732 | ≈ 690 px | Bordes limpios sobre fondo oscuro y claro | AIRAL (airal.com.co/airmaxgl) |
| `airmax-gl` | OK | 764 × 754 | ≈ 760 px | Bordes limpios sobre fondo oscuro y claro | AIRAL (airal.com.co/airmaxgl) |
| `centelsa-cable-thhn` | REEMPLAZADA | 770 × 720 | ≈ 540 px | Cable cortado por el borde de la imagen (diagonal en la esquina) → rollo completo Nexans Línea Plus THHN/THWN-2 (Centelsa) | Nexans Colombia, https://www.nexans.co/.rest/eservice/dam/v1/image/259435 (800 × 800; el rollo mide ≈ 640 × 600) |
| `fluke-multimetro-117-kit` | OK | 768 × 715 | ≈ 610 px | Bordes limpios sobre fondo oscuro y claro | Fluke Colombia (media.fluke.com) |
| `fluke-multimetro-117` | OK | 571 × 766 | ≈ 610 px | Bordes limpios sobre fondo oscuro y claro | Fluke Colombia (media.fluke.com) |
| `soler-palau-extractor-axial` | REEMPLAZADA | 748 × 772 | ≈ 690 px | Extractor blanco/verde de ventana (doméstico) → extractor axial tubular industrial galvanizado THGT de Soler & Palau, con motor y aspas metálicas | https://www.solerpalau.com/media/catalog/product/T/H/THGT_product4.jpg (foto oficial 900 × 900 px) |
| `soler-palau-extractor-centrifugo` | OK | 767 × 766 | ≈ 420 px | Bordes limpios sobre fondo oscuro y claro | Soler & Palau (solerpalau.com) |
| `soler-palau-extractor-tejado` | OK | 767 × 719 | ≈ 310 px | Bordes limpios sobre fondo oscuro y claro. **Baja resolución:** se ve suave al ampliar la ficha (1000 px) | Soler & Palau (solerpalau.com) |
| `sylvania-start-highbay` | OK | 761 × 376 | ≈ 610 px | Bordes limpios sobre fondo oscuro y claro | Sylvania Colombia (sylvania-colombia.com) |
| `weg-accesorios-de-maniobra` | OK | 770 × 437 | ≈ 620 px | Bordes limpios sobre fondo oscuro y claro | PDF oficial de WEG (static.weg.net, ver creditos-media.md) |
| `weg-accesorios-variadores` | RE-RECORTADA | 595 × 765 | ≈ 540 px | Ruido y restos junto al ventilador | Misma foto; rembg con alpha matting + limpieza de blanco |
| `weg-arrancadores-directos` | OK | 768 × 709 | ≈ 380 px | Bordes limpios sobre fondo oscuro y claro | PDF oficial de WEG (static.weg.net, ver creditos-media.md) |
| `weg-borneras` | OK | 765 × 733 | ≈ 380 px | Bordes limpios sobre fondo oscuro y claro | PDF oficial de WEG (static.weg.net, ver creditos-media.md) |
| `weg-cestari-wcg01` | OK | 729 × 768 | ≈ 310 px | Bordes limpios sobre fondo oscuro y claro. **Baja resolución:** se ve suave al ampliar la ficha (1000 px) | PDF oficial de WEG (static.weg.net, ver creditos-media.md) |
| `weg-cestari-wcg20` | RE-RECORTADA | 757 × 515 | ≈ 450 px | Halo blanco y sombra pegada bajo la base | Misma foto; rembg con alpha matting + limpieza de blanco (umbral 186) |
| `weg-cfw100` | RE-RECORTADA | 564 × 767 | ≈ 270 px | Restos blancos del piso en la base | Misma foto de PDF oficial de WEG (static.weg.net, ver creditos-media.md); rembg con alpha matting + limpieza de blanco |
| `weg-cfw300` | RE-RECORTADA | 767 × 566 | ≈ 350 px | Aletas blancas del reflejo en la base (cortes dentados) | Misma foto; rembg con alpha matting + limpieza de blanco |
| `weg-cfw500` | OK | 381 × 766 | ≈ 540 px | Bordes limpios sobre fondo oscuro y claro | PDF oficial de WEG (static.weg.net, ver creditos-media.md) |
| `weg-cfw900` | RE-RECORTADA | 768 × 364 | ≈ 610 px | Repisa blanca bajo la fila de variadores | Misma foto; rembg con alpha matting + limpieza de blanco |
| `weg-contactores` | OK | 574 × 764 | ≈ 310 px | Bordes limpios sobre fondo oscuro y claro. **Baja resolución:** se ve suave al ampliar la ficha (1000 px) | PDF oficial de WEG (static.weg.net, ver creditos-media.md) |
| `weg-guardamotores` | OK | 477 × 766 | ≈ 340 px | Bordes limpios sobre fondo oscuro y claro. **Baja resolución:** se ve suave al ampliar la ficha (1000 px) | PDF oficial de WEG (static.weg.net, ver creditos-media.md) |
| `weg-minibreakers` | RE-RECORTADA | 771 × 511 | ≈ 620 px | Mancha gris difusa (la puerta translúcida) y borde con cortes | Imagen de la p. 1 del PDF «Soluciones integradas para instalaciones eléctricas» con su máscara alfa real (smask), 660 × 479 px |
| `weg-plc500` | RE-RECORTADA | 766 × 764 | ≈ 340 px | Manchas oscuras en la parte baja izquierda | Misma foto; rembg con alpha matting + limpieza de blanco |
| `weg-pulsadores` | OK | 765 × 207 | ≈ 460 px | Bordes limpios sobre fondo oscuro y claro | PDF oficial de WEG (static.weg.net, ver creditos-media.md) |
| `weg-reles-de-control` | OK | 769 × 588 | ≈ 310 px | Bordes limpios sobre fondo oscuro y claro. **Baja resolución:** se ve suave al ampliar la ficha (1000 px) | PDF oficial de WEG (static.weg.net, ver creditos-media.md) |
| `weg-reles-termicos` | OK | 703 × 769 | ≈ 350 px | Bordes limpios sobre fondo oscuro y claro. **Baja resolución:** se ve suave al ampliar la ficha (1000 px) | PDF oficial de WEG (static.weg.net, ver creditos-media.md) |
| `weg-ssw05` | OK | 480 × 769 | ≈ 540 px | Bordes limpios sobre fondo oscuro y claro | PDF oficial de WEG (static.weg.net, ver creditos-media.md) |
| `weg-ssw07` | RE-RECORTADA | 556 × 766 | ≈ 540 px | Halo gris en el borde | Misma foto; rembg con alpha matting |
| `weg-totalizadores` | RE-RECORTADA | 461 × 728 | ≈ 220 px | Base rota con fragmentos blancos | Misma foto; alpha matting, limpieza de blanco y corte recto bajo el cuerpo. Con reservas: la foto original ya venía cortada en la base |
| `weg-w01-cerrado` | OK | 768 × 622 | ≈ 420 px | Bordes limpios sobre fondo oscuro y claro | PDF oficial de WEG (static.weg.net, ver creditos-media.md) |
| `weg-w01-semiabierto` | OK | 762 × 617 | ≈ 190 px | Bordes limpios sobre fondo oscuro y claro. **Baja resolución:** se ve suave al ampliar la ficha (1000 px) | PDF oficial de WEG (static.weg.net, ver creditos-media.md) |
| `weg-w12` | OK | 748 × 358 | ≈ 600 px | Bordes limpios sobre fondo oscuro y claro | PDF oficial de WEG (static.weg.net, ver creditos-media.md) |
| `weg-w21-aluminio` | OK | 769 × 752 | ≈ 270 px | Bordes limpios sobre fondo oscuro y claro. **Baja resolución:** se ve suave al ampliar la ficha (1000 px) | PDF oficial de WEG (static.weg.net, ver creditos-media.md) |
| `weg-w22-explosion` | OK | 764 × 747 | ≈ 380 px | Bordes limpios sobre fondo oscuro y claro | PDF oficial de WEG (static.weg.net, ver creditos-media.md) |
| `weg-w22-iec` | OK | 770 × 441 | ≈ 420 px | Bordes limpios sobre fondo oscuro y claro | PDF oficial de WEG (static.weg.net, ver creditos-media.md) |
| `weg-w22-lateral` | OK | 769 × 488 | ≈ 540 px | Bordes limpios sobre fondo oscuro y claro | PDF oficial de WEG (static.weg.net, ver creditos-media.md) |
| `weg-w22-monofasico` | CONSERVADA CON RESERVAS | 748 × 719 | ≈ 300 px | Vista despiezada (render oficial de WEG) con piezas sueltas; 748 px | PDF «W22 single phase European market» (WEG). La foto del motor armado del mismo PDF mide solo 324 px |
| `weg-w22-nema-premium` | OK | 768 × 507 | ≈ 540 px | Bordes limpios sobre fondo oscuro y claro | PDF oficial de WEG (static.weg.net, ver creditos-media.md) |

## Imágenes con baja resolución que siguen pendientes

Se ven bien en tarjetas (480 px) y suaves al ampliar la ficha. Para mejorarlas hace falta la foto oficial por línea de WEG (centro de medios, con distribuidor) o de la marca:

`soler-palau-extractor-tejado` (≈ 310 px), `weg-cestari-wcg01` (≈ 310 px), `weg-contactores` (≈ 310 px), `weg-guardamotores` (≈ 340 px), `weg-reles-de-control` (≈ 310 px), `weg-reles-termicos` (≈ 350 px), `weg-w01-semiabierto` (≈ 190 px), `weg-w21-aluminio` (≈ 270 px).

## Portadas y fotos de ambiente

- **Portadas de familia** (`FAMILY_PHOTO`): w22 nema premium (OK), WCG20 (re-recortada), CFW500 (OK), **extractor axial industrial (reemplazada)**, **rollo THHN (reemplazada)**, Sylvania START (OK), ABAC compresor vertical (OK) y Fluke 117 (OK).
- **Hero:** `fotos/hero-banda.webp` (banda de rodillos con su motorreductor, Pixabay 4000 × 3000 px) y `fotos/hero-extractores.webp` (dos extractores axiales reales, Pixabay 4912 × 3264 px), ambas a 1920 px; las dos pasaron por `gradar-foto.py`.
- **Mapa de la tienda** (`img/mapa-tienda.webp`): recorte de 900 × 520 px de teselas de OpenStreetMap (© colaboradores de OpenStreetMap, ODbL), descargadas una sola vez.

## Pendiente

- Fotos oficiales por SKU de WEG (403 en `weg.net`) para subir la resolución de las líneas de la lista anterior.
- Foto de la marca y el modelo del compresor vertical (ver `imagenes-pendientes.md`).
- Foto propia de un asesor en un mostrador (Contacto usa hoy la foto de asesoría en tablero).
