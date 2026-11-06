# Créditos de media: fuente y licencia

Todo el material de terceros que se usa es de **Pixabay** y se usa bajo la [Pixabay Content License](https://pixabay.com/service/license-summary/): uso comercial permitido, sin atribución obligatoria, sin dar a entender que el autor o una marca respalda a la empresa. Se descargó con la API oficial. Detalle de edición y descartados en `docs/recursos.md`.

## Videos del hero (`cliente/public/media/`)

El video tiene versión de escritorio (16:9, 1920 px) y vertical (4:5, 720 px), en MP4 y WebM, más póster WebP. Cada archivo pesa menos de 3 MB.

| Diapositiva | Archivos | Fuente | Autor | Licencia |
|---|---|---|---|---|
| Automatización y control | `hero-d2*` | https://pixabay.com/videos/id-86252/ (segundos 1 a 10, cableado de control y tablero). Se quitó el tramo del técnico de espaldas (id-159674) y el fundido borroso entre los dos clips | lumosajans | Pixabay Content License |
| Motores WEG y motorreductores Cestari | Sin video: diseño gráfico de marca (arcos naranja e icono) | No hay material adecuado con licencia (ver `docs/imagenes-pendientes.md`) | | |
| Extractores e iluminación | Sin video: diseño gráfico de marca. Se eliminó el clip anterior (`hero-d3*`, id-235032) | No hay material adecuado con licencia (ver `docs/imagenes-pendientes.md`) | | |

## Coverr (revisado, sin material usado)

Se consultó la API de Coverr con la clave de `cliente/.env`. **La variable se llama `COVERR_API_BASE_URL` pero contiene la clave**; conviene renombrarla a `COVERR_API_KEY` en tu `.env` (no se versiona).

- **Licencia:** la respuesta de la API no trae un campo de licencia; solo `is_premium: false` (video gratuito). La licencia aplicable es la de https://coverr.co/license, que permite el uso comercial sin pedir permiso ni exigir atribución en descargas del sitio, y excluye la reventa o redistribución de los archivos y los logos o marcas que aparezcan en la imagen.
- **Resultado:** el catálogo gratuito de Coverr no tiene extractores industriales, motores eléctricos, tableros eléctricos ni fotos de mostrador de ferretería (solo hay videos; el endpoint de fotos responde 404). Por eso **no se descargó ni se usó ningún archivo de Coverr**. Consultas y resultados en `docs/imagenes-pendientes.md`.

## Fotos (`cliente/public/img/`)

| Uso | Fuente | Autor | Licencia |
|---|---|---|---|
| Aplicación: ventilación y extracción (`aplicaciones/ventilacion*`) | https://pixabay.com/photos/fan-air-condition-cooling-7662133/ | Wounds_and_Cracks | Pixabay Content License |
| Aplicación: bombeo | https://pixabay.com/photos/machine-tubes-blue-pipes-factory-495376/ | Martinelle | Pixabay Content License |
| Aplicación: bandas transportadoras | https://pixabay.com/photos/industry-metal-stainless-steel-2147405/ | Bru-nO | Pixabay Content License |
| Aplicación: tableros | https://pixabay.com/photos/electrician-electric-electricity-2755683/ | jarmoluk | Pixabay Content License |
| Aplicación: energía solar | https://pixabay.com/photos/solar-energy-solar-panels-5622969/ | fietzfotos | Pixabay Content License |
| Crear cuenta e Ingresar | https://pixabay.com/photos/electrical-data-center-2476782/ | Akela999 | Pixabay Content License |
| Contacto | https://pixabay.com/photos/electric-wiring-electrics-electro-4198293/ | image4you | Pixabay Content License |
| Categoría energía solar | https://pixabay.com/photos/solar-system-roof-power-generation-2939560/ | ulleo | Pixabay Content License |
| Fachada, logos | Propias de la empresa | | Propia |

Las imágenes de `img/categorias/` (excepto energía solar) son composiciones provisionales hechas en Canva: no son foto real y se reemplazan en la Fase 3.
