#!/usr/bin/env python3
"""Convierte docs/inventario/inventario-productos-nuevos.xlsx en JSON para generar_fase1.py (necesita openpyxl: pip install openpyxl).
Escribe filas-nuevas.json (hoja «Productos nuevos») y stock-distinto.json (hoja «Stock distinto»)."""
import json, os
import openpyxl
here = os.path.dirname(__file__)
wb = openpyxl.load_workbook(os.path.join(here, 'inventario-productos-nuevos.xlsx'), data_only=True)
rows = list(wb['Productos nuevos'].iter_rows(values_only=True))
head = rows[0]
json.dump([dict(zip(head, r)) for r in rows[1:]], open(os.path.join(here, 'filas-nuevas.json'), 'w', encoding='utf8'), ensure_ascii=False, indent=1, default=str)
st = list(wb['Stock distinto'].iter_rows(values_only=True))
out = [{'sku': str(r[0]).strip(), 'descripcion': r[1], 'linea': r[2], 'stock_tienda': int(r[3]), 'stock_excel': int(r[4])} for r in st[1:] if r[0]]
json.dump(out, open(os.path.join(here, 'stock-distinto.json'), 'w', encoding='utf8'), ensure_ascii=False, indent=1)
print(len(rows) - 1, 'filas;', len(out), 'referencias con stock distinto')
