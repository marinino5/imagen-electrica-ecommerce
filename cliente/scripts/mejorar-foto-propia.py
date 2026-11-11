"""Mejora las fotos propias de la tienda (celular): recorte, balance de blancos suave, contraste, nitidez y exportación WebP.

Uso: python3 mejorar-foto-propia.py <origen.jpg> <destino-sin-extension> --crop x0,y0,x1,y1 (fracciones 0-1) [--ancho 2000] [--giro grados]
Genera <destino>.webp (ancho indicado) y <destino>-sm.webp (800 px). Natural, sin filtros exagerados: gris-mundo al 55 %, saturación 0,94, contraste 1,06.
"""
import argparse
import numpy as np
from PIL import Image, ImageEnhance, ImageFilter, ImageOps

ap = argparse.ArgumentParser()
ap.add_argument('src'); ap.add_argument('dst')
ap.add_argument('--crop', default='0,0,1,1'); ap.add_argument('--ancho', type=int, default=2000); ap.add_argument('--giro', type=float, default=0)
a = ap.parse_args()
im = ImageOps.exif_transpose(Image.open(a.src)).convert('RGB')
if a.giro:
    im = im.rotate(a.giro, resample=Image.BICUBIC, expand=False)
w, h = im.size
x0, y0, x1, y1 = (float(v) for v in a.crop.split(','))
im = im.crop((int(x0 * w), int(y0 * h), int(x1 * w), int(y1 * h)))
# balance de blancos: gris-mundo suave (el piso azul y el techo dejan la foto fría)
arr = np.asarray(im).astype(np.float32) / 255
mean = arr.reshape(-1, 3).mean(0)
gain = 1 + 0.55 * (mean.mean() / mean - 1)
arr = np.clip(arr * gain, 0, 1)
arr = arr ** 0.95  # levanta apenas las sombras
im = Image.fromarray((arr * 255).astype('uint8'))
im = ImageEnhance.Contrast(im).enhance(1.06)
im = ImageEnhance.Color(im).enhance(0.94)
W = a.ancho
im = im.resize((W, round(im.height * W / im.width)), Image.LANCZOS)
im = im.filter(ImageFilter.UnsharpMask(radius=1.6, percent=55, threshold=3))
im.save(a.dst + '.webp', 'WEBP', quality=84, method=6)
sm = im.resize((800, round(im.height * 800 / im.width)), Image.LANCZOS)
sm.save(a.dst + '-sm.webp', 'WEBP', quality=82, method=6)
print(a.dst, im.size)
