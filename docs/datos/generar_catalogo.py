#!/usr/bin/env python3
"""Genera backend/database/data/catalogo.json desde docs/datos/catalogo-lineas.xlsx y PORTAFOLIO_Y_FLUJO.xlsx.

Solo usa la biblioteca estándar (un .xlsx es un zip de XML). Uso, desde la raíz del repositorio:
    python3 docs/datos/generar_catalogo.py
"""
import ast, json, re, sys, zipfile, collections
import xml.etree.ElementTree as ET

NS = {'m': 'http://schemas.openxmlformats.org/spreadsheetml/2006/main', 'r': 'http://schemas.openxmlformats.org/officeDocument/2006/relationships'}


def read(path):
    z = zipfile.ZipFile(path)
    ss = []
    if 'xl/sharedStrings.xml' in z.namelist():
        for si in ET.fromstring(z.read('xl/sharedStrings.xml')).findall('m:si', NS):
            ss.append(''.join(t.text or '' for t in si.iter('{%s}t' % NS['m'])))
    wb = ET.fromstring(z.read('xl/workbook.xml'))
    rels = {r.get('Id'): r.get('Target') for r in ET.fromstring(z.read('xl/_rels/workbook.xml.rels'))}
    out = {}
    for s in wb.find('m:sheets', NS):
        t = rels[s.get('{%s}id' % NS['r'])].lstrip('/')
        t = t if t.startswith('xl/') else 'xl/' + t
        rows = []
        for row in ET.fromstring(z.read(t)).iter('{%s}row' % NS['m']):
            cells = {}
            for c in row.findall('m:c', NS):
                col = re.match(r'[A-Z]+', c.get('r')).group()
                v = c.find('m:v', NS)
                ty = c.get('t')
                if ty == 's' and v is not None:
                    val = ss[int(v.text)]
                elif ty == 'inlineStr':
                    val = ''.join(x.text or '' for x in c.iter('{%s}t' % NS['m']))
                else:
                    val = v.text if v is not None else None
                cells[col] = val
            rows.append(cells)
        out[s.get('name')] = rows
    return out


def num(v):
    if v in (None, ''):
        return None
    f = float(v)
    return int(f) if f == int(f) else f


book = read('docs/datos/catalogo-lineas.xlsx')
hdr = book['Referencias por línea'][0]
refs = [{hdr[k]: v for k, v in r.items() if k in hdr} for r in book['Referencias por línea'][1:]]
fichas = book['Líneas (fichas)']
fh = fichas[0]
lineas = [{fh[k]: v for k, v in r.items() if k in fh} for r in fichas[1:]]

# Imagen de línea (clave de /img/categorias) según la maqueta aprobada
MAQ = {(l['f'], l['c'], l['n']): l['img'] for l in json.load(open('/private/tmp/claude-501/ml.json'))} if len(sys.argv) > 1 else {}

FAMILIES = ['Motores eléctricos', 'Reductores y motorreductores', 'Control y automatización', 'Ventilación y climatización',
            'Cables y conductores', 'Iluminación', 'Compresores', 'Medición y testeo']
FAMILY_IMAGE = {'Medición y testeo': 'medicion', 'Motores eléctricos': 'motores', 'Reductores y motorreductores': 'motorreductores', 'Control y automatización': 'variadores-arrancadores',
                'Ventilación y climatización': 'ventilacion', 'Cables y conductores': 'cables', 'Iluminación': 'iluminacion', 'Compresores': 'compresores'}
DESCRIPTION = {
    'Motores eléctricos': 'Motores WEG para bombas, ventiladores, compresores y máquinas en general. Elige la potencia y la velocidad; el resto lo verás en la placa de datos.',
    'Control y automatización': 'Equipos WEG para arrancar, proteger y controlar tus motores.',
}
LINE_IMAGE = {('Motores eléctricos'): 'motores'}

# SKUs: 3 filas del Excel no traen código y uno se repite. Se les da un código provisional que se reemplaza por el de Siigo.
seen = collections.Counter()
pending = 0
lines = {}
order = []
for r in refs:
    key = (r['familia'], r['categoria'], r['linea'])
    sku = r.get('sku')
    if not sku:
        pending += 1
        sku = f'PEND-{pending:03d}'
    seen[sku] += 1
    if seen[sku] > 1:
        sku = f'{sku}-{seen[sku]}'
    if key not in lines:
        lines[key] = []
        order.append(key)
    lines[key].append({
        'sku': sku, 'label': r['variante'], 'name': r['nombre'], 'brand': r['marca'].strip(),
        'stock': num(r.get('stock')) or 0, 'hp': num(r.get('hp')), 'rpm': num(r.get('rpm')), 'amp': r.get('amp'),
        'volt': num(r.get('volt')), 'ip': r.get('ip'), 'mode': r['modo'], 'price': num(r.get('precio')), 'weight': num(r.get('peso')),
    })

meta = {(l['f'], l['c'], l['n']): l for l in lineas}
out_lines = []
for key in order:
    f, c, n = key
    m = meta[key]
    out_lines.append({
        'family': f, 'category': c, 'name': n, 'brand_label': m['b'], 'image': MAQ.get(key, FAMILY_IMAGE[f]),
        'specs': ast.literal_eval(m['specs']) if m.get('specs') else [], 'mode': 'direct' if m['mode'] == 'on' else 'quote',
        'variants': lines[key],
    })

# 7 líneas del portafolio sin inventario: bajo pedido, se cotizan. Solo con las especificaciones que trae el portafolio.
EXTRA = [
    ('Reductores y motorreductores', 'Sinfín y corona', 'Motorreductor sinfín WCG01', 'WEG Cestari', ['Relación 7,5–100', '0,25–15 HP']),
    ('Reductores y motorreductores', 'Helicoidales', 'Reductor helicoidal WCG20', 'WEG Cestari', ['0,25–75 HP', 'Relación 5–100']),
    ('Control y automatización', 'PLC', 'PLC WEG PLC200 y PLC500', 'WEG', []),
    ('Ventilación y climatización', 'Ventilación industrial', 'Extractores AirMax GL y TTT500', 'EMI · AIRAL', []),
    ('Cables y conductores', 'Conductores para instalaciones', 'THHN / THWN-2 Centelsa', 'Centelsa by Nexans', []),
    ('Iluminación', 'Iluminación industrial', 'START Highbay LED', 'Sylvania', []),
    ('Compresores', 'Compresores de pistón', 'Compresor vertical', None, ['2–15 HP']),
    # Familia 08: sin inventario todavía; la ficha existe para que la familia aparezca en el portafolio y se cotice
    ('Medición y testeo', 'Instrumentos de medición', 'Instrumentos de medición Fluke', 'Fluke', []),
]
for f, c, n, b, specs in EXTRA:
    out_lines.append({'family': f, 'category': c, 'name': n, 'brand_label': b, 'image': FAMILY_IMAGE[f], 'specs': specs, 'mode': 'quote', 'variants': []})

# Presentación: un rango con los dos extremos iguales ("63–63 A") se muestra como un solo valor ("63 A")
def collapse(spec):
    m = re.fullmatch(r'\s*([\d.,]+)\s*[–-]\s*([\d.,]+)(\s*\D.*)?', spec)
    return f'{m.group(1)}{m.group(3) or ""}' if m and m.group(1) == m.group(2) else spec

# Dos referencias con la misma etiqueta y tensiones distintas se diferencian con la tensión: "5.6-8 A · 220 V"
for line in out_lines:
    line['specs'] = [collapse(x) for x in line['specs']]
    groups = collections.defaultdict(list)
    for v in line['variants']:
        groups[v['label']].append(v)
    for label, twins in groups.items():
        volts = [v.get('volt') for v in twins]
        if len(twins) > 1 and all(volts) and len(set(volts)) == len(volts):
            for v in twins:
                v['label'] = f"{label} · {int(v['volt'])} V"

data = {'families': [{'name': f, 'image': FAMILY_IMAGE.get(f), 'description': DESCRIPTION.get(f)} for f in FAMILIES], 'lines': out_lines}
json.dump(data, open('backend/database/data/catalogo.json', 'w'), ensure_ascii=False, indent=1)
total = sum(len(l['variants']) for l in out_lines)
print(len(out_lines), 'líneas,', total, 'referencias,', pending, 'sin SKU (provisional),', sum(1 for v in seen.values() if v > 1), 'SKU repetido')
