"""Reestructura el catálogo por líneas: una línea = un solo producto (mismo tipo y marca, distinta potencia, corriente o tensión).

Entrada y salida: backend/database/data/catalogo.json y line-images.json (los que deja generar_fase1.py).
Es idempotente: las reglas buscan la línea por su nombre ANTERIOR; si ya se aplicaron no encuentran nada y no cambian nada.

Qué hace con cada línea mezclada:
  - la separa en una línea por producto (p. ej. «Contactores» → CWB, CWB bobina DC, CWBC, mini CWC, accesorios);
  - nombre de la ficha = qué es + marca + serie («Contactor WEG CWB»);
  - la etiqueta de cada variante dice lo que la distingue («18 A · bobina 220 V»);
  - agrega a specs el tipo y la serie (solo cuando salen del nombre o del código de la referencia);
  - elige la foto de la línea (la de la primera variante con foto, o la que se indique).
Después de correrlo: php artisan migrate aplica CatalogLayoutService::sync() sobre la base ya desplegada.

Uso: python3 docs/inventario/reestructurar_catalogo.py
"""
import json
import re
import unicodedata
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
DATA = ROOT / 'backend' / 'database' / 'data'


def slugify(s):
    s = unicodedata.normalize('NFKD', s).encode('ascii', 'ignore').decode().lower()
    return re.sub(r'[^a-z0-9]+', '-', s).strip('-')


def num(txt):
    return float(txt.replace(',', '.'))


def fmt_amp(txt):
    return str(txt).replace('.', ',')


# ---------------------------------------------------------------- etiquetas por tipo de variante
def lab_amp(v):
    return f"{fmt_amp(v['amp'])} A" if v.get('amp') else v['label']


def lab_cwb(v):
    base = lab_amp(v)
    return f"{base} · bobina {v['volt']} V" if v.get('volt') else base


def lab_cwb_dc(v):
    return f"{lab_amp(v)} · bobina {v['volt']} Vdc"


def lab_cwbc(v):
    return f"{lab_amp(v)} · bobina {v['volt']} V"


def lab_cwc(v):
    m = re.search(r'Cwc(\d+)\s+(\d)\s?Na\s+(\d+)\s*Vac', v['name'], re.I)
    return f"CWC{m.group(1)} · {m.group(2)} NA · {m.group(3)} V" if m else v['label']


def lab_polos(v):
    m = re.search(r'(\d) Polo', v['name'])
    return f"{m.group(1)} {'polo' if m.group(1) == '1' else 'polos'} · {lab_amp(v)}" if m else lab_amp(v)


def lab_dwb(v):
    m = re.search(r'(Dwb\d+)', v['name'])
    extra = ' · regulable' if 'Regulable' in v['name'] else (' · alta capacidad' if 'Alta Capacidad' in v['name'] else '')
    return f"{m.group(1).upper()} · {v['label']}{extra}" if m else v['label']


def lab_name_tail(strip):
    """Etiqueta = el nombre sin el prefijo del producto (que ya está en el título de la línea)."""
    def f(v):
        t = re.sub(strip, '', v['name'], flags=re.I).strip(' -·')
        t = re.sub(r'\s+', ' ', t)
        return t[:1].upper() + t[1:] if t else v['label']
    return f


def keep(v):
    return v['label']


# ---------------------------------------------------------------- reglas
# 'pick' decide qué variantes de la línea vieja van a la línea nueva (primera regla que coincide gana).
# image: ruta de la foto de la línea (None = la de la primera variante con foto; 'sin' = sin foto de ese producto).
def name_has(*words):
    return lambda v: any(w.lower() in v['name'].lower() for w in words)


def sku_in(*skus):
    return lambda v: v['sku'] in skus


def brand_is(b):
    return lambda v: v['brand'] == b


def anyv(_):
    return True


def L(name, brand_label, pick, relabel=keep, *, slug=None, image=None, tipo=None, serie=None, chips=None, extra=None, category=None, hidden=False):
    return dict(name=name, brand_label=brand_label, pick=pick, relabel=relabel, slug=slug, image=image, tipo=tipo, serie=serie, chips=chips, extra=extra, category=category)


RULES = {
    # ------------------------------------------------ Control y automatización · WEG
    'Arrancadores directos': [
        L('Arrancador directo WEG DLW', 'WEG', anyv, keep, slug='arrancadores-directos', tipo='Arrancador directo', serie='DLW'),
    ],
    'Contactores': [
        L('Contactor Siemens 3RT2', 'Siemens', brand_is('Siemens'), lab_amp, image='sin', tipo='Contactor', serie='3RT2'),
        L('Contactor WEG CWB con bobina DC', 'WEG', lambda v: 'vdc' in v['name'].lower(), lab_cwb_dc, image='/img/catalogo/weg-contactores.webp', tipo='Contactor tripolar', serie='CWB'),
        L('Contactor WEG CWBC para banco de capacitores', 'WEG', name_has('cwbc'), lab_cwbc, image='/img/catalogo/weg-contactores.webp', tipo='Contactor para banco de capacitores', serie='CWBC'),
        L('Contactor WEG CWB', 'WEG', lambda v: v['name'].lower().startswith('contactor cwb'), lab_cwb, slug='contactores', image='/img/catalogo/weg-contactores.webp', tipo='Contactor tripolar', serie='CWB'),
        L('Mini contactor auxiliar WEG CWCA0', 'WEG', name_has('cwca0'), lambda v: '3 NA + 1 NC · 220 V', image='/img/catalogo/weg-mini-contactor-cwc.webp', tipo='Mini contactor auxiliar', serie='CWCA0'),
        L('Mini contactor WEG CWC', 'WEG', name_has('mini contactor cwc'), lab_cwc, image='/img/catalogo/weg-mini-contactor-cwc.webp', tipo='Mini contactor', serie='CWC'),
        L('Accesorios para contactores WEG CWB', 'WEG', anyv, lab_name_tail(r'^(bloque frontal para contactor cwb|enclavamiento mecanico utilizar con)\s*'), image='/img/catalogo/weg-contactores.webp', tipo='Accesorio de contactor', serie='CWB'),
    ],
    'Relés térmicos': [
        L('Relé térmico WEG RW17', 'WEG', name_has('rw17'), lab_amp, image='/img/catalogo/weg-reles-termicos.webp', tipo='Relé térmico de sobrecarga', serie='RW17'),
        L('Relé térmico WEG RW27', 'WEG', name_has('rw27'), lab_amp, slug='reles-termicos', image='/img/catalogo/weg-reles-termicos.webp', tipo='Relé térmico de sobrecarga', serie='RW27'),
        L('Relé térmico WEG RW67', 'WEG', name_has('rw67'), lab_amp, image='/img/catalogo/weg-reles-termicos.webp', tipo='Relé térmico de sobrecarga', serie='RW67'),
    ],
    'Relés de control y protección': [
        L('Relé térmico WEG RW17', 'WEG', name_has('rw17'), lab_amp, tipo='Relé térmico de sobrecarga', serie='RW17'),  # va a la línea RW17 (se funde por nombre)
        L('Relé electrónico de protección WEG RWB40', 'WEG', name_has('rwb40'), lab_amp, image='/img/catalogo/weg-reles-de-control.webp', tipo='Relé electrónico de sobrecarga', serie='RWB40'),
        L('Relé temporizador WEG RTW17', 'WEG', name_has('rtw17'), lambda v: re.sub(r'^Rele Electronico Rtw17 ', '', v['name']).replace('Conexion', 'Retardo a la conexión').replace('Desconexion', 'Retardo a la desconexión').replace('Estrella-Triangulo', 'Estrella-triángulo').replace(' S', ' s'), image='/img/catalogo/weg-rtw17.webp', tipo='Relé temporizador electrónico', serie='RTW17'),
        L('Relé vigilante de tensión WEG RMW17', 'WEG', name_has('rmw17'), lambda v: f"220 V {'monofásica' if 'Monofasica' in v['name'] else 'trifásica'}", image='/img/catalogo/weg-reles-de-control.webp', tipo='Relé monitor de tensión', serie='RMW17', slug='reles-de-control-y-proteccion'),
        L('Relé vigilante WEG RNW', 'WEG', name_has('vigilante'), lambda v: v['name'].replace('Rele Electronico Vigilante ', '').upper(), image='/img/catalogo/weg-reles-de-control.webp', tipo='Relé electrónico vigilante', serie='RNW'),
    ],
    'Guardamotores': [
        L('Guardamotor WEG MPW18', 'WEG', name_has('mpw18 ') , lab_amp, slug='guardamotores', image='/img/catalogo/weg-guardamotores.webp', tipo='Guardamotor magnetotérmico', serie='MPW18'),
        L('Guardamotor WEG MPW40', 'WEG', lambda v: 'guardamotor mpw40' in v['name'].lower(), lab_amp, image='/img/catalogo/weg-guardamotores.webp', tipo='Guardamotor magnetotérmico', serie='MPW40'),
        L('Accesorios para guardamotores WEG MPW', 'WEG', anyv, lab_name_tail(r'^'), image='/img/catalogo/weg-guardamotores.webp', tipo='Accesorio de guardamotor', serie='MPW'),
    ],
    'Pulsadores, pilotos y selectores': [
        L('Accesorios para guardamotores WEG MPW', 'WEG', name_has('mpw'), lab_name_tail(r'^'), tipo='Accesorio de guardamotor', serie='MPW'),  # los bloques MPW se funden con la línea de accesorios
        L('Paradas de emergencia WEG CSW', 'WEG', name_has('parada', 'placa de emergencia'), lab_name_tail(r'^'), image='/img/catalogo/weg-pulsadores.webp', tipo='Parada de emergencia', serie='CSW'),
        L('Selectores WEG CSW', 'WEG', name_has('selector'), lab_name_tail(r'^selector\s*'), image='/img/catalogo/weg-pulsadores.webp', tipo='Selector', serie='CSW'),
        L('Pilotos luminosos WEG CSW', 'WEG', name_has('piloto', 'iluminacion directa'), lab_name_tail(r'^'), image='/img/catalogo/weg-pulsadores.webp', tipo='Piloto luminoso', serie='CSW'),
        L('Bloques de contacto WEG CSW', 'WEG', name_has('bloque contacto'), lab_name_tail(r'^bloque contacto\s*'), image='/img/catalogo/weg-pulsadores.webp', tipo='Bloque de contacto', serie='CSW'),
        L('Pulsadores WEG CSW', 'WEG', anyv, lab_name_tail(r'^'), slug='pulsadores-pilotos-y-selectores', image='/img/catalogo/weg-pulsadores.webp', tipo='Pulsador', serie='CSW'),
    ],
    'Borneras y accesorios': [
        L('Bornes y accesorios WEG BTW', 'WEG', anyv, lab_name_tail(r'^'), slug='borneras-y-accesorios', image='/img/catalogo/weg-borneras.webp', tipo='Borne para riel', serie='BTW'),
    ],
    'Accesorios de maniobra': [
        L('Contacto auxiliar Siemens 3RH2', 'Siemens', brand_is('Siemens'), lambda v: '1 NA + 1 NC', image='sin', tipo='Contacto auxiliar', serie='3RH2'),
        L('Seccionador WEG MSW', 'WEG', name_has('seccionador', 'conector de alimentacion'), lab_name_tail(r'^'), image='/img/catalogo/weg-accesorios-de-maniobra.webp', tipo='Seccionador', serie='MSW', slug='accesorios-de-maniobra'),
        L('Accesorios para contactores WEG CWB', 'WEG', name_has('enclavamiento'), lab_name_tail(r'^enclavamiento mecanico utilizar con'), tipo='Accesorio de contactor', serie='CWB'),
        L('Pulsadores WEG CSW', 'WEG', name_has('capuchon'), lab_name_tail(r'^'), tipo='Pulsador', serie='CSW'),
        L('Bornes y accesorios WEG BTW', 'WEG', anyv, lab_name_tail(r'^'), tipo='Borne para riel', serie='BTW'),
    ],
    'Minibreakers': [
        L('Minibreaker WEG', 'WEG', anyv, lab_polos, slug='minibreakers', image='/img/catalogo/weg-minibreakers.webp', tipo='Minibreaker (interruptor termomagnético)'),
    ],
    'Totalizadores': [
        L('Totalizador WEG DWB', 'WEG', anyv, lab_dwb, slug='totalizadores', image='/img/catalogo/weg-totalizadores.webp', tipo='Interruptor totalizador', serie='DWB'),
    ],
    'Variador CFW100': [L('Variador de frecuencia WEG CFW100', 'WEG', anyv, keep, slug='variador-cfw100', tipo='Variador de frecuencia', serie='CFW100')],
    'Variador CFW300': [L('Variador de frecuencia WEG CFW300', 'WEG', anyv, keep, slug='variador-cfw300', tipo='Variador de frecuencia', serie='CFW300')],
    'Variador CFW500': [L('Variador de frecuencia WEG CFW500', 'WEG', anyv, keep, slug='variador-cfw500', tipo='Variador de frecuencia', serie='CFW500')],
    'Variador CFW900': [L('Variador de frecuencia WEG CFW900', 'WEG', anyv, keep, slug='variador-cfw900', tipo='Variador de frecuencia', serie='CFW900')],
    'Accesorios para variadores': [L('Módulo de expansión de entradas y salidas WEG', 'WEG', anyv, lambda v: 'Entradas y salidas', slug='accesorios-para-variadores', tipo='Módulo de expansión E/S')],
    'Arrancador suave SSW05': [L('Arrancador suave WEG SSW05', 'WEG', anyv, keep, slug='arrancador-suave-ssw05', tipo='Arrancador suave', serie='SSW05')],
    'Arrancador suave SSW07': [L('Arrancador suave WEG SSW07', 'WEG', anyv, keep, slug='arrancador-suave-ssw07', tipo='Arrancador suave', serie='SSW07')],
    'PLC WEG PLC200 y PLC500': [
        L('PLC WEG PLC500', 'WEG', anyv, keep, slug='plc-weg-plc200-y-plc500', image='/img/catalogo/weg-plc500.webp', tipo='Controlador lógico programable', serie='PLC500'),
        L('PLC WEG PLC200', 'WEG', lambda v: False, keep, image='sin', tipo='Controlador lógico programable', serie='PLC200'),
    ],
    # ------------------------------------------------ Motores y reductores: solo el nombre (qué es + marca + serie)
    'W01 monofásico semiabierto': [L('Motor monofásico WEG W01 semiabierto', 'WEG', anyv, keep, slug='w01-monofasico-semiabierto', tipo='Motor monofásico', serie='W01')],
    'W01 monofásico cerrado': [L('Motor monofásico WEG W01 cerrado', 'WEG', anyv, keep, slug='w01-monofasico-cerrado', tipo='Motor monofásico', serie='W01')],
    'W12 monofásico': [L('Motor monofásico WEG W12', 'WEG', anyv, keep, slug='w12-monofasico', tipo='Motor monofásico', serie='W12')],
    'W22 monofásico': [L('Motor monofásico WEG W22', 'WEG', anyv, keep, slug='w22-monofasico', tipo='Motor monofásico', serie='W22')],
    'W21 IEC · carcasa aluminio': [L('Motor trifásico WEG W21 IEC · carcasa aluminio', 'WEG', anyv, keep, slug='w21-iec-carcasa-aluminio', tipo='Motor trifásico', serie='W21')],
    'W22 IEC · carcasa hierro': [L('Motor trifásico WEG W22 IEC · carcasa hierro', 'WEG', anyv, keep, slug='w22-iec-carcasa-hierro', tipo='Motor trifásico', serie='W22')],
    'W22 NEMA Premium Efficiency': [L('Motor trifásico WEG W22 NEMA Premium', 'WEG', anyv, keep, slug='w22-nema-premium-efficiency', tipo='Motor trifásico', serie='W22')],
    'W22 a prueba de explosión': [L('Motor WEG W22 a prueba de explosión', 'WEG', anyv, keep, slug='w22-a-prueba-de-explosion', tipo='Motor trifásico a prueba de explosión', serie='W22')],
    'Motor trifásico WEG de bobinado': [L('Motor trifásico WEG de bobinado', 'WEG', anyv, keep, slug='motor-trifasico-weg-de-bobinado', tipo='Motor trifásico')],
    'Motores ABB': [L('Motor trifásico ABB', 'ABB', anyv, keep, slug='motores-abb', tipo='Motor trifásico')],
    'Motores Nidec US Motors': [L('Motor trifásico Nidec US Motors', 'Nidec', anyv, keep, slug='motores-nidec-us-motors', tipo='Motor trifásico')],
    'Motores Nord': [L('Motor trifásico Nord', 'Nord', anyv, keep, slug='motores-nord', tipo='Motor trifásico')],
    'Motores Match Drive': [L('Motor monofásico Match Drive', 'Match Drive', anyv, keep, slug='motores-match-drive', tipo='Motor monofásico', image='sin')],
    'Motorreductor sinfín WCG01': [L('Motorreductor WEG Cestari WCG01 · sinfín y corona', 'WEG Cestari', anyv, keep, slug='motorreductor-sinfin-wcg01', tipo='Motorreductor sinfín y corona', serie='WCG01')],
    'Reductor helicoidal WCG20': [L('Reductor helicoidal WEG Cestari WCG20', 'WEG Cestari', anyv, keep, slug='reductor-helicoidal-wcg20', tipo='Reductor helicoidal', serie='WCG20')],
    'Cajas reductoras WEG-Cestari': [L('Caja reductora WEG Cestari', 'WEG', anyv, keep, slug='cajas-reductoras-weg-cestari', tipo='Caja reductora')],
    'Reductores de otras marcas': [L('Reductor Siti', 'Siti', anyv, keep, slug='reductores-de-otras-marcas', tipo='Reductor')],
    'THHN / THWN-2 Centelsa': [L('Cable THHN/THWN-2 Centelsa', 'Centelsa', anyv, keep, slug='thhn-thwn-2-centelsa', tipo='Cable THHN/THWN-2')],
    'START Highbay LED': [L('Campana LED Sylvania START Highbay', 'Sylvania', anyv, keep, slug='start-highbay-led', tipo='Campana LED industrial', serie='START Highbay')],
    # El compresor del inventario es FERRARA (se lee en el tanque), no ABAC: foto propia de la tienda, recortada
    'Compresor vertical': [L('Compresor de pistón Ferrara', 'Ferrara', anyv, keep, slug='compresor-vertical', image='/img/tienda/compresor-ferrara.webp', tipo='Compresor de pistón')],
    # ------------------------------------------------ Fase 1B: una línea por producto
    'Inversores de giro, estaciones de mando e interruptores': [
        L('Interruptor de pedal Xurui XF', 'Xurui', name_has('pedal'), keep, image=None, tipo='Interruptor de pedal', serie='XF'),
        L('Interruptor on/off Margirius', 'Margirius', name_has('on/off 2 posiciones'), keep, tipo='Interruptor on/off de 2 posiciones'),
        L('Inversor de giro Merz', 'Merz', brand_is('Merz'), keep, tipo='Inversor de giro (conmutador de leva)', extra={'polos': 3}),
        L('Inversor de giro EBCHQ', 'EBCHQ', name_has('inversor de giro'), keep, tipo='Inversor de giro (conmutador de leva)', extra={'polos': 3}),
        L('Estación de mando de pulsadores y emergencia', 'Sin marca', name_has('estación de mando', 'estacion de mando'), keep, tipo='Estación de mando'),
        L('Selector de muletilla EBCHQ de 3 posiciones', 'EBCHQ', name_has('selector de muletilla'), keep, tipo='Selector de muletilla', serie='LAY4'),
        L('Arrancador on/off con pulsador EBCHQ', 'EBCHQ', name_has('motor starter'), keep, tipo='Arrancador on/off con pulsador'),
        L('Protector electrónico de voltaje trifásico Magom', 'Magom', anyv, keep, tipo='Protector de voltaje trifásico'),
    ],
    'Relés enchufables y bases': [
        L('Base para relé de 8 pines', 'Sin marca', lambda v: v['name'].lower().startswith('base para mini relé 8') , keep, tipo='Base para relé'),
        L('Base para relé de 11 pines Telemecanique', 'Telemecanique', lambda v: v['brand'] == 'Telemecanique', keep, tipo='Base para relé', serie='RUZ'),
        L('Base para relé de 11 pines', 'Sin marca', name_has('base para relé 11'), keep, tipo='Base para relé'),
        L('Mini relé de 14 pines Relpol', 'Relpol', brand_is('Relpol'), keep, tipo='Mini relé enchufable'),
        L('Mini relé de 14 pines', 'Sin marca', name_has('mini relé 14'), keep, tipo='Mini relé enchufable'),
        L('Mini relé slim de 5 pines', 'Sin marca', name_has('slim'), keep, tipo='Mini relé slim'),
        L('Relé de 8 pines EBCHQ', 'EBCHQ', anyv, keep, tipo='Relé enchufable'),
    ],
    'Finales de carrera y micro switches': [
        L('Final de carrera Xurui XZ-8', 'Xurui', brand_is('Xurui') if False else (lambda v: v['sku'] == 'XZ-8/108'), keep, tipo='Final de carrera', serie='XZ-8'),
        L('Final de carrera de varilla ajustable', 'Sin marca', name_has('final de carrera'), keep, tipo='Final de carrera'),
        L('Micro switch KAP', 'KAP', brand_is('KAP'), keep, tipo='Micro switch'),
        L('Micro switch Xurui', 'Xurui', anyv, keep, tipo='Micro switch'),
    ],
    'Aisladores de barraje y distribución': [
        L('Aislador de barraje SM', 'Sin marca', name_has('aislador de barraje'), keep, tipo='Aislador de barraje', serie='SM'),
        L('Aislador de barraje escalonado', 'Sin marca', name_has('aislador'), keep, tipo='Aislador escalonado'),
        L('Bloque de distribución Onka', 'Onka', anyv, keep, tipo='Bloque de distribución'),
    ],
    'Temporizadores, controladores de temperatura y horómetros': [
        L('Controlador de temperatura Autonics', 'Autonics', name_has('controlador de temperatura'), keep, tipo='Controlador de temperatura'),
        L('Temporizador estrella-triángulo Autonics', 'Autonics', name_has('estrella-triángulo'), keep, tipo='Temporizador estrella-triángulo'),
        L('Temporizador Autonics', 'Autonics', lambda v: v['name'].lower().startswith('temporizador'), keep, tipo='Temporizador'),
        L('Horómetro Autonics LE8N', 'Autonics', name_has('horómetro', 'horometro'), keep, tipo='Horómetro', serie='LE8N'),
        L('Programador semanal EBCHQ para riel DIN', 'EBCHQ', name_has('programador'), keep, tipo='Programador semanal'),
        L('Termostato Plastim', 'Plastim', anyv, keep, tipo='Termostato'),
    ],
    'Bombillos LED y ahorradores': [
        L('Bombillo halógeno H3', 'American', name_has('halógeno'), keep, tipo='Bombillo halógeno'),
        L('Bombillo LED E27 Sylvania', 'Sylvania', brand_is('Sylvania'), keep, tipo='Bombillo LED'),
        L('Bombillo LED E27 Lexmana', 'Lexmana', brand_is('Lexmana'), keep, tipo='Bombillo LED'),
        L('Bombillo LED E27 Excelite', 'Excelite', brand_is('Excelite'), keep, tipo='Bombillo LED'),
        L('Bombillo LED RGB GU10 Mercury', 'Mercury', anyv, keep, tipo='Bombillo LED RGB'),
    ],
    'Paneles LED': [
        L('Panel LED circular de incrustar Mercury', 'Mercury', brand_is('Mercury'), keep, tipo='Panel LED circular de incrustar'),
        L('Panel LED circular de sobreponer Sylvania', 'Sylvania', brand_is('Sylvania'), keep, tipo='Panel LED circular de sobreponer'),
        L('Panel LED cuadrado de incrustar', 'Sin marca', name_has('cuadrado'), keep, tipo='Panel LED cuadrado de incrustar'),
        L('Panel LED redondo de incrustar', 'Sin marca', anyv, keep, tipo='Panel LED redondo de incrustar'),
    ],
    'Reflectores LED': [
        L('Lámpara de alumbrado público VCP', 'VCP', brand_is('VCP'), keep, tipo='Lámpara de alumbrado público'),
        L('Reflector LED Titanium', 'Titanium', brand_is('Titanium'), keep, tipo='Reflector LED'),
        L('Reflector LED Élite', 'Élite', anyv, keep, tipo='Reflector LED'),
    ],
    'Spots, bases y emergencia': [L('Spot LED Karluz', 'Karluz', anyv, keep, tipo='Spot LED')],
    'Balizas': [L('Baliza multivoltaje tricolor SPK', 'SPK', anyv, keep, tipo='Baliza de señalización')],
    'Transformadores de control': [L('Transformador de control Wattio', 'Wattio', anyv, keep, tipo='Transformador de control')],
    'Motobombas': [L('Motobomba IHM', 'IHM', anyv, keep, tipo='Motobomba')],
    'Cintas aislantes': [L('Cinta aislante PVC 3M Temflex', '3M', anyv, keep, tipo='Cinta aislante de PVC', serie='Temflex')],
    'Insumos, herramientas y repuestos': [
        L('Cinta de enmascarar Cellux 18 mm', 'Cellux', name_has('enmascarar'), keep, tipo='Cinta de enmascarar', category='Cintas de enmascarar', extra={'longitud': '40 yardas'}),
        L('Batería Magna 12 V 5 Ah', 'Magna', name_has('batería', 'bateria'), keep, tipo='Batería', category='Baterías', image='/img/catalogo/bateria-12v.webp'),
        L('Tapón macho Swagelok 3/4 NPT acero inoxidable', 'Swagelok', anyv, keep, tipo='Tapón macho', serie='SS-12-P', category='Tapones y conexiones', extra={'rosca': '3/4 NPT', 'material': 'Acero inoxidable'}),
    ],
    'Interruptores y tomas inteligentes': [L('Interruptor inteligente Ilumax', 'Ilumax', anyv, keep, tipo='Interruptor inteligente')],
    'Tomas, clavijas y tapas Leviton': [
        L('Tapa para toma Leviton', 'Leviton', name_has('tapa'), keep, tipo='Tapa para toma'),
        L('Toma doble Leviton', 'Leviton', name_has('toma doble'), keep, tipo='Toma doble', extra={}),
        L('Clavija Leviton', 'Leviton', name_has('clavija'), keep, tipo='Clavija'),
        L('Toma industrial Leviton', 'Leviton', anyv, keep, tipo='Toma industrial'),
    ],
    'Medidores e instrumentos de panel': [
        L('Amperímetro análogo Chint', 'Chint', brand_is('Chint'), keep, tipo='Amperímetro análogo de panel'),
        L('Analizador de red multifunción Nippen', 'Nippen', anyv, keep, tipo='Analizador de red multifunción'),
    ],
}

# Marca de referencias que el Excel trae «Sin marca» pero cuya fuente (página del distribuidor o código de fabricante) la dice
SKU_BRAND = {'47040': 'VCP', '9023': 'VCP', '46983': 'Relpol', '46984': 'Relpol', '25776': 'EBCHQ', 'AP3T72Z11': 'Comepi', '34035': 'SPK', '34051': 'SPK'}
NAME_FIX = {
    'Base para relé de 8 pines': 'Base para relé de 8 pines VCP', 'Base para relé de 11 pines': 'Base para relé de 11 pines VCP',
    'Mini relé slim de 5 pines': 'Mini relé slim de 5 pines Relpol', 'Estación de mando de pulsadores y emergencia': 'Estación de mando EBCHQ de pulsadores y emergencia',
    'Final de carrera de varilla ajustable': 'Final de carrera Comepi de varilla ajustable', 'Aislador de barraje SM': 'Aislador de barraje SPK SM',
}

FAMILY_RENAMES = {
    'Motores eléctricos': 'Motores Eléctricos', 'Reductores y motorreductores': 'Reductores y Motorreductores',
    'Control y automatización': 'Control y Automatización', 'Ventilación y climatización': 'Ventilación y Climatización',
    'Cables y conductores': 'Cables y Conductores', 'Medición y testeo': 'Medición y Testeo', 'Material eléctrico': 'Material Eléctrico',
    'Otros productos': 'Herramientas y Repuestos',
}

# Referencias cuya foto actual NO es de ese producto (se quitan; quedan en docs/inventario/fotos-para-tomar.md)
FOTO_INCORRECTA = {'MA5-12'}

# Series que salen del código de la referencia (SKU) cuando el fabricante lo usa como código de serie
SERIE_POR_SKU = [
    (r'^TC4Y', 'TC4Y'), (r'^TZN4S', 'TZN4S'), (r'^AT8PMN', 'AT8PMN'), (r'^ATE8', 'ATE8'), (r'^AT8SDN', 'AT8SDN'), (r'^ATE-60S', 'ATE-60S'),
    (r'^XZ-8', 'XZ-8'), (r'^XZ-15', 'XZ-15'), (r'^XV-156', 'XV-156'), (r'^M3B', 'M3B'),
    (r'^WID', None), (r'^ELT33', 'ELT33'), (r'^TTA63', 'TTA63'),
]


def main():
    cat = json.loads((DATA / 'catalogo.json').read_text())
    images = json.loads((DATA / 'line-images.json').read_text())
    # Familias con mayúscula inicial en cada palabra importante; la 10 pasa a «Herramientas y Repuestos»
    for old, new in FAMILY_RENAMES.items():
        for fam in cat['families']:
            if fam['name'] == old:
                fam['name'] = new
        for line in cat['lines']:
            if line['family'] == old:
                line['family'] = new
    for fam in cat['families']:
        if fam['name'] == 'Herramientas y Repuestos':
            fam['description'] = 'Cintas, baterías, conexiones y repuestos para el día a día de tu taller o planta.'
    lines_out, merged = [], {}  # merged: (nombre nuevo) -> línea ya creada (para fundir varias líneas viejas)
    removed_slugs = set()
    changed = 0

    for line in cat['lines']:
        rules = RULES.get(line['name'])
        if not rules:
            lines_out.append(line)
            continue
        changed += 1
        old_slug = slugify(line['name'])
        remaining = list(line['variants'])
        for rule in rules:
            picked = [v for v in remaining if rule['pick'](v)]
            remaining = [v for v in remaining if v not in picked]
            new = merged.get(rule['name'])
            if new is None:
                new = {
                    'family': line['family'], 'category': rule['category'] or line['category'], 'name': rule['name'],
                    'brand_label': None if rule['brand_label'] == 'Sin marca' else rule['brand_label'], 'image': line['image'], 'specs': list(line['specs']) if len(rules) == 1 else [], 'mode': line['mode'], 'variants': [],
                }
                if rule['slug']:
                    new['slug'] = rule['slug']
                if rule['image']:
                    new['_image'] = rule['image']
                merged[rule['name']] = new
                lines_out.append(new)
            for v in picked:
                nv = dict(v)
                nv['label'] = rule['relabel'](v)
                if rule['name'] in ('Accesorios para guardamotores WEG MPW',):
                    nv['label'] = nv['label']
                specs = dict(nv.get('specs') or {})
                if rule['tipo']:
                    specs['tipo'] = rule['tipo']
                serie = rule['serie']
                if serie is None:
                    for pat, s in SERIE_POR_SKU:
                        if re.search(pat, v['sku']) and s:
                            serie = s
                            break
                if serie:
                    specs['serie'] = serie
                for k, val in (rule['extra'] or {}).items():
                    specs.setdefault(k, val)
                # La corriente de los mini contactores sale de la designación WEG (CWC07 = 7 A, CWC09 = 9 A, CWC12 = 12 A)
                m = re.search(r'CWC(\d+)', nv['label'])
                if m and rule['serie'] == 'CWC' and not nv.get('amp'):
                    nv['amp'] = str(int(m.group(1)))
                nv['specs'] = specs
                # XZ-15GD-B y XV-156-1C25 vienen como «Sin marca» en el Excel, pero la foto del producto trae el logo Xurui
                if v['sku'] in ('XZ-15GD-B', 'XV-156-1C25'):
                    nv['brand'] = 'Xurui'
                # WCG01 y WCG20 son reductores WEG Cestari (catálogo general de WEG Cestari); el inventario los trae sin marca o como WEG
                if nv['sku'] in SKU_BRAND and nv['brand'] == 'Sin marca':
                    nv['brand'] = SKU_BRAND[nv['sku']]
                if rule['serie'] in ('WCG01', 'WCG20'):
                    nv['brand'] = 'WEG Cestari'
                # La foto de MA5-12 es de otra batería (la etiqueta dice «MA 4-6», de 6 V): se quita hasta tener la foto de la 12 V 5 Ah
                if v['sku'] in FOTO_INCORRECTA:
                    nv.pop('image', None)
                new['variants'].append(nv)
            if not picked and rule['name'] not in [n for n in merged if merged[n]['variants']]:
                pass
        if remaining:
            raise SystemExit(f"Variantes sin regla en «{line['name']}»: {[v['sku'] for v in remaining]}")
        removed_slugs.add(old_slug)

    # Marca en todas las líneas: nombre con la marca cuando la hay; «Genérico» cuando el inventario no la dice
    for l in lines_out:
        l['name'] = NAME_FIX.get(l['name'], l['name'])
        for v in l['variants']:
            if v['brand'] == 'Sin marca':
                v['brand'] = 'Genérico'
        brands = []
        for v in l['variants']:
            if v['brand'] not in brands:
                brands.append(v['brand'])
        if brands:
            l['brand_label'] = ' · '.join(brands)

    # Slug final de cada línea y foto
    final_slugs = {}
    for l in lines_out:
        l['slug'] = l.get('slug') or slugify(l['name'])
        final_slugs[l['slug']] = l
    new_images = {}
    for slug, entry in images.items():
        if slug in final_slugs:
            new_images[slug] = entry
    for l in lines_out:
        img = l.pop('_image', None)
        if img == 'sin':
            new_images.pop(l['slug'], None)
        elif img:
            new_images[l['slug']] = img
        elif l['slug'] not in new_images:
            first = next((v.get('image') for v in l['variants'] if v.get('image')), None)
            if first:
                new_images[l['slug']] = first

    # Etiquetas repetidas dentro de una línea: se distinguen con el SKU
    for l in lines_out:
        if any(v.get('hp') for v in l['variants']):
            continue  # motores y reductores: la ficha distingue las gemelas por corriente, tensión y protección (twinTag)
        seen = {}
        for v in l['variants']:
            seen.setdefault(v['label'], []).append(v)
        for label, vs in seen.items():
            if len(vs) > 1:
                for v in vs:
                    if v['sku'] not in v['label']:
                        v['label'] = f"{label} · {v['sku']}"

    # Chips de la línea: rango de lo que traen las variantes (potencia, corriente) cuando no hay chips propios
    for l in lines_out:
        if not l.get('specs'):
            lows, highs = [], []
            for v in l['variants']:
                if v.get('amp'):
                    parts = str(v['amp']).split('-')
                    lows.append(num(parts[0]))
                    highs.append(num(parts[-1]))
            watts = [v['specs']['potencia_w'] for v in l['variants'] if (v.get('specs') or {}).get('potencia_w')]
            nice = lambda x: fmt_amp(int(x) if x == int(x) else x)
            if lows:
                lo, hi = min(lows), max(highs)
                l['specs'] = [f"{nice(lo)} A" if lo == hi else f"{nice(lo)}–{nice(hi)} A"]
            elif watts:
                l['specs'] = [f"{min(watts)}–{max(watts)} W"] if min(watts) != max(watts) else [f"{watts[0]} W"]

    # Un SKU en un solo lugar
    seen_sku = {}
    for l in lines_out:
        for v in l['variants']:
            if v['sku'] in seen_sku:
                raise SystemExit(f"SKU repetido {v['sku']} en {seen_sku[v['sku']]} y {l['name']}")
            seen_sku[v['sku']] = l['name']

    cat['lines'] = lines_out
    (DATA / 'catalogo.json').write_text(json.dumps(cat, ensure_ascii=False, indent=1) + '\n')
    (DATA / 'line-images.json').write_text(json.dumps(new_images, ensure_ascii=False, indent=1) + '\n')
    print(f'{changed} líneas reestructuradas → {len(lines_out)} líneas, {len(seen_sku)} referencias, {len(new_images)} fotos de línea')


if __name__ == '__main__':
    main()
