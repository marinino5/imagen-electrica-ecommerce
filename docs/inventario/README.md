# Inventario 2026 · productos nuevos (Fase 1B)

| Archivo | Qué es |
|---|---|
| `INVENTARIO_2026_1.xlsx` | Inventario de la empresa (hojas por proveedor), tal como lo entregó |
| `inventario-productos-nuevos.xlsx` | Resultado del cruce con la tienda: hojas «Productos nuevos» (292 filas, estado Listo/Pendiente), «Resumen por línea», «Stock distinto» y «Hojas vacías» |
| `extraer_excel.py` | Pasa las hojas «Productos nuevos» y «Stock distinto» a `filas-nuevas.json` y `stock-distinto.json` |
| `nombres_fase1.py` | Nombre, etiqueta y especificaciones al formato de la tienda de las 225 referencias «Listo» (solo datos que salen de la descripción) |
| `imagenes_fase1.json` | SKU → foto recortada (archivo, página de origen, tipo de fuente). Sin entrada no se publica |
| `generar_fase1.py` | Arma `backend/database/data/catalogo.json` (= `catalogo.base.json` + líneas nuevas), `line-images.json`, `pendientes-empresa.md`, `precios-pendientes.csv` y las filas de `docs/imagenes-referencias.csv` |
| `pendientes-empresa.md` | Lo que la empresa debe completar: códigos, descripciones, precios y fotos |
| `precios-pendientes.csv` | Una fila por referencia publicada con `precio_con_iva` vacío |
| `fotos-pendientes.json` / `intentos_fotos.json` | Referencias «Listo» sin foto y las páginas que se revisaron |

## Reglas

- Solo se publican las filas con Estado = «Listo» **y** con foto. Las «Pendiente» no se publican (ver `pendientes-empresa.md`).
- Ninguna referencia nueva tiene precio: todas se venden por **cotización** hasta que lleguen los precios.
- Fotos: sitio o catálogo oficial de la marca → distribuidor oficial en Colombia → sitio de la marca en otro país. Nunca la foto de otra marca ni generada con IA. Cada una pasa por `cliente/scripts/normalizar-producto.py` (WebP recortado, lienzo de 1000 × 1000, producto al 78 %, sombra de contacto).

## Cuando lleguen los precios

1. Llenar `precio_con_iva` en `precios-pendientes.csv` (acepta `1.250.000` o `1250000,50`; deja vacías las que sigan en cotización).
2. Probar: `php artisan catalog:load-prices docs/inventario/precios-pendientes.csv --dry-run`
3. Cargar: `php artisan catalog:load-prices docs/inventario/precios-pendientes.csv`

Cada SKU con precio pasa a compra en línea (`sale_mode = direct`, precio con IVA, `price_is_simulated = false`) y su línea pasa a «directa». Las demás siguen en cotización.

## Regenerar

```bash
python3 docs/inventario/extraer_excel.py      # solo si cambió el Excel (necesita openpyxl)
python3 docs/inventario/generar_fase1.py
```

`catalogo.base.json` y `line-images.base.json` son el catálogo de la entrega anterior: el generador siempre parte de ellos, así que se puede correr las veces que haga falta.
