# Recursos visuales: inventario y licencias

Regla: lo que no tenga derechos comprobados se marca **NO USAR** y no aparece en la tienda.

## Videos del hero

| Archivo | Origen | Licencia | Ubicación | Estado |
|---|---|---|---|---|
| hero-tablero (mp4, webm, poster) | Generado con IA, marca de agua retirada | No comprobada | `cliente/media-fuente/descartados/` (fuera de git) | **NO USAR** |
| hero-motores (mp4, webm, poster) | Generado con IA, marca de agua retirada | No comprobada | `cliente/media-fuente/descartados/` (fuera de git) | **NO USAR** |

Los dos videos anteriores se retiraron del hero y de `cliente/public/media/`.

### Videos nuevos (pendientes)

No hay clips con licencia comprobada todavía. Intento de búsqueda automática:
- Pexels y Pixabay responden 403 a descargas automáticas (exigen API key gratuita o descarga manual).
- Coverr no tiene tomas de motores, tableros ni extractores industriales; solo material genérico o generado con IA.

Para continuar: crear una API key gratuita en https://www.pexels.com/api/ y guardarla en `cliente/.env.local` como `PEXELS_API_KEY` (no se sube a git), o descargar a mano 2 o 3 clips por diapositiva en `cliente/media-fuente/videos/`. Después se editan con ffmpeg (instalado) según las salidas pedidas.

Cada clip debe registrarse aquí con archivo, enlace de origen, licencia y verificación de marcas visibles y personas reconocibles.

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
