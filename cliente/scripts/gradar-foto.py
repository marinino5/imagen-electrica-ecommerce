"""Tratamiento único de las fotos de ambiente: tono ligeramente frío, contraste medio, saturación contenida.

Uso: python3 gradar-foto.py <origen> <destino.webp> [--ancho 1600] [--aspecto 4:5] [--foco 0.5,0.5]
  --aspecto recorta al formato dado alrededor del punto focal (x,y de 0 a 1); sin él conserva la proporción.
Todas las fotos del sitio pasan por aquí para que se vean de la misma marca.
"""
import argparse
import numpy as np
from PIL import Image

GAIN = np.array([0.972, 1.0, 1.04])  # R, G, B: un punto frío
CONTRAST, SATURATION = 1.07, 0.93


def gradar(im):
    a = np.asarray(im.convert('RGB')).astype(np.float32) / 255
    a = a * GAIN
    a = 0.5 + (a - 0.5) * CONTRAST                     # contraste medio
    lum = (a * [0.299, 0.587, 0.114]).sum(axis=2, keepdims=True)
    a = lum + (a - lum) * SATURATION                   # menos saturación
    sombras = np.clip(1 - lum * 3, 0, 1) * 0.018       # sombras apenas azuladas
    a[..., 2] += sombras[..., 0]
    return Image.fromarray((np.clip(a, 0, 1) * 255).astype(np.uint8))


def recortar(im, aspecto, foco):
    w, h = im.size
    aw, ah = (float(x) for x in aspecto.split(':'))
    objetivo = aw / ah
    if w / h > objetivo:
        nw, nh = round(h * objetivo), h
    else:
        nw, nh = w, round(w / objetivo)
    fx, fy = foco
    x = min(max(round(fx * w - nw / 2), 0), w - nw)
    y = min(max(round(fy * h - nh / 2), 0), h - nh)
    return im.crop((x, y, x + nw, y + nh))


if __name__ == '__main__':
    p = argparse.ArgumentParser()
    p.add_argument('origen'); p.add_argument('destino')
    p.add_argument('--ancho', type=int, default=1600)
    p.add_argument('--aspecto'); p.add_argument('--foco', default='0.5,0.5')
    a = p.parse_args()
    im = Image.open(a.origen).convert('RGB')
    if a.aspecto:
        im = recortar(im, a.aspecto, [float(v) for v in a.foco.split(',')])
    if im.width > a.ancho:
        im = im.resize((a.ancho, round(im.height * a.ancho / im.width)), Image.LANCZOS)
    gradar(im).save(a.destino, 'WEBP', quality=82, method=6)
