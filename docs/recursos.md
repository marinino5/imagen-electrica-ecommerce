# Recursos visuales: inventario y licencias

Regla: lo que no tenga derechos comprobados se marca **NO USAR** y no aparece en la tienda.

## Videos del hero

| Archivo | Origen | Licencia | Ubicación | Estado |
|---|---|---|---|---|
| hero-tablero (mp4, webm, poster) | Generado con IA, marca de agua retirada | No comprobada | `cliente/media-fuente/descartados/` (fuera de git) | **NO USAR** |
| hero-motores (mp4, webm, poster) | Generado con IA, marca de agua retirada | No comprobada | `cliente/media-fuente/descartados/` (fuera de git) | **NO USAR** |

Los dos videos anteriores se retiraron del hero y de `cliente/public/media/`.

### Videos nuevos (Pixabay)

Licencia de todos: [Pixabay Content License](https://pixabay.com/service/license-summary/) (uso comercial permitido, sin atribución obligatoria; no se puede dar a entender que el autor o una marca respalda a la empresa). Descargados con la API oficial (tamaño `medium` o `large`) a `cliente/media-fuente/videos/` (fuera de git). Revisados cuadro a cuadro: sin logos ni marcas visibles.

| Diapositiva | Origen (pageURL) | Autor | Archivos en `public/media/` | Notas |
|---|---|---|---|---|
| D2 Automatización y control | https://pixabay.com/videos/id-159674/ (0 a 5 s) + https://pixabay.com/videos/id-86252/ (6 a 11 s), unidos con fundido en un solo clip de 9,4 s | CreativeCommonsCentral y lumosajans | `hero-d2.mp4/.webm`, `hero-d2-m.mp4/.webm`, `hero-d2-poster.webp` | Técnico con casco en un tablero y cableado de control. Pixabay no garantiza autorización de imagen de la persona: se ve de espaldas y de perfil; confirmar antes de publicar |
| D3 Extractores e iluminación | https://pixabay.com/videos/id-235032/ (9 s) | phlyingdoctor | `hero-d3.mp4/.webm`, `hero-d3-m.mp4/.webm`, `hero-d3-poster.webp` | Unidades de ventilación con ventiladores girando en una cubierta industrial. No muestra iluminación de nave: Pixabay no tiene una toma equivalente |
| D1 Motores y motorreductores | **Imagen IA provisional (pendiente de descargar)** | Generada en Canva | `scripts/kenburns-hero.sh` listo | Imagen fotorrealista de un motor industrial azul sin logos ni texto, generada en Canva (media `MAHaKfBbqg4`, enlace en Canva desde la sesión). Las herramientas solo entregan una miniatura de 199 px: hay que abrirla en Canva, descargarla (idealmente 4K) a `cliente/media-fuente/` y correr `scripts/kenburns-hero.sh imagen.png hero-d1`, que hace el movimiento lento de 8 s en loop. Es material IA provisional, no un equipo real ni de WEG. Antes de eso, la diapositiva usa el fondo de marca. Pixabay no tiene motores industriales ni motorreductores reales (solo motores de auto, motos, barcos y renders). No se usa material que no muestre lo que dice el título. Bloqueado hasta tener el material oficial de abajo |

Edición con `cliente/scripts/procesar-hero.sh`: misma gradación en los dos clips, sin audio, 9 a 9,4 s, fundido de entrada y salida para el loop. Segunda codificación (la primera se veía pixelada): desde la fuente de mayor resolución (4K en id-86252 e id-235032; id-159674 solo existe en 1080p y no se agranda), bajada a 1920 px con lanczos, H.264 con CRF 21 y preset slow (tope 4,5 Mbps) y VP9. Pesos tras la recompresión final (CRF 29, tope 2,2 Mbps; WebM CRF 37 a 41): escritorio menos de 2,2 MB en todos los archivos; vertical 720 px con CRF 23: 1,2 y 1,5 MB.

Comprobado en Chrome a 375 y 1440 px: reproduce solo el clip de la diapositiva activa, usa la versión vertical en celular y no hay errores de consola.

Candidatos revisados y descartados: id-41477 (primer plano de metal sin contexto), id-88220 (nave industrial sin motores), id-148834 (escena urbana; el extractor casi no se ve), id-197209 (bodega con cajas que muestran marcas).

## Material oficial solicitado

Pedir a WEG / WEG Cestari autorización escrita y material oficial:
- Motor industrial **W22 de 100 HP**: tomas generales y de detalle, sin retoques.
- **Motorreductor Cestari**: tomas generales y de detalle.
- Formato: video 4K o 1080p horizontal, 10 a 15 s por toma, más fotos en alta resolución.
- Autorización escrita para usarlos en la tienda en línea de Imagen Eléctrica.

Mientras no llegue, la diapositiva 1 del hero no muestra imagen de equipos. El nombre WEG aparece solo en el texto.

## Imágenes existentes

| Archivo | Origen | Licencia | Uso | Estado |
|---|---|---|---|---|
| `img/fachada.webp` | Foto de la fachada de la empresa | Propia | Nosotros | Usar |
| `img/logo.png`, `img/logo-blanco.png` | Logo de la empresa | Propia | Encabezado, pie | Usar |
| `img/categorias/*.webp` (8 líneas) | Composiciones hechas en Canva con renders | Por verificar; aspecto sintético | Mosaico de categorías y tarjetas sin foto | Provisional: reemplazar por fotos reales |

## Edición de los videos del hero

Con los clips ya descargados en `cliente/media-fuente/videos/`:

```bash
cd cliente
scripts/procesar-hero.sh media-fuente/videos/<clip>.mp4 hero-d1 <segundo_inicio> <x_sujeto_0a1>
```

Genera en `public/media/`: `hero-dN.mp4/.webm` (16:9, 1920 px, menos de 3 MB), `hero-dN-m.mp4/.webm` (4:5, 720 px, menos de 1,5 MB), `hero-dN-poster.webp`, sin audio y con fundido para el loop. Después se agrega `media` a la diapositiva en `src/components/home/Hero.jsx` (el ejemplo está en el comentario del archivo). Probado con un clip de prueba sintético: los tamaños salen dentro de los límites.

## Fotos nuevas (Pixabay)

Licencia: [Pixabay Content License](https://pixabay.com/service/license-summary/). Descargadas con la API oficial en `largeImageURL` (1280 px) a `cliente/media-fuente/fotos/` (fuera de git) y convertidas a WebP (calidad 80) en 1280 y 640 px: la fuente solo llega a 1280 px, por eso no hay versión de 1600 px.

| Uso | pageURL | Autor | Archivo(s) | Estado |
|---|---|---|---|---|
| Aplicación: bombeo | https://pixabay.com/photos/machine-tubes-blue-pipes-factory-495376/ | Martinelle | `img/aplicaciones/bombeo*.webp` | Usar |
| Aplicación: ventilación y extracción | https://pixabay.com/photos/fan-air-condition-cooling-7662133/ | Wounds_and_Cracks | `img/aplicaciones/ventilacion*.webp` | Usar (extractor axial industrial en primer plano; recortado a 1070 x 720 desde la fuente de 1280 x 720) |
| Aplicación: bandas transportadoras | https://pixabay.com/photos/industry-metal-stainless-steel-2147405/ | Bru-nO | `img/aplicaciones/bandas*.webp` | Usar |
| Aplicación: tableros y automatización | https://pixabay.com/photos/electrician-electric-electricity-2755683/ | jarmoluk | `img/aplicaciones/tableros*.webp` | Usar (persona de perfil con casco) |
| Aplicación: energía solar | https://pixabay.com/photos/solar-energy-solar-panels-5622969/ | fietzfotos | `img/aplicaciones/solar*.webp` | Usar |
| Aplicación: iluminación industrial | **Sin foto** | | | Pixabay solo tiene bombillas genéricas y naves abandonadas. La tarjeta usa el ícono de marca |
| Crear cuenta e Ingresar | https://pixabay.com/photos/electrical-data-center-2476782/ | Akela999 | `img/fotos/crear-cuenta*.webp` | Usar |
| Contacto | https://pixabay.com/photos/electric-wiring-electrics-electro-4198293/ | image4you | `img/fotos/contacto*.webp` | Usar |
| Categoría energía solar | https://pixabay.com/photos/solar-system-roof-power-generation-2939560/ | ulleo | `img/categorias/energia-solar*.webp` | Usar (lleva arco naranja y curva violeta por CSS) |
| Categoría compresores verticales | **Sin foto** | | | Pixabay solo tiene compresores oxidados, de otra marca visible o portátiles. Sigue la imagen provisional |

### Banners (pendientes de descarga manual)

1280 px no alcanza para un banner de ancho completo. Descarga el original (botón "Descargar" de cada página, tamaño más grande) y déjalo en `cliente/media-fuente/fotos/`:

| Banner | pageURL | Nota |
|---|---|---|
| Control y automatización | https://pixabay.com/photos/electrician-electric-electricity-1080586/ (3246 x 2160) o https://pixabay.com/photos/electrician-electric-electricity-2755682/ (4096 x 2726) | Electricista de espaldas frente a un tablero |
| Ventilación | https://pixabay.com/photos/fan-cooling-propeller-power-plant-3645379/ (4008 x 2696) | Misma foto de la tarjeta de aplicación |
| Motores | **Sin foto** | Esperar el material oficial de WEG (motor W22 de 100 HP) |

## Referencias de Canva (no exportadas)

Los 8 IDs (`MAHaGh7FbRU`, `MAHaGiOlDIY`, `MAHaGtwdAeU`, `MAHaGn92TWY`, `MAHaGn3mqg4`, `MAHaGt6r7Os`, `MAHaGs7tWxE`, `MAHaGrCbDas`) son recursos de imagen, no diseños. El conector de Canva solo exporta diseños (IDs que empiezan con `D`) y para los recursos solo entrega miniaturas de 200 px, que no sirven como fuente. Para tenerlos en alta resolución hay que descargarlos a mano desde Canva (Subidos > imagen > Descargar) o darme el ID del diseño que los contiene.

Con las miniaturas se confirma que son composiciones generadas (técnico con casco junto a bombas, compresor vertical y paneles solares sobre círculo lavanda): no se usan como foto real.

## Imágenes con aspecto sintético en la tienda

Las 8 imágenes de `img/categorias/` (excepto `energia-solar`) siguen siendo las composiciones de Canva. Reemplazarlas por fotos reales cuando haya material.
