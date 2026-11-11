#!/usr/bin/env python3
"""Fase 1B: agrega al catálogo las referencias «Listo» de inventario-productos-nuevos.xlsx.

Uso (desde la raíz del repositorio):  python3 docs/inventario/generar_fase1.py
Lee:
  docs/inventario/filas-nuevas.json    las filas de la hoja «Productos nuevos» (se genera con extraer_excel.py)
  docs/inventario/nombres_fase1.py     nombre, etiqueta y especificaciones de cada referencia
  docs/inventario/imagenes_fase1.json  SKU → foto recortada (archivo, fuente, tipo). Sin foto la referencia NO se publica
  backend/database/data/catalogo.base.json  el catálogo de las 351 referencias anteriores (no se modifica)
Escribe:
  backend/database/data/catalogo.json  base + las líneas nuevas (idempotente: siempre parte de catalogo.base.json)
  backend/database/data/line-images.json  foto de cada línea nueva
  docs/inventario/pendientes-empresa.md  lo que la empresa debe completar
  docs/inventario/precios-pendientes.csv  una fila por referencia nueva, con la columna de precio vacía
Reglas: todas las líneas nuevas se venden por cotización (sin precio) hasta que lleguen los precios; nada de SKU provisionales.
"""
import csv, json, os, re, sys, collections
ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..'))
sys.path.insert(0, os.path.dirname(__file__))
from nombres_fase1 import N

INV = os.path.join(ROOT, 'docs', 'inventario')
DATA = os.path.join(ROOT, 'backend', 'database', 'data')
rows = json.load(open(os.path.join(INV, 'filas-nuevas.json'), encoding='utf8'))
base = json.load(open(os.path.join(DATA, 'catalogo.base.json'), encoding='utf8'))
imgs = json.load(open(os.path.join(INV, 'imagenes_fase1.json'), encoding='utf8')) if os.path.exists(os.path.join(INV, 'imagenes_fase1.json')) else {}
line_imgs = json.load(open(os.path.join(DATA, 'line-images.base.json'), encoding='utf8'))

# Línea del Excel → (nombre en la tienda, ¿ya existe en el catálogo?)
LINE_NAME = {
    'Pilotos y pulsadores (otras marcas)': 'Pulsadores y pilotos de otras marcas',
    'Reductor helicoidal WCG20 (variantes en stock)': 'Reductor helicoidal WCG20',
    'Motorreductor sinfín WCG01 (variantes en stock)': 'Motorreductor sinfín WCG01',
}
FAMILY_IMAGE = {'Material Eléctrico': 'material-electrico', 'Herramientas y Repuestos': 'otros'}
NEW_FAMILIES = [
    {'name': 'Material Eléctrico', 'image': 'material-electrico', 'description': 'Tomas, clavijas, cintas aislantes y puesta a tierra para instalaciones eléctricas.'},
    {'name': 'Herramientas y Repuestos', 'image': 'otros', 'description': 'Insumos, herramientas y repuestos que no son material eléctrico.'},
]

def sku_of(x): return str(x['Código (SKU)']).strip()
def brand_of(x):
    b = str(x['Marca']).strip()
    return {'Weg': 'WEG', 'Abb': 'ABB', 'Vcp': 'VCP', 'Spk': 'SPK', 'Ebchq': 'EBCHQ', 'Thck': 'THCK', 'Ihm': 'IHM', 'Kap': 'KAP', 'Epk': 'EPK', 'Sommv': 'SOMMV', 'Tmcon': 'TMCON', 'Wattio': 'Wattio', 'Margirius': 'Margirius', 'Phillips': 'Philips'}.get(b, b)

# Stock de las 12 referencias de la hoja «Stock distinto»: el Excel manda (solo si el catálogo base aún trae el valor de la tienda)
stock_fix = json.load(open(os.path.join(INV, 'stock-distinto.json'), encoding='utf8'))
by_sku = {v['sku']: v for l in base['lines'] for v in l['variants']}
for f in stock_fix:
    v = by_sku[f['sku']]
    assert v['stock'] in (f['stock_tienda'], f['stock_excel']), (f['sku'], v['stock'])
    v['stock'] = f['stock_excel']

existing_skus = {v['sku'] for l in base['lines'] for v in l['variants']}
listo = [x for x in rows if x['Estado'] == 'Listo']
pend = [x for x in rows if x['Estado'] != 'Listo']
assert len(listo) == 225 and len(pend) == 67, (len(listo), len(pend))
clash = [sku_of(x) for x in listo if sku_of(x) in existing_skus]
assert not clash, f'SKU ya existentes en el catálogo: {clash}'

def fmt_num(v): return str(v).replace('.', ',')
def chips(variants):
    out = []
    for key, unit in (('potencia_w', 'W'), ('corriente_a', 'A'), ('potencia_hp', 'HP')):
        vals = [v['specs'][key] for v in variants if key in v['specs']]
        if len(vals) == len(variants) and vals:
            lo, hi = min(vals), max(vals)
            out.append(f'{fmt_num(lo)} {unit}' if lo == hi else f'{fmt_num(lo)}–{fmt_num(hi)} {unit}')
    return out[:2]

def variant(x, no_image):
    sku = sku_of(x)
    name, label, specs = N[sku]
    specs = dict(specs)
    core = {k: specs.pop(k, None) for k in ('potencia_hp', 'velocidad_rpm', 'corriente_a', 'voltaje_v', 'proteccion_ip')}
    stock = int(x['Cantidad'])
    v = {'sku': sku, 'label': label, 'name': name, 'brand': brand_of(x), 'stock': stock,
         'hp': core['potencia_hp'], 'rpm': core['velocidad_rpm'], 'amp': core['corriente_a'], 'volt': core['voltaje_v'], 'ip': core['proteccion_ip'],
         'mode': 'quote', 'price': None, 'specs': specs}
    return v

groups = collections.OrderedDict()
published, unpublished = [], []
for x in listo:
    sku = sku_of(x)
    img = imgs.get(sku)
    if not img or not img.get('file'):
        unpublished.append(x)
        continue
    published.append(x)
    key = (x['Familia'], x['Categoría'], LINE_NAME.get(x['Línea propuesta'], x['Línea propuesta']))
    v = variant(x, False)
    v['image'] = '/img/catalogo/' + img['file'] + '.webp'
    groups.setdefault(key, []).append(v)

# Etiquetas repetidas dentro de una línea: se distinguen con la marca (o con el SKU si la marca también se repite)
for vs in groups.values():
    cnt = collections.Counter(v['label'] for v in vs)
    for v in vs:
        if cnt[v['label']] > 1:
            cand = f"{v['label']} · {v['brand']}" if v['brand'] != 'Sin marca' else f"{v['label']} · {v['sku']}"
            v['label'] = cand
    cnt = collections.Counter(v['label'] for v in vs)
    for v in vs:
        if cnt[v['label']] > 1:
            v['label'] = f"{v['label']} · {v['sku']}"

existing_lines = {l['name']: l for l in base['lines']}
lines, line_image_map = [], dict(line_imgs)
slug = lambda s: re.sub(r'-+', '-', re.sub(r'[^a-z0-9]+', '-', __import__('unicodedata').normalize('NFKD', s.lower()).encode('ascii', 'ignore').decode())).strip('-')
new_lines = []
for (family, category, name), vs in groups.items():
    brands = [b for b, _ in collections.Counter(v['brand'] for v in vs).most_common() if b != 'Sin marca']
    if name in existing_lines:
        # Las referencias nuevas entran como variantes de la línea que ya existe (WCG01, WCG20)
        row = existing_lines[name]
        row['variants'] = row['variants'] + vs
        row['mode'] = 'quote' if not any(v['mode'] == 'direct' for v in row['variants']) else 'direct'
        continue
    line = {'family': family, 'category': category, 'name': name, 'brand_label': ' · '.join(sorted(brands)) if brands else None,
            'image': FAMILY_IMAGE.get(family, 'nuevo'), 'specs': chips(vs), 'mode': 'quote', 'variants': vs}
    new_lines.append(line)
    line_image_map[slug(name)] = vs[0]['image']

base['families'] = base['families'] + [f for f in NEW_FAMILIES if f['name'] not in {x['name'] for x in base['families']}]
base['lines'] = base['lines'] + new_lines
# «Medición y testeo» ya existe en el catálogo base: las líneas nuevas van a la familia por su nombre
json.dump(base, open(os.path.join(DATA, 'catalogo.json'), 'w', encoding='utf8'), indent=1, ensure_ascii=False)
json.dump(line_image_map, open(os.path.join(DATA, 'line-images.json'), 'w', encoding='utf8'), indent=2, ensure_ascii=False)
open(os.path.join(DATA, 'line-images.json'), 'a').write('\n')

# ---- docs/imagenes-referencias.csv: una fila por referencia nueva (se reemplazan las de corridas anteriores)
KIND_TXT = {'oficial': 'con imagen oficial recortada (sitio o catálogo de la marca)', 'distribuidor-co': 'con foto de distribuidor colombiano recortada'}
csv_path = os.path.join(ROOT, 'docs', 'imagenes-referencias.csv')
new_skus = {v['sku'] for vs in groups.values() for v in vs}
with open(csv_path, encoding='utf8', newline='') as f:
    old_rows = list(csv.reader(f))
header, body = old_rows[0], [r for r in old_rows[1:] if r and r[0] not in new_skus]
fotos = collections.Counter(imgs[s]['file'] for s in new_skus)
sku_line = {v['sku']: n for (_, _, n), vs in groups.items() for v in vs}
for sku in sorted(new_skus, key=lambda k: (sku_line[k], k)):
    i = imgs[sku]
    own = 'propia de la referencia' if fotos[i['file']] == 1 else 'compartida con otras referencias del mismo modelo'
    body.append([sku, sku_line[sku], i.get('source') or 'foto oficial de la línea ya publicada', '/img/catalogo/' + i['file'] + '.webp', own, KIND_TXT.get(i['kind'], i['kind'])])
with open(csv_path, 'w', encoding='utf8', newline='') as f:
    w = csv.writer(f); w.writerow(header); w.writerows(body)

# ---- Precios pendientes (para cargar con `php artisan catalog:load-prices`)
with open(os.path.join(INV, 'precios-pendientes.csv'), 'w', newline='', encoding='utf8') as f:
    w = csv.writer(f)
    w.writerow(['sku', 'nombre', 'etiqueta', 'marca', 'stock', 'precio_con_iva'])
    for vs in groups.values():
        for v in vs:
            w.writerow([v['sku'], v['name'], v['label'], v['brand'], v['stock'], ''])

# ---- Pendientes de la empresa
def row_md(x, why):
    return f"| {sku_of(x) if x['Código (SKU)'] not in (None, '') else '—'} | {x['Descripción'] or '—'} | {x['Marca']} | {x['Cantidad'] if x['Cantidad'] is not None else '—'} | {why} |"

def why_of(x):
    p = str(x['Problemas de datos'] or '')
    parts = []
    if 'sin código' in p: parts.append('Sin código (SKU)')
    if 'sin descripción' in p: parts.append('Sin descripción')
    if 'descripción incompleta' in p: parts.append('Descripción incompleta (falta tipo y marca)')
    if 'código repetido' in p: parts.append('Código repetido en otra referencia')
    if 'dañado por Excel' in p: parts.append('Código dañado por Excel (aparece como fecha)')
    if 'cantidad decimal' in p: parts.append('Cantidad decimal (4,5): confirmar unidades')
    if 'sin cantidad' in p: parts.append('Sin cantidad')
    if 'la empresa marcó' in p: parts.append('La empresa lo marcó: ' + str(x['Comentario original']).lower())
    return '; '.join(parts) or p

md = ['# Pendientes para la empresa (Fase 1B)', '',
      'Referencias que **no se publicaron** y qué hace falta para publicarlas. Nada se completó con datos adivinados ni con SKU provisionales.', '',
      f'- Filas en la hoja «Productos nuevos»: {len(rows)}. Publicadas: **{len(published)}**. Con estado «Pendiente»: **{len(pend)}**. «Listo» sin publicar por falta de foto: **{len(unpublished)}**.',
      '- **Precios**: ninguna referencia nueva tiene precio; todas salen en «Cotizar». Llenar la columna `precio_con_iva` de `precios-pendientes.csv` y cargarla con `php artisan catalog:load-prices docs/inventario/precios-pendientes.csv` (pasa a compra en línea las que tengan precio y stock).', '',
      '## 1. Estado «Pendiente» (problemas en el Excel)', '', '| SKU | Descripción | Marca | Cantidad | Problema |', '|---|---|---|---|---|']
md += [row_md(x, why_of(x)) for x in pend]
md += ['', '## 2. «Listo» sin foto', '',
       'Referencias con datos completos para las que no apareció una foto del fabricante o de un distribuidor. Las fuentes revisadas están en `docs/inventario/fotos-pendientes.json`.', '',
       '| SKU | Descripción | Marca | Cantidad |', '|---|---|---|---|']
md += [f"| {sku_of(x)} | {x['Descripción']} | {x['Marca']} | {x['Cantidad']} |" for x in unpublished]
def count(key): return sum(1 for x in pend if key in str(x['Problemas de datos'] or ''))
md += ['', '## 3. Resumen: qué necesitamos de la empresa', '',
       f'1. **Precios con IVA** de las {len(published)} referencias publicadas (`precios-pendientes.csv`). Mientras no lleguen, se cotizan.',
       f'2. **Códigos (SKU)**: {count("sin código")} filas «Pendiente» no traen código; {count("código repetido")} comparten código con otra referencia; {count("dañado por Excel")} lo traía convertido en fecha.',
       f'3. **Descripciones**: {count("sin descripción")} filas sin descripción y {count("descripción incompleta")} con descripción incompleta (solo color y voltaje, sin tipo ni marca).',
       f'4. **Revisión de lo que la empresa marcó** («VERIFICAR…», «BUSCAR»): {count("la empresa marcó")} filas.',
       f'5. **Cantidad**: {count("cantidad decimal") + count("sin cantidad")} filas con cantidad decimal o vacía.',
       f'6. **Fotos o fichas**: {len(unpublished)} referencias «Listo» no se publicaron por no encontrar una foto del fabricante ni de un distribuidor colombiano (sección 2).',
       '7. **Dato por confirmar**: el código `TC4Y` (descripción «HOROMETRO DIGITAN AUTONICS») es el de un controlador de temperatura de Autonics, no el de un horómetro; no se publicó hasta confirmar cuál es.']
open(os.path.join(INV, 'pendientes-empresa.md'), 'w', encoding='utf8').write('\n'.join(md) + '\n')
# URLs revisadas por cada «Listo» sin foto (las deja harvest en intentos_fotos.json si existe)
tried_path = os.path.join(INV, 'intentos_fotos.json')
tried = json.load(open(tried_path, encoding='utf8')) if os.path.exists(tried_path) else {}
json.dump({sku_of(x): tried.get(sku_of(x), []) for x in unpublished}, open(os.path.join(INV, 'fotos-pendientes.json'), 'w', encoding='utf8'), ensure_ascii=False, indent=1)
print(f'publicadas {len(published)} · sin foto {len(unpublished)} · pendientes {len(pend)} · líneas nuevas {len(new_lines)}')
