"""Recorta y normaliza una foto oficial de producto al formato único de la tienda.

Uso (necesita Pillow, rembg y scipy: pip install "rembg[cpu]" pillow numpy scipy):
    python3 normalizar-producto.py <origen> <destino-sin-extension> [--sin-recorte] [--blanco]
  --huecos: borra el blanco puro encerrado dentro del producto (lo que se ve a través de un ventilador o una rejilla).
  --umbral=N: luminancia desde la que se considera blanco con --blanco (218 por defecto; 205 para reflejos grisáceos).
  --blanco: además del recorte, quita el blanco del fondo y de los reflejos del piso que quedaron pegados al borde
            (solo para productos que NO son blancos: variadores, motores, relés grises o azules).

Formato (catálogo, miniaturas, mega-menú y bento):
  - WebP con fondo transparente, lienzo cuadrado de 1000 x 1000 px (más una versión -sm de 480 px).
  - El producto mide como máximo el 78 % del lienzo, va centrado y apoyado en la misma línea base (y = 880).
  - Sombra de contacto idéntica en todas: elipse suave en el violeta de la marca.
"""
import sys
from PIL import Image, ImageFilter, ImageDraw

CANVAS, FILL, BASELINE = 1000, 0.78, 880
_session = None


def recortar(im):
    """rembg con alpha matting: el borde sale con transparencia gradual, sin dientes de sierra."""
    global _session
    from rembg import remove, new_session
    if _session is None:
        _session = new_session('u2net')
    return remove(
        im.convert('RGB'), session=_session, alpha_matting=True,
        alpha_matting_foreground_threshold=240, alpha_matting_background_threshold=12, alpha_matting_erode_size=8,
    )


def quitar_blanco(im, alcance=70, umbral=218):
    """Borra el blanco (casi sin color) que sigue conectado al fondo, hasta `alcance` px del borde: reflejos del piso y halos."""
    import numpy as np
    from scipy import ndimage as ndi
    a = np.asarray(im).astype(float)
    rgb, alfa = a[..., :3], a[..., 3]
    lum = rgb.mean(-1)
    sat = rgb.max(-1) - rgb.min(-1)
    blanco = (lum > umbral) & (sat < 26)
    fondo = alfa < 40
    cerca = ndi.binary_dilation(fondo, iterations=alcance)
    comp = ndi.binary_propagation(fondo, mask=(blanco | fondo) & cerca)
    alfa = np.where(comp & blanco, 0, alfa)
    return Image.fromarray(np.dstack([rgb, alfa]).astype('uint8'), 'RGBA')


def quitar_huecos(im, minimo=2500):
    """Borra las zonas blancas puras y grandes que quedaron dentro del producto (el fondo que se ve a través de un ventilador, un asa o una rejilla)."""
    import numpy as np
    from scipy import ndimage as ndi
    a = np.asarray(im).astype(float)
    rgb, alfa = a[..., :3], a[..., 3]
    puro = (rgb.min(-1) > 244) & (alfa > 40)
    etiquetas, n = ndi.label(puro)
    tam = ndi.sum(puro, etiquetas, range(1, n + 1))
    grandes = np.isin(etiquetas, [i + 1 for i, t in enumerate(tam) if t >= minimo])
    alfa = np.where(grandes, 0, alfa)
    return Image.fromarray(np.dstack([rgb, alfa]).astype('uint8'), 'RGBA')


def limpiar_borde(im):
    """Quita el halo: erosiona el alfa 1 px y suaviza el borde."""
    r, g, b, a = im.split()
    a = a.filter(ImageFilter.MinFilter(3)).filter(ImageFilter.GaussianBlur(0.8))
    return Image.merge('RGBA', (r, g, b, a))


def normalizar(im, con_recorte=True, blanco=False, huecos=False, umbral=218):
    im = recortar(im) if con_recorte else im.convert('RGBA')
    if blanco:
        im = quitar_blanco(im, umbral=umbral)
    if huecos:
        im = quitar_huecos(im)
    im = limpiar_borde(im)
    box = im.getbbox()
    im = im.crop(box)
    w, h = im.size
    s = CANVAS * FILL / max(w, h)
    im = im.resize((max(1, round(w * s)), max(1, round(h * s))), Image.LANCZOS)
    w, h = im.size
    lienzo = Image.new('RGBA', (CANVAS, CANVAS), (0, 0, 0, 0))
    # Sombra de contacto: elipse del 70 % del ancho del producto (con tope), difuminada, en ink de la marca
    sombra = Image.new('RGBA', (CANVAS, CANVAS), (0, 0, 0, 0))
    sw = min(w * 0.72, CANVAS * 0.7)
    d = ImageDraw.Draw(sombra)
    d.ellipse((CANVAS / 2 - sw / 2, BASELINE - 14, CANVAS / 2 + sw / 2, BASELINE + 18), fill=(38, 28, 62, 105))
    sombra = sombra.filter(ImageFilter.GaussianBlur(14))
    lienzo.alpha_composite(sombra)
    lienzo.alpha_composite(im, ((CANVAS - w) // 2, BASELINE - h))
    return lienzo


def guardar(lienzo, destino):
    lienzo.save(destino + '.webp', 'WEBP', quality=88, method=6)
    lienzo.resize((480, 480), Image.LANCZOS).save(destino + '-sm.webp', 'WEBP', quality=86, method=6)


if __name__ == '__main__':
    origen, destino = sys.argv[1], sys.argv[2]
    guardar(normalizar(Image.open(origen), '--sin-recorte' not in sys.argv, '--blanco' in sys.argv, '--huecos' in sys.argv, int(next((a.split('=')[1] for a in sys.argv if a.startswith('--umbral=')), 218))), destino)
