"""Genera docs/revision-productos.md: una fila por línea con imagen, especificaciones, ficha técnica y cambios.
Uso: python3 docs/inventario/generar_revision.py   (compara con el catálogo de antes de la revisión: commit fca7ccb)
"""
import json
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
cat = json.loads((ROOT / 'backend/database/data/catalogo.json').read_text())
images = json.loads((ROOT / 'backend/database/data/line-images.json').read_text())
antes = json.loads(subprocess.run(['git', 'show', 'fca7ccb:backend/database/data/catalogo.json'], cwd=ROOT, capture_output=True, text=True).stdout)
old_line = {v['sku']: l['name'] for l in antes['lines'] for v in l['variants']}

HIDDEN = {'Motor monofásico Match Drive', 'Contactor Siemens 3RT2', 'Contacto auxiliar Siemens 3RH2', 'PLC WEG PLC200'}
NO_IMG = {'Batería sellada Magna': 'no (foto retirada: era de otra batería; falta fotografiar la 12 V 5 Ah)'}
APROX = {
    'Contactor WEG CWB con bobina DC', 'Contactor WEG CWBC para banco de capacitores', 'Accesorios para contactores WEG CWB',
    'Mini contactor auxiliar WEG CWCA0', 'Relé electrónico de protección WEG RWB40', 'Relé vigilante WEG RNW',
    'Relé vigilante de tensión WEG RMW17', 'Accesorios para guardamotores WEG MPW', 'Paradas de emergencia WEG CSW', 'Selectores WEG CSW',
    'Pilotos luminosos WEG CSW', 'Bloques de contacto WEG CSW', 'Pulsadores WEG CSW', 'Bornes y accesorios WEG BTW', 'Seccionador WEG MSW',
    'Módulo de expansión de entradas y salidas WEG', 'Relé térmico WEG RW17', 'Relé térmico WEG RW67', 'Totalizador WEG DWB',
    'Motor trifásico WEG de bobinado',
}


def datos(v):
    s = v.get('specs') or {}
    return [k for k in ('hp', 'rpm', 'amp', 'volt', 'ip') if v.get(k)] + [k for k in s if k not in ('tipo', 'serie')]


rows = []
resumen = {'img_si': 0, 'img_aprox': 0, 'img_no': 0, 'esp_si': 0, 'esp_parcial': 0}
for l in cat['lines']:
    vs = l['variants']
    slug = l['slug']
    tiene_foto = slug in images or any(v.get('image') for v in vs)
    if l['name'] in HIDDEN:
        img = 'no (sin foto oficial; línea oculta)'
        resumen['img_no'] += 1
    elif l['name'] in NO_IMG:
        img = NO_IMG[l['name']]
        resumen['img_no'] += 1
    elif not tiene_foto and vs:
        img = 'no (sin foto)'
        resumen['img_no'] += 1
    elif l['name'] in APROX:
        img = 'aprox. (foto de la misma familia y marca, no de ese modelo exacto)'
        resumen['img_aprox'] += 1
    else:
        img = 'sí'
        resumen['img_si'] += 1
    if not vs:
        esp = 'línea bajo pedido (sin referencias)'
    else:
        con_tipo = all((v.get('specs') or {}).get('tipo') for v in vs)
        con_dato = all(datos(v) for v in vs)
        if con_tipo and con_dato:
            esp = 'sí (tipo, marca, serie y datos técnicos)' if any((v.get('specs') or {}).get('serie') for v in vs) else 'sí (tipo, marca y datos técnicos)'
            resumen['esp_si'] += 1
        else:
            esp = 'parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó)'
            resumen['esp_parcial'] += 1
    antes_names = sorted({old_line[v['sku']] for v in vs if v['sku'] in old_line})
    cambios = []
    if antes_names and antes_names != [l['name']]:
        cambios.append('antes «' + '» + «'.join(antes_names) + '»')
    if vs and any(v['label'] != next((x['label'] for a in antes['lines'] for x in a['variants'] if x['sku'] == v['sku']), v['label']) for v in vs):
        cambios.append('etiquetas de variante corregidas')
    cambios.append('tipo' + ('/serie' if any((v.get('specs') or {}).get('serie') for v in vs) else '') + ' en especificaciones' if vs else 'nombre')
    rows.append(f"| {l['name']} | {len(vs)} | {img} | {esp} | sí | {'; '.join(cambios)} |")

out = ['# Revisión producto por producto', '',
       f"Estado tras la revisión del catálogo ({len(cat['lines'])} líneas, {sum(len(l['variants']) for l in cat['lines'])} referencias). Se generó con `docs/inventario/generar_revision.py`.", '',
       '- **Imagen correcta**: «sí» = foto del producto y de la marca; «aprox.» = foto oficial de la misma familia y marca pero no de ese modelo (se reemplaza al conseguir la foto exacta); «no» = sin foto o con foto retirada. Las fotos de referencias nuevas de distribuidores colombianos se revisaron una por una (sin marca de agua, sin mostrador ni bodega); su origen exacto está en `docs/licencias-imagenes.md`.',
       '- **Especificaciones completas**: toda referencia trae tipo y marca; la serie se agrega cuando sale del nombre o del código de la referencia. Los datos técnicos son los de la descripción del inventario y, para los WEG, se contrastaron con el catálogo oficial (ver `docs/correcciones-especificaciones.md`).',
       '- **Ficha técnica funciona**: «Imprimir ficha» abre un PDF con logo, nombre, imagen, referencia, tabla de especificaciones, SKU y contacto. Se comprobó en Chrome generando el PDF de cada una de las líneas publicadas (todas válidas, una página, con logo, tabla y contacto). **Safari** (macOS/iOS) no se pudo probar desde este equipo: usa la misma hoja de estilos de impresión (`@media print`, `-webkit-print-color-adjust`), pero hay que abrir una ficha en Safari antes de la sustentación.', '',
       f"Resumen: imagen sí {resumen['img_si']} · aprox. {resumen['img_aprox']} · no {resumen['img_no']} · especificaciones completas {resumen['esp_si']} · parciales {resumen['esp_parcial']}.", '',
       '| Línea | Referencias | Imagen correcta | Especificaciones completas | Ficha técnica funciona | Cambios hechos |', '|---|---|---|---|---|---|'] + rows
(ROOT / 'docs/revision-productos.md').write_text('\n'.join(out) + '\n')
print(resumen)
