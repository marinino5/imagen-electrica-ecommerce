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
| Motores (video) / Automatización y control (video) | https://pixabay.com/videos/id-41477/ y https://pixabay.com/videos/id-159674/ | DmitrySteshenko / CreativeCommonsCentral | `hero-v1*`, `hero-v3*` | Ver `docs/creditos-media.md` |
| Ventilación | **Sin video.** Se eliminó el clip anterior (id-235032) | | | Diseño gráfico de marca (arcos y ventilador). No hay extractores industriales en primer plano con licencia en Coverr ni en Pixabay: ver `docs/opciones-video/` |
| Motores | **Sin video.** Diseño gráfico de marca | | | Sin motores industriales reales en Coverr ni en Pixabay (solo renders de pistones). Material oficial pendiente, ver abajo |

Edición con `cliente/scripts/procesar-hero.sh`: misma gradación en los dos clips, sin audio, 9 a 9,4 s, fundido de entrada y salida para el loop. Segunda codificación (la primera se veía pixelada): desde la fuente de mayor resolución (4K en id-86252 e id-235032; id-159674 solo existe en 1080p y no se agranda), bajada a 1920 px con lanczos, H.264 con CRF 21 y preset slow (tope 4,5 Mbps) y VP9. Pesos finales (el script ya trae CRF 29, tope 2,2 Mbps y WebM CRF 38): escritorio 1,4 MB (MP4) y 1,6 MB (WebM); vertical 720 px, 1,3 MB (MP4) y 0,9 MB (WebM).

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
