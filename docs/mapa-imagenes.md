# Mapa de imágenes y videos

Cada imagen o video de la tienda con su página, sección y fuente. Regla: **una imagen, un lugar**. Los productos recortados (`catalogo/*`) son la excepción natural: la foto de una línea aparece en su tarjeta, su ficha y el carrito, porque es el mismo producto. Fuentes y licencias en detalle: `docs/creditos-media.md`.

Origen abreviado: **Pexels** = [Pexels License](https://www.pexels.com/license/) (uso comercial, sin atribución), originales de 2400 px en `cliente/media-fuente/r7/`; **Pixabay** = Pixabay Content License; **Mixkit** = [licencia de Mixkit](https://mixkit.co/license/); **propia** = de la empresa; **fabricante** = foto oficial del producto.

## Inicio (`/`)

| Sección | Archivo | Origen |
|---|---|---|
| Hero · 1 · Cables (video) | `media/hero-v3*` (+ póster) | Pixabay video 86252 (manos cableando un tablero) |
| Hero · 2 · Motorreductores | `fotos/hero-banda.webp` + `catalogo/weg-cestari-wcg01.webp` | Pixabay 1137993 + fabricante (WEG) |
| Hero · 3 · Banda transportadora (video) | `media/hero-v1*` (+ póster) | Mixkit 20770 |
| Hero · 4 · Ventilación | `fotos/hero-extractores.webp` + `catalogo/soler-palau-extractor-axial.webp` | Pixabay 2182767 + fabricante (Soler & Palau) |
| Familias | `catalogo/*-sm.webp` | fabricante |
| Ofertas / lo más pedido | foto de cada línea (`catalogo/*`) | fabricante |
| Asesoría (bloque extendido) | `fotos/asesoria-tablero.webp` | Pixabay 2755683 |
| Entrega (bloque extendido) | `fotos/entrega-almacen.webp` | Pixabay 5983142 |
| Sectores (7 tarjetas) | `fotos/sector-*.webp` | Pixabay |
| Marcas | `marcas/*` | logos oficiales / Wikimedia |
| Cierre | `fotos/tecnico-casco.webp` | Pixabay 1080590 |

## Catálogo (`/productos`)

| Sección | Archivo | Origen |
|---|---|---|
| Vitrina del banner | `catalogo/weg-w22-nema-premium`, `weg-cestari-wcg20`, `weg-cfw500`, `weg-contactores`, `fluke-multimetro-117` | fabricante |
| Cabeceras de familia / miniaturas | `catalogo/*-sm.webp` | fabricante |
| Franja editorial (una por página) | `fotos/tecnico-motor.webp` | Pexels 34526423 |
| Tarjeta «Motores de otras marcas» | sin foto WEG: diseño de marca + logos ABB, Match Drive, Nidec, Nord | ver pendientes |

## Cómo comprar (`/como-comprar`)

| Sección | Archivo | Origen |
|---|---|---|
| Hero (portátil y celular) | `como-comprar/portatil.webp`, `ficha.webp` | capturas propias de la tienda (`scripts/capturar-mockups.mjs`) |
| Compra en línea · celular fijo | `como-comprar/catalogo`, `ficha`, `carrito`, `confirmacion` | capturas propias |
| Cotización · celular fijo | `como-comprar/cotizar`, `adjuntar`, `cotizacion-enviada` + pantalla de WhatsApp (maqueta HTML) | capturas propias |
| Lo que recibes · producto en su caja | `fotos/comprar-caja.webp` | Pexels 11781094 |
| Lo que recibes · técnico | `fotos/comprar-tecnico.webp` | Pexels 33694016 |
| Lo que recibes · despacho | `fotos/comprar-despacho.webp` | Pexels 4487383 |
| Envíos | mapa SVG de Colombia (dibujado en código) | propia |

## Contacto (`/contacto`)

| Sección | Archivo | Origen |
|---|---|---|
| Hero | `fotos/contacto-mostrador.webp` | Pexels 12917478 (volteada) |
| Bento · Cotizar un proyecto | `fotos/contacto-cotizar.webp` | Pexels 6615107 |
| Bento · Asesoría técnica | `fotos/contacto-asesoria.webp` | Pexels 8867631 |
| Bento · Estado de mi pedido | `fotos/contacto-pedido.webp` | Pexels 6868621 |
| Bento · Garantías y devoluciones | `fotos/contacto-garantia.webp` | Pexels 9242911 |
| Visítanos · mapa | `mapa-tienda.webp` | © OpenStreetMap (ODbL) |
| Visítanos · interior del almacén | panel «Foto próximamente» | pendiente |

## Nosotros (`/nosotros`)

| Sección | Archivo | Origen |
|---|---|---|
| Hero (la foto que crece) | `fachada.webp` | propia |
| Muro de producto | `catalogo/*-sm.webp` | fabricante |
| Cómo trabajamos · asesoría | `fotos/nosotros-asesoria.webp` | Pexels 34054501 |
| Cómo trabajamos · marcas en estantería | `fotos/nosotros-estanteria.webp` | Pexels 38195854 (estantería de cajas; sin marcas visibles) |
| Cómo trabajamos · despacho | `fotos/nosotros-despacho.webp` | Pexels 4480982 |
| Equipo y almacén | paneles «Foto próximamente» | pendiente |
| Marcas | `marcas/*` | logos oficiales |

## Cuenta (`/ingresar`, `/crear-cuenta`, `/recuperar`)

Riel de producto con fotos nítidas a 180 px: `catalogo/weg-minibreakers`, `weg-cfw500`, `soler-palau-extractor-axial`, `fluke-multimetro-117`, `weg-accesorios-variadores` (carril 1) y `airmax-gl`, `weg-cestari-wcg20`, `fluke-multimetro-117-kit`, `weg-cfw900`, `airmax-gl-persianas` (carril 2, con 2 px de desenfoque). Origen: fabricante.

## Otras pantallas

| Pantalla | Archivo | Origen |
|---|---|---|
| 404 | `fotos/busca-portatil.webp` | Pixabay 4941347 |
| Ficha · «Dónde se usa» | `aplicaciones/*`, `fotos/sector-*`, `almacen-*`, `planta-operando`, `tablero-manos` | Pixabay (ver repetidos) |
| Menú / familias (portadas) | `fotos/electricista-tablero`, `tablero-cerca`, `almacen-oscuro`, `planta-operando` y `aplicaciones/*` | Pixabay |

## Repetidos que quedan

- **Ficha · «Dónde se usa»** (`lib/datasheets.js`) usa las mismas fotos de sector y de planta para varias familias (por ejemplo `planta-operando` en cuatro) y repite las de la tarjeta de Sectores del Inicio. Se dejó así porque son fotos de contexto por aplicación, no de una página; cambiarlas pide fotos nuevas por cada aplicación.
- Las portadas de familia (`lib/families.js`) reutilizan `planta-operando`, `bandas`, `ventilacion`, `bombeo` y `fluke-en-tablero` de la ficha.

Ya no se repiten: el trabajador del hero en la franja editorial del Catálogo (ahora `tecnico-motor`), y la foto de asesoría del Inicio en el hero de Contacto (ahora `contacto-mostrador`).

## Pendientes de imagen

1. **Foto oficial de un motor ABB** para «Motores de otras marcas». `new.abb.com` bloquea las descargas automáticas (error de protocolo) y no se esquivó; mientras tanto la tarjeta muestra el diseño de marca con los logos de sus marcas, en lugar de un motor WEG.
2. **Estantería con marcas reales de la tienda** (la actual es de cajas rotuladas, sin marcas visibles), **equipo** y **almacén por dentro** (Nosotros y Contacto).
3. **Video de cables** propio: el primero del hero es el de manos cableando un tablero (Pixabay), no un video de un rollo o un cable THHN.
