# Créditos de media: fuente y licencia

Todo lo de terceros viene de una fuente oficial del fabricante o de Pixabay. Se descargó con navegador normal o con la API oficial, con pausas entre descargas; **no se esquivó ninguna protección**.

## Imágenes de producto (fabricantes)

**Formato único (nivel 2):** WebP con fondo transparente, 1000 × 1000 px, producto al 78 % del lienzo, centrado y sobre la misma línea base, con la misma sombra de contacto. Se recortó el fondo con rembg (modelo u2net) y se normalizó con `cliente/scripts/normalizar-producto.py`; los originales con fondo blanco quedaron en `cliente/media-fuente/productos/` (carpeta ignorada por git). Los archivos están en `cliente/public/img/catalogo/` (más una versión `-sm` de 480 px). Las fotos de los catálogos PDF son de 350 a 660 px de origen: se ven bien en tarjetas y suaves al ampliarlas. La asignación a cada línea está en `backend/database/data/line-images.json` (migración `2026_11_09_100000_assign_cutout_product_images`) y el detalle por SKU en `docs/imagenes-referencias.csv`.

| Archivo en `cliente/public/img/catalogo/` | Marca | Fuente (PDF oficial en `static.weg.net/medias/downloadcenter/` salvo que se indique) |
|---|---|---|
| `weg-contactores`, `weg-accesorios-de-maniobra` | WEG | `hac/h16/WEG-operacion-y-proteccion-de-motores-hasta-18-5-kw-25-hp-50051422-catalogo-espanol.pdf` |
| `weg-reles-termicos` | WEG | `h2c/he8/WEG-reles-de-sobrecarga-termicos-RW-50070232-es.pdf` |
| `weg-reles-de-control` | WEG | `hb4/h1d/WEG-reles-temporizadores-protetores-e-de-nivel-50009830-catalogo-portugues-br-dc.pdf` |
| `weg-guardamotores` | WEG | `h19/he1/WEG-guardamotores-MPW-50030559-es.pdf` |
| `weg-arrancadores-directos` | WEG | `h72/h63/WEG-automation-catalog-power-and-control-products-enclosed-starters-us300c-en.pdf` |
| `weg-borneras` | WEG | `h1a/hf0/WEG-bornes-linea-btw-50034142-catalogo-espanol.pdf` |
| `weg-minibreakers` | WEG | `hec/h5f/WEG-solucoes-integradas-para-instalacoes-eletricas-50009824-pt.pdf` |
| `weg-totalizadores` | WEG | `h3c/h14/WEG-chaves-de-partida-50009814-catalogo-portugues-br-dc.pdf` |
| `weg-pulsadores` | WEG | `h0b/h58/WEG-linea-de-mando-y-senalizacion-csw-panorama-general-50066475-es.pdf` (recorte de la página 3) |
| `weg-cfw100` | WEG | `hd9/h2d/WEG-CFW100-50049400-catalogo-es.pdf` |
| `weg-cfw300`, `weg-cfw900` | WEG | `h24/he1/WEG-inversores-de-frequencia-10525554-catalogo-pt.pdf` |
| `weg-cfw500`, `weg-accesorios-variadores` | WEG | `h44/hc1/WEG-CFW500-catalog-50036260-es.pdf` |
| `weg-ssw05` | WEG | `h4e/h02/WEG-ssw-05-soft-starter-usassw0509-brochure-english.pdf` |
| `weg-ssw07` | WEG | `h8a/h9c/WEG-ssw-07-soft-starter-usassw07-brochure-english.pdf` |
| `weg-plc500` | WEG | `h05/h0f/WEG-PLC-programmable-logic-controllers-50096533-en.pdf` |
| `weg-w22-iec` | WEG | `hf8/h29/WEG-w22-three-phase-electric-motor-50029265-brochure-english-web.pdf` |
| `weg-w22-nema-premium`, `weg-w22-explosion`, `weg-w22-lateral` | WEG | `hae/hdc/US100-Standard-Catalog-Super-Premium-and-Explosion-Proof-Sections.pdf` |
| `weg-w21-aluminio` | WEG | `h28/h24/WEG-WMO-W21-aluminium-multimounting-50111617-brochure-spanish-web.pdf` |
| `weg-w12` | WEG | `h2e/h40/WEG-W12-motor-eletrico-monofasico-e-trifasico-50084475-brochure-portuguese-web.pdf` |
| `weg-w22-monofasico` | WEG | `h52/h99/WEG-w22-single-phase-european-market-50069268-brochure-english-web.pdf` |
| `weg-w01-semiabierto` | WEG | `h86/h18/WEG-motores-monofasicos-mercado-mexicano-catalogo-espanol.pdf` (página 2) |
| `weg-w01-cerrado` | WEG | `he6/h0c/WEG-WMO-jet-pump-motor-50108147-brochure-spanish-web.pdf` |
| `weg-cestari-wcg01`, `weg-cestari-wcg20` | WEG Cestari | `h6e/hef/WEG-CESTARI-catalogo-geral-de-redutores-50115262-brochure-pt-web.pdf` |
| `fluke-multimetro-117`, `fluke-multimetro-117-kit` | Fluke | https://www.fluke.com/es-co/producto/comprobacion-electrica/multimetros-digitales/fluke-117 (`media.fluke.com`) |
| `soler-palau-extractor-tejado`, `soler-palau-extractor-centrifugo` | Soler & Palau | https://www.solerpalau.com (fotos de producto CRVB y CRMT) |
| `airmax-gl`, `airmax-gl-persianas` | AIRAL | https://www.airal.com.co/airmaxgl (vistas frontal y de persianas del AirMax GL; sitio oficial de la marca, descarga normal con pausas) |
| `thhn-centelsa` | Centelsa by Nexans | https://www.nexans.co/.rest/eservice/dam/v1/image/259435, imagen de la ficha oficial de Nexans Colombia (https://www.nexans.co/en/products/Construcci%C3%B3n/Cables-de-Cobre-de-Baja-Tensi%C3%B3n-Aislados/Alambre-THHN-THWN-2/Wire-Type-24145.html); se recortó el rollo del banner |
| `sylvania-start-highbay` | Sylvania | https://sylvania-colombia.com/media/c2xp4nhs/gc101-st-producto-05.jpg (START High Bay GC101-ST, https://sylvania-colombia.com/productos/sylvania-start/led-high-bay-gc101-st/) |
| `compresor-ilustracion.svg` | Imagen Eléctrica | Ilustración propia (sin foto oficial: falta la marca del equipo) |

Las imágenes de fabricantes se usan para identificar productos que la empresa distribuye; si un fabricante pide retirarlas, se cambian por la ilustración de marca.

## Logos de marcas (`cliente/public/img/marcas/`)

| Marca | Fuente |
|---|---|
| WEG | Recorte vectorial del logo en los catálogos oficiales de WEG (`static.weg.net`) |
| WEG Cestari | https://wegcestari.com.br (SVG del sitio) |
| Nord | https://pattern-library.nord.com/v2/img/nord-logo.svg |
| Soler & Palau | https://www.solerpalau.com (SVG del sitio) |
| AIRAL | https://www.airal.com.co (logo del sitio) |
| Nexans, ABB, Siemens, Fluke, Nidec, Delta | Wikimedia Commons (archivos de logo; el sitio del fabricante bloquea la descarga automática) |
| Sylvania, York, EMI, Centelsa | Sin archivo: la tienda muestra el nombre en la tipografía de marca (ver `docs/imagenes-pendientes.md`) |

## Fotos de ambiente (Pixabay, [Pixabay Content License](https://pixabay.com/service/license-summary/))

Uso comercial permitido, sin atribución obligatoria (se anota el autor por cortesía). **Tratamiento único (nivel 2):** todas pasan por `cliente/scripts/gradar-foto.py` (tono ligeramente frío, contraste 1.07, saturación .93); los originales de Pixabay están en `cliente/media-fuente/`. Archivos en `cliente/public/img/fotos/`, `aplicaciones/` y `fachada.webp` (propia).

| Archivo | Página de origen | Autor | Dónde se usa |
|---|---|---|---|
| `asesoria-tablero.webp` | https://pixabay.com/photos/electrician-electric-electricity-2755683/ | jarmoluk | Inicio, bloque de asesoría |
| `entrega-bodega.webp` | https://pixabay.com/photos/hand-product-finished-goods-5983142/ | KaweewatT | Inicio, bloque de entrega |
| `bodega-pasillo.webp` | https://pixabay.com/photos/factory-plant-industrial-hall-1137992/ | falco | Cómo comprar, encabezado |
| `busca-portatil.webp` | https://pixabay.com/photos/engineer-engineering-4941347/ | This_is_Engineering | Cómo comprar, paso «Busca» |
| `despacho-montacargas.webp` | https://pixabay.com/photos/steel-coil-warehouse-4395773/ | jotoler | Cómo comprar, paso «Recibe»; «Dónde se usa» |
| `sector-talleres.webp` | https://pixabay.com/photos/human-work-industry-man-one-2164189/ | stafichukanatoly | Sectores |
| `sector-alimentos.webp` | https://pixabay.com/photos/cider-bottling-plant-production-line-4484017/ | markdnalee | Sectores |
| `sector-construccion.webp` | https://pixabay.com/photos/construction-worker-safety-2578410/ | bridgesward | Sectores |
| `sector-petroleo.webp` | https://pixabay.com/photos/factory-industrial-production-7026489/ | yamabon | Sectores |
| `sector-agroindustria.webp` | https://pixabay.com/photos/industry-factory-working-place-5434589/ | iLights | Sectores |
| `sector-mineria.webp` | https://pixabay.com/photos/landscape-open-pit-mining-mining-4359640/ | 652234 | Sectores |
| `tecnico-casco.webp` | https://pixabay.com/photos/electrician-electric-electricity-1080590/ | jarmoluk | Inicio, cierre |
| `electricista-tablero.webp` | https://pixabay.com/photos/electric-electricity-building-1080584/ | jarmoluk | Catálogo, Nosotros |
| `tablero-cerca.webp` | https://pixabay.com/photos/electric-electricity-building-1080585/ | jarmoluk | Nosotros |
| `tablero-manos.webp` | https://pixabay.com/photos/electrician-electric-electricity-1080574/ | jarmoluk | Catálogo, 404 |
| `planta-operando.webp` | https://pixabay.com/photos/lab-machine-technology-8499925/ | This_is_Engineering | Hero, ficha |
| `bodega.webp` | https://pixabay.com/photos/hangar-stock-suite-building-5183399/ | AJS1 | Inicio, cierre |
| `bodega-cajas.webp` | https://pixabay.com/photos/factory-storehouse-box-700591/ | FIONASUN | Nosotros |
| `bodega-oscura.webp` | https://pixabay.com/photos/store-stacks-shelves-stock-5619201/ | tianya1223 | Hero, Nosotros |
| `contacto.webp` | Pixabay (módulos en un tablero) | | Contacto |
| `aplicaciones/*` (bandas, bombeo, ventilación, fluke-en-tablero) | Pixabay / Fluke | | Ficha, «Dónde se usa» |

**Retiradas en este nivel** (no eran industriales o no coincidían con el texto): `asesor-mostrador`, `asesor-linea`, `recepcion`, `entrega` (mensajero en una puerta), `pedido-empacado`, `obra-casco`, `crear-cuenta*` y las de `familias/`. Quedan en `cliente/media-fuente/descartados-nivel2/`.

La **fachada** (`fachada.webp`) y los logos de la empresa son propios. Las capturas de los celulares de «Cómo comprar» (`public/img/mockups/`) son de la propia tienda (`cliente/scripts/capturar-mockups.mjs`).

## Video del hero (`cliente/public/media/`)

| Diapositiva | Archivos | Fuente | Autor |
|---|---|---|---|
| Automatización y control | `hero-d2*` | https://pixabay.com/videos/id-86252/ (segundos 1 a 10, cableado de un tablero; sin el tramo del técnico borroso) | lumosajans |
| Motores, Motorreductores, Ventilación | Sin video: foto oficial del producto sobre foto de ambiente | Coverr no tiene motores, motorreductores ni extractores industriales (consultas en `imagenes-pendientes.md`) | |

## Coverr

Se volvió a consultar la API (`factory machine`, `forklift`, `welding`, `electric`, `machine`, `power plant`, `technician`, `cables`, `warehouse shelves`): solo hay videos de madera, máquinas de café, routers y similares. **No se usó ningún archivo de Coverr.** Licencia aplicable: https://coverr.co/license.
