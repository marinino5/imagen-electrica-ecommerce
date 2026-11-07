"""Recorta y normaliza una foto oficial de producto al formato único de la tienda.

Uso (necesita Pillow y rembg: pip install rembg pillow numpy):
    python3 normalizar-producto.py <origen> <destino-sin-extension> [--sin-recorte]

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
    global _session
    from rembg import remove, new_session
    if _session is None:
        _session = new_session('u2net')
    return remove(im.convert('RGB'), session=_session)


def limpiar_borde(im):
    """Quita el halo blanco: encoge el alfa 1 px y suaviza el borde."""
    r, g, b, a = im.split()
    a = a.filter(ImageFilter.MinFilter(3)).filter(ImageFilter.GaussianBlur(0.8))
    return Image.merge('RGBA', (r, g, b, a))


def normalizar(im, con_recorte=True):
    im = recortar(im) if con_recorte else im.convert('RGBA')
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
    guardar(normalizar(Image.open(origen), '--sin-recorte' not in sys.argv), destino)
