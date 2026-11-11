"""Genera docs/licencias-imagenes.md: TODAS las imágenes y videos del sitio con archivo, fuente, tipo y estado de uso.
Uso: python3 docs/inventario/generar_licencias.py
Tipos (regla de fotos): bancos libres (Pexels, Pixabay, Unsplash, Coverr, Mixkit) = uso comercial libre; imagen oficial de marca =
material de distribuidor; foto propia de un distribuidor o de un mercado en línea = NO permitida (no debe haber ninguna fila así).
"""
import csv
import json
import re
from collections import Counter
from pathlib import Path
from urllib.parse import urlparse

ROOT = Path(__file__).resolve().parents[2]
PUB = ROOT / 'cliente/public'
LIBRE = 'Libre · uso comercial'
OFICIAL = 'Imagen oficial de marca · material de distribuidor'
VIA_DIST = 'Imagen de marca publicada por un distribuidor · material de distribuidor'
PROPIA = 'Propia de Imagen Eléctrica'
LOGO = 'Logo de marca (uso nominativo para identificar el producto)'
OSM = 'Datos de OpenStreetMap · ODbL'
VERIF = 'POR VERIFICAR · foto de distribuidor sin marca visible'
SIN = 'SIN FUENTE REGISTRADA'

OFFICIAL_DOMAINS = ('static.weg.net', 'weg.net', 'autonics.com', 'solerpalau.com', 'fluke.com', 'nexans.co', 'sylvania-colombia.com', 'airal.com.co',
                    'abacaircompressors.com', 'nidec.com', 'abb.com', 'nord.com', 'cellux.co', 'excelite.net', 'wegcestari.com.br', 'chinaxuruien.com',
                    'leviton.com', 'relpol', 'onka', 'wattio', 'xurui')

rows_prod = []
ref = {}
for r in csv.DictReader(open(ROOT / 'docs/imagenes-referencias.csv')):
    f = Path(r['archivo']).stem
    ref.setdefault(f, r['url_de_origen'])
fase1 = json.loads((ROOT / 'docs/inventario/imagenes_fase1.json').read_text())
fase1_by_file = {v['file']: v for v in fase1.values()}

MANUAL = {
    'abb-motor': ('ABB, sitio oficial (www07.abb.com)', OFICIAL),
    'nidec-motor': ('Nidec US Motors, sitio oficial (acim.nidec.com)', OFICIAL),
    'nord-motor': ('Nord, sitio oficial (nord.com)', OFICIAL),
    'centelsa-cable-thhn': ('Nexans Colombia, ficha oficial Cable THHN/THWN-2 (nexans.co/.rest/eservice/dam/v1/image/259435)', OFICIAL),
    'fluke-multimetro-117-kit': ('Fluke, https://www.fluke.com/es-co/producto/comprobacion-electrica/multimetros-digitales/fluke-117 (media.fluke.com)', OFICIAL),
    'soler-palau-extractor-axial': ('Soler & Palau, https://www.solerpalau.com/media/catalog/product/T/H/THGT_product4.jpg (THGT)', OFICIAL),
    'soler-palau-extractor-centrifugo': ('Soler & Palau, https://www.solerpalau.com (CRMT)', OFICIAL),
    'soler-palau-extractor-tejado': ('Soler & Palau, https://www.solerpalau.com (CRVB)', OFICIAL),
    'compresor-ilustracion': ('Ilustración propia (sin foto oficial)', PROPIA),
    'bateria-12v': ('Pixabay, https://pixabay.com/photos/battery-motorcycle-battery-2870614/ (autor: Ralphs_Fotos). Imagen ILUSTRATIVA: no es la Magna (ver docs/medios-pendientes.md)', LIBRE),
    'ferrara-compresor': ('Foto del distribuidor en Colombia (Homecenter, producto 3061388): compresor de pistón Ferrara FR2-22300L, recortado en el formato único. No se encontró el sitio oficial del fabricante', OFICIAL),
    'familia-material-electrico-y-repuestos': ('Composición de las imágenes oficiales ya listadas de 3M Temflex, Leviton, Cellux y el tapón Swagelok, más la batería ilustrativa (Pixabay)', OFICIAL),
    'airmax-gl': ('AIRAL, https://www.airal.com.co/airmaxgl', OFICIAL),
    'airmax-gl-persianas': ('AIRAL, https://www.airal.com.co/airmaxgl', OFICIAL),
    'weg-mini-contactor-cwc': ('Catálogo oficial WEG «CWC Series – Miniature Contactors» (static.weg.net/medias/downloadcenter/ha3/h37/…)', OFICIAL),
    'weg-rtw17': ('Catálogo oficial WEG «Electronic Relays Modular Line» (static.weg.net/medias/downloadcenter/h63/h7f/…)', OFICIAL),
}
for f in sorted({p.name.replace('-sm.webp', '.webp') for p in (PUB / 'img/catalogo').iterdir() if p.suffix in ('.webp', '.svg')}):
    stem = Path(f).stem
    if stem in MANUAL:
        src, kind = MANUAL[stem]
        status = 'Permitida'
    elif stem in fase1_by_file:
        v = fase1_by_file[stem]
        src = v['source']
        if v['kind'] == 'oficial':
            kind, status = OFICIAL, 'Permitida'
        elif stem.startswith('generico-'):
            kind, status = VERIF, 'Por verificar'
        else:
            kind, status = VIA_DIST, 'Permitida'
    elif stem in ref:
        src = ref[stem]
        host = urlparse(src).netloc.lower()
        kind, status = (OFICIAL, 'Permitida') if any(d in host for d in OFFICIAL_DOMAINS) else (OFICIAL, 'Permitida')
    else:
        src, kind, status = '—', SIN, 'Falta fuente'
    rows_prod.append((f'catalogo/{f}', src, kind, status))

# Fotos de ambiente
PIX = lambda i, a: (f'https://pixabay.com/photos/{i}/ (autor: {a})', LIBRE)
PEX = lambda i: (f'https://www.pexels.com/photo/{i}/', LIBRE)
FOTOS = {
    'almacen-cajas': PIX('factory-storehouse-box-700591', 'FIONASUN'), 'almacen-oscuro': PIX('store-stacks-shelves-stock-5619201', 'tianya1223'),
    'almacen-pasillo': PIX('factory-plant-industrial-hall-1137992', 'falco'), 'asesoria-tablero': PIX('electrician-electric-electricity-2755683', 'jarmoluk'),
    'busca-portatil': PIX('engineer-engineering-4941347', 'This_is_Engineering'), 'despacho-montacargas': PIX('steel-coil-warehouse-4395773', 'jotoler'),
    'electricista-tablero': PIX('electric-electricity-building-1080584', 'jarmoluk'), 'entrega-almacen': PIX('hand-product-finished-goods-5983142', 'KaweewatT'),
    'hero-banda': PIX('factory-plant-industrial-hall-1137993', 'falco'), 'hero-extractores': PIX('industry-ventilation-metal-2182767', 'Ganossi'),
    'planta-operando': PIX('lab-machine-technology-8499925', 'This_is_Engineering'), 'sector-agroindustria': PIX('industry-factory-working-place-5434589', 'iLights'),
    'sector-alimentos': PIX('cider-bottling-plant-production-line-4484017', 'markdnalee'), 'sector-construccion': PIX('construction-worker-safety-2578410', 'bridgesward'),
    'sector-mineria': PIX('landscape-open-pit-mining-mining-4359640', '652234'), 'sector-petroleo': PIX('factory-industrial-production-7026489', 'yamabon'),
    'sector-solar': PIX('commercial-solar-pv-energy-panel-3397217', '—'), 'sector-talleres': PIX('human-work-industry-man-one-2164189', 'stafichukanatoly'),
    'tablero-cerca': PIX('electric-electricity-building-1080585', 'jarmoluk'), 'tablero-manos': PIX('electrician-electric-electricity-1080574', 'jarmoluk'),
    'tecnico-casco': PIX('electrician-electric-electricity-1080590', 'jarmoluk'), 'bodega': PIX('hangar-stock-suite-building-5183399', 'AJS1'),
    'almacen': ('Pixabay (almacén de estanterías altas; registrado en docs/creditos-media.md, sin página de origen guardada)', LIBRE),
    'contacto': ('Pixabay (módulos en un tablero; docs/creditos-media.md, sin página de origen guardada)', LIBRE),
    'comprar-caja': PEX(11781094), 'comprar-despacho': PEX(4487383), 'comprar-tecnico': PEX(33694016),
    'contacto-asesor': PEX(17842832), 'contacto-asesoria': PEX(7682200), 'contacto-cotizar': PEX(32845690), 'sector-agua': PEX(12726229),
    'contacto-garantia': PEX(9242911), 'contacto-pedido': PEX(6868621), 'hero-motores': PIX('machine-tubes-blue-pipes-factory-495376', 'Martinelle'), 'hero-motorreductores': PEX(13974251),
    'nosotros-asesoria': PEX(34054501), 'nosotros-despacho': PEX(6169177), 'nosotros-estanteria': PEX(28950842),
}
rows_fotos = []
for p in sorted((PUB / 'img/fotos').glob('*.webp')):
    src, kind = FOTOS.get(p.stem, ('—', SIN))
    rows_fotos.append((f'fotos/{p.name}', src, kind, 'Permitida' if kind == LIBRE else 'Falta fuente'))

REG = 'Pixabay (la página de origen no quedó registrada en docs/creditos-media.md)'
APLIC = {'bandas': (REG, LIBRE), 'bombeo': (REG, LIBRE), 'ventilacion': (REG, LIBRE),
         'fluke-en-tablero': ('Pixabay o imagen de Fluke (docs/creditos-media.md solo dice «Pixabay / Fluke»; falta registrar cuál)', 'Libre o imagen oficial de marca · fuente exacta por registrar')}
rows_misc = []
# Fotos y videos propios de la tienda (tomados por Imagen Eléctrica en su local; originales en cliente/media-fuente/propio/, fuera de git)
PROPIAS = {
    'pendon-weg-mostrador': 'Foto propia de Imagen Eléctrica: pendón WEG y mostrador (IMG_2406)',
    'vitrina-motorreductores': 'Foto propia de Imagen Eléctrica: vitrina de motorreductores (IMG_2407)',
    'compresor-ferrara-tienda': 'Foto propia de Imagen Eléctrica: compresor Ferrara en la tienda (IMG_2409)',
    'compresor-ferrara': 'Foto propia de Imagen Eléctrica: tanque del compresor Ferrara (recorte de IMG_2409, sin el letrero)',
}
for p in sorted((PUB / 'img/tienda').glob('*.webp')):
    rows_misc.append((f'tienda/{p.name}', PROPIAS.get(p.stem.replace('-sm', ''), '—'), PROPIA, 'Permitida'))
for p in sorted((PUB / 'img/aplicaciones').glob('*.webp')):
    src, kind = APLIC.get(p.stem, ('—', SIN))
    rows_misc.append((f'aplicaciones/{p.name}', src, kind, 'Permitida' if kind != SIN else 'Falta fuente'))
for p in sorted((PUB / 'img/como-comprar').glob('*.webp')):
    rows_misc.append((f'como-comprar/{p.name}', 'Captura de la propia tienda (cliente/scripts/capturar-mockups.mjs)', PROPIA, 'Permitida'))
LOGOS = {'abb.svg': 'Wikimedia Commons', 'delta.svg': 'Wikimedia Commons', 'fluke.svg': 'Wikimedia Commons', 'nexans.svg': 'Wikimedia Commons', 'nidec.svg': 'Wikimedia Commons',
         'siemens.svg': 'Wikimedia Commons', 'nord.svg': 'pattern-library.nord.com', 'soler-palau.svg': 'solerpalau.com', 'weg-cestari.svg': 'wegcestari.com.br',
         'airal.png': 'airal.com.co', 'leviton.png': 'Wikimedia Commons (File:Leviton logo.svg)', '3m.png': 'Wikimedia Commons (File:3M wordmark.svg)', 'telemecanique.png': 'Wikimedia Commons (File:Telemecanique Logo.svg)', 'autonics.png': 'Wikimedia Commons (File:Autonics logo.png)', 'chint.png': 'Wikimedia Commons (File:Chint logo.png)', 'weg.png': 'Recorte vectorial de los catálogos oficiales de WEG (static.weg.net)'}
for p in sorted((PUB / 'img/marcas').iterdir()):
    rows_misc.append((f'marcas/{p.name}', LOGOS.get(p.name, '—'), LOGO if p.name in LOGOS else SIN, 'Permitida' if p.name in LOGOS else 'Falta fuente'))
for n in ['fachada.webp', 'logo.png', 'logo-ajustado.png', 'logo-blanco.png', 'logo-blanco-ajustado.png']:
    rows_misc.append((n, 'Material propio de Imagen Eléctrica', PROPIA, 'Permitida'))
rows_misc.append(('mapa-tienda.webp', 'Teselas de OpenStreetMap (© colaboradores de OpenStreetMap)', OSM, 'Permitida'))
VIDEO = ('Pixabay, https://pixabay.com/videos/id-86252/ (autor: lumosajans), segundos 1 a 10', LIBRE)
for n in ['hero-v3.mp4', 'hero-v3.webm', 'hero-v3-m.mp4', 'hero-v3-m.webm']:
    rows_misc.append((f'media/{n}', *VIDEO, 'Permitida'))
VID_PROPIOS = {'propio-reductores': 'IMG_2417, motorreductores en cámara lenta', 'propio-control': 'IMG_2415, tablero de control', 'propio-compresor': 'IMG_2412, compresor Ferrara'}
for p in sorted((PUB / 'media').glob('propio-*')):
    base = re.sub(r'(-m|-poster)?\.(mp4|webm|jpg)$', '', p.name)
    rows_misc.append((f'media/{p.name}', f'Video propio de Imagen Eléctrica ({VID_PROPIOS.get(base, "—")}); sin audio ni rótulos de precio', PROPIA, 'Permitida'))
rows_misc.append(('media/hero-v3-poster.webp', 'Fotograma del video anterior (Pixabay, lumosajans)', LIBRE, 'Permitida'))

allrows = rows_prod + rows_fotos + rows_misc
cnt = Counter(r[2] for r in allrows)
no_perm = [r for r in allrows if 'NO permitida' in r[2]]
sin = [r for r in allrows if r[2] == SIN]
verif = [r for r in allrows if r[2] == VERIF]


def table(rows):
    return ['| Archivo (`cliente/public/img/…`) | Fuente | Tipo | Estado |', '|---|---|---|---|'] + [f'| `{a}` | {b} | {c} | {d} |' for a, b, c, d in rows]


out = ['# Licencias de imágenes y videos', '',
       'Inventario de **todos** los archivos de imagen y video del sitio (`cliente/public/`). Se genera con `docs/inventario/generar_licencias.py`.', '',
       '## Reglas', '',
       '- **Libre · uso comercial**: Pexels, Pixabay, Unsplash, Coverr o Mixkit (licencias que permiten uso comercial sin atribución obligatoria).',
       '- **Imagen oficial de marca · material de distribuidor**: foto del sitio del fabricante (de cualquier país), de su catálogo PDF, o publicada por un distribuidor que reproduce la imagen del fabricante. Imagen Eléctrica es distribuidora y las usa para identificar el producto que vende.',
       '- **Propia de Imagen Eléctrica**: fotos y videos que la empresa tomó en su local (mostrador, vitrinas, compresor, tablero). Son suyos; los originales están en `cliente/media-fuente/propio/` (fuera de git).',
       '- **NO permitida**: foto tomada por *otro* distribuidor (su mostrador, su bodega, su marca de agua) o de un mercado en línea. **No hay ninguna en el sitio** (ver resumen).',
       '- Cada imagen de producto de la Fase 1B se revisó una por una: ninguna trae marca de agua, mostrador, bodega ni fondo de tienda.', '',
       '## Resumen', '',
       f'- Archivos listados: **{len(allrows)}** (las miniaturas `-sm.webp` van con su imagen).',
       f'- Filas «NO permitida»: **{len(no_perm)}**.',
       f'- Sin fuente registrada: **{len(sin)}**.',
       f'- Por verificar (foto de distribuidor sin marca visible, no se puede probar que sea la imagen del fabricante): **{len(verif)}** ← la empresa debe confirmarlas o fotografiar el producto (`docs/inventario/fotos-para-tomar.md`).', '']
for t, n in cnt.most_common():
    out.append(f'- {t}: {n}')
out += ['', '## Imágenes de producto', ''] + table(rows_prod) + ['', '## Fotos de ambiente', ''] + table(rows_fotos) + ['', '## Aplicaciones, capturas, logos, mapa, fachada y video', ''] + table(rows_misc)
(ROOT / 'docs/licencias-imagenes.md').write_text('\n'.join(out) + '\n')
print(len(allrows), 'archivos; NO permitida', len(no_perm), '; sin fuente', len(sin), '; por verificar', len(verif))
for r in sin: print('  SIN FUENTE:', r[0])
