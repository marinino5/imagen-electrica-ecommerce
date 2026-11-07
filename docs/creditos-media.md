# Créditos de media: fuente y licencia

Todo lo de terceros viene de una fuente oficial del fabricante o de Pixabay. Se descargó con navegador normal o con la API oficial, con pausas entre descargas; **no se esquivó ninguna protección**.

## Imágenes de producto (fabricantes)

Procesadas con el mismo flujo: fondo blanco, producto centrado con 8 % de margen, cuadradas, WebP, sombra suave. Las fotos de los catálogos PDF son de 350 a 660 px de origen: se muestran bien en tarjetas, pero se ven suaves al ampliarlas. La asignación a cada línea está en `backend/database/data/line-images.json` (migración `2026_11_08_100100_assign_official_line_images`).

| Archivo en `cliente/public/img/productos/` | Marca | Fuente (PDF oficial en `static.weg.net/medias/downloadcenter/`) |
|---|---|---|
| `control/weg-contactores` | WEG | `hac/h16/WEG-operacion-y-proteccion-de-motores-hasta-18-5-kw-25-hp-50051422-catalogo-espanol.pdf` |
| `control/weg-accesorios-de-maniobra` | WEG | `hac/h16/WEG-operacion-y-proteccion-de-motores-hasta-18-5-kw-25-hp-50051422-catalogo-espanol.pdf` |
| `control/weg-reles-termicos` | WEG | `h2c/he8/WEG-reles-de-sobrecarga-termicos-RW-50070232-es.pdf` |
| `control/weg-reles-de-control` | WEG | `hb4/h1d/WEG-reles-temporizadores-protetores-e-de-nivel-50009830-catalogo-portugues-br-dc.pdf` |
| `control/weg-guardamotores` | WEG | `h19/he1/WEG-guardamotores-MPW-50030559-es.pdf` |
| `control/weg-arrancadores-directos` | WEG | `h72/h63/WEG-automation-catalog-power-and-control-products-enclosed-starters-us300c-en.pdf` |
| `control/weg-borneras` | WEG | `h1a/hf0/WEG-bornes-linea-btw-50034142-catalogo-espanol.pdf` |
| `control/weg-minibreakers` | WEG | `hec/h5f/WEG-solucoes-integradas-para-instalacoes-eletricas-50009824-pt.pdf` |
| `control/weg-totalizadores` | WEG | `h3c/h14/WEG-chaves-de-partida-50009814-catalogo-portugues-br-dc.pdf` |
| `control/weg-pulsadores` | WEG | `h0b/h58/WEG-linea-de-mando-y-senalizacion-csw-panorama-general-50066475-es.pdf` (recorte de la página 3) |
| `control/weg-cfw100` | WEG | `hd9/h2d/WEG-CFW100-50049400-catalogo-es.pdf` |
| `control/weg-cfw300, weg-cfw900` | WEG | `h24/he1/WEG-inversores-de-frequencia-10525554-catalogo-pt.pdf` |
| `control/weg-cfw500, weg-accesorios-variadores` | WEG | `h44/hc1/WEG-CFW500-catalog-50036260-es.pdf` |
| `control/weg-ssw05` | WEG | `h4e/h02/WEG-ssw-05-soft-starter-usassw0509-brochure-english.pdf` |
| `control/weg-ssw07` | WEG | `h8a/h9c/WEG-ssw-07-soft-starter-usassw07-brochure-english.pdf` |
| `control/weg-plc500` | WEG | `h05/h0f/WEG-PLC-programmable-logic-controllers-50096533-en.pdf` |
| `motores/weg-w22-iec` | WEG | `hf8/h29/WEG-w22-three-phase-electric-motor-50029265-brochure-english-web.pdf` |
| `motores/weg-w22-nema-premium, weg-w22-explosion, weg-w22-lateral` | WEG | `hae/hdc/US100-Standard-Catalog-Super-Premium-and-Explosion-Proof-Sections.pdf` |
| `motores/weg-w21-aluminio` | WEG | `h28/h24/WEG-WMO-W21-aluminium-multimounting-50111617-brochure-spanish-web.pdf` |
| `motores/weg-w12` | WEG | `h2e/h40/WEG-W12-motor-eletrico-monofasico-e-trifasico-50084475-brochure-portuguese-web.pdf` |
| `motores/weg-w22-monofasico` | WEG | `h52/h99/WEG-w22-single-phase-european-market-50069268-brochure-english-web.pdf` |
| `motores/weg-w01-semiabierto` | WEG | `h86/h18/WEG-motores-monofasicos-mercado-mexicano-catalogo-espanol.pdf` (motor monofásico armazón 48/56 de lámina rolada; recorte de la página 2) |
| `motores/weg-w01-cerrado` | WEG | `he6/h0c/WEG-WMO-jet-pump-motor-50108147-brochure-spanish-web.pdf` (motor monofásico de lámina de acero) |
| `reductores/weg-cestari-wcg01, weg-cestari-wcg20` | WEG Cestari | `h6e/hef/WEG-CESTARI-catalogo-geral-de-redutores-50115262-brochure-pt-web.pdf` |
| `medicion/fluke-multimetro-117`, `fluke-multimetro-117-kit` y `aplicaciones/fluke-en-tablero` | Fluke | Página oficial https://www.fluke.com/es-co/producto/comprobacion-electrica/multimetros-digitales/fluke-117 (`media.fluke.com`) |
| `ventilacion/soler-palau-extractor-tejado`, `soler-palau-extractor-centrifugo` | Soler & Palau | https://www.solerpalau.com (fotos de producto CRVB y CRMT). Se usan en la familia Ventilación; no hay foto de la línea de AIRAL / EMI |

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

Uso comercial permitido, sin atribución obligatoria. Archivos en `cliente/public/img/fotos/` y `familias/`.

| Archivo | Página de origen | Autor |
|---|---|---|
| `tecnico-casco.webp` | https://pixabay.com/photos/electrician-electric-electricity-1080590/ | jarmoluk |
| `electricista-tablero.webp` | https://pixabay.com/photos/electric-electricity-building-1080584/ | jarmoluk |
| `tablero-cerca.webp` | https://pixabay.com/photos/electric-electricity-building-1080585/ | jarmoluk |
| `planta-operando.webp` | https://pixabay.com/photos/lab-machine-technology-8499925/ | This_is_Engineering |
| `bodega.webp` | https://pixabay.com/photos/hangar-stock-suite-building-5183399/ | AJS1 |
| `bodega-cajas.webp` | https://pixabay.com/photos/factory-storehouse-box-700591/ | FIONASUN |
| `bodega-oscura.webp` | https://pixabay.com/photos/store-stacks-shelves-stock-5619201/ | tianya1223 |
| `entrega.webp` | https://pixabay.com/photos/packages-delivery-delivery-man-6153947/ | romeosessions |
| `pedido-empacado.webp` | https://pixabay.com/photos/boxes-parcels-deliver-cardboard-3883980/ | bluebudgie |
| `sector-construccion.webp` | https://pixabay.com/photos/construction-worker-safety-2578410/ | bridgesward |
| `sector-petroleo.webp` | https://pixabay.com/photos/factory-industrial-production-7026489/ | yamabon |
| `sector-agroindustria.webp` | https://pixabay.com/photos/industry-factory-working-place-5434589/ | iLights |
| `sector-alimentos.webp` | https://pixabay.com/photos/big-kitchen-kitchen-work-kitchen-2765065/ | vitesca |
| `sector-talleres.webp` | https://pixabay.com/photos/auto-repair-oil-change-oil-auto-3691963/ | DokaRyan |
| `sector-mineria.webp` | https://pixabay.com/photos/landscape-open-pit-mining-mining-4359640/ | 652234 |
| `asesor-mostrador.webp` | https://pixabay.com/photos/call-center-headset-smile-10378566/ | geralt |
| `asesor-linea.webp` | https://pixabay.com/photos/woman-headset-call-center-smiling-10378564/ | geralt |
| `recepcion.webp` | https://pixabay.com/photos/receptionists-phone-call-hotel-5975962/ | Rodrigo_SalomonHC |
| `tablero-manos.webp` | https://pixabay.com/photos/electrician-electric-electricity-1080574/ | jarmoluk |
| `obra-casco.webp` | https://pixabay.com/photos/construction-worker-construction-work-60585/ | WikiImages |
| `familias/cables.webp` | https://pixabay.com/photos/cables-cable-cable-drum-electricity-2147464/ | Bru-nO |
| `familias/iluminacion.webp` | https://pixabay.com/photos/led-highbay-hall-radiator-5444366/ | maxsel |

Conservadas de la entrega anterior (Pixabay): `aplicaciones/*` (bandas, bombeo, ventilación, tableros, solar), `fotos/crear-cuenta*` y `fotos/contacto*`. La fachada (`fachada.webp`) y los logos de la empresa son propios.

## Video del hero (`cliente/public/media/`)

| Diapositiva | Archivos | Fuente | Autor |
|---|---|---|---|
| Automatización y control | `hero-d2*` | https://pixabay.com/videos/id-86252/ (segundos 1 a 10, cableado de un tablero; sin el tramo del técnico borroso) | lumosajans |
| Motores, Motorreductores, Ventilación | Sin video: foto oficial del producto sobre foto de ambiente | Coverr no tiene motores, motorreductores ni extractores industriales (consultas en `imagenes-pendientes.md`) | |

## Coverr

Se volvió a consultar la API (`factory machine`, `forklift`, `welding`, `electric`, `machine`, `power plant`, `technician`, `cables`, `warehouse shelves`): solo hay videos de madera, máquinas de café, routers y similares. **No se usó ningún archivo de Coverr.** Licencia aplicable: https://coverr.co/license.
