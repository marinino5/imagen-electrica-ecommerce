"""Compone la portada de una familia con varios productos ya recortados (formato único de la tienda) y la normaliza.

Uso: python3 componer-portada.py <destino-sin-extension> <producto1> <producto2> ... [--alto=1,0.9,...]
  Cada producto es el nombre de un archivo de public/img/catalogo/ (sin .webp). Se recorta cada uno a su silueta (sin la sombra de
  contacto), se escala a la misma altura relativa, se acomoda de izquierda a derecha con un pequeño solape y el conjunto pasa por
  normalizar-producto.py (--sin-recorte): 1000 × 1000, 78 % del lienzo, misma línea base y misma sombra que las demás portadas.
"""
import sys
from pathlib import Path
from PIL import Image
sys.path.insert(0, str(Path(__file__).parent))
from normalizar_producto_mod import normalizar, guardar  # noqa: E402

CAT = Path(__file__).resolve().parents[1] / 'public' / 'img' / 'catalogo'

destino, *args = sys.argv[1:]
nombres = [a for a in args if not a.startswith('--')]
alturas = next((list(map(float, a.split('=')[1].split(','))) for a in args if a.startswith('--alto=')), [1.0] * len(nombres))
piezas = []
for n, rel in zip(nombres, alturas):
    im = Image.open(CAT / f'{n}.webp').convert('RGBA')
    # silueta del producto: alfa fuerte (la sombra de contacto es suave y queda fuera)
    solido = im.split()[3].point(lambda v: 255 if v > 200 else 0)
    box = solido.getbbox()
    im = im.crop(box)
    piezas.append((im, rel))
# Disposición «pirámide»: los dos primeros al fondo (uno junto al otro) y el tercero al frente, centrado y apoyado en la base.
# Queda casi cuadrada, como las demás portadas, en vez de una tira larga.
H = 640
esc = []
for im, rel in piezas:
    h = int(H * rel)
    w = max(1, round(im.width * h / im.height))
    esc.append(im.resize((w, h), Image.LANCZOS))
if len(esc) != 3:
    raise SystemExit('Se esperan exactamente 3 productos')
a, b, c = esc
solape = 30
ancho_fondo = a.width + b.width - solape
W = max(ancho_fondo, c.width) + 20
alto_total = max(a.height, b.height) + int(c.height * 0.45) + 20
lienzo = Image.new('RGBA', (W, alto_total), (0, 0, 0, 0))
x0 = (W - ancho_fondo) // 2
base_fondo = alto_total - int(c.height * 0.45)  # el fondo termina a mitad de altura del producto del frente
lienzo.alpha_composite(a, (x0, base_fondo - a.height))
lienzo.alpha_composite(b, (x0 + a.width - solape, base_fondo - b.height))
lienzo.alpha_composite(c, ((W - c.width) // 2, alto_total - 10 - c.height))
guardar(normalizar(lienzo, con_recorte=False), destino)
print(destino, lienzo.size)
