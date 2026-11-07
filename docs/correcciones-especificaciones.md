# Correcciones de especificaciones

Revisión de los datos técnicos que muestra la ficha (potencia, RPM, IP, corriente, tensión) contra lo publicado por el fabricante.

**Límite de esta revisión.** `weg.net` bloquea el acceso automatizado (403), así que no se pudieron abrir las fichas oficiales de WEG por su propio sitio y **no se esquivó el bloqueo**. Se contrastó con lo que sí es público: catálogos y fichas de WEG alojados en otros sitios (repositorio de la UTN, `static.weg.net` vía buscador, distribuidores que copian la ficha) y con el propio inventario. Lo que no se pudo confirmar queda marcado como **pendiente**; no se cambió ningún dato sin fuente.

Las referencias del catálogo salen del inventario de Siigo: la potencia y las RPM de cada motor vienen del nombre del producto. Las fichas de motores no guardan corriente, tensión, polos, frame ni eficiencia, por eso la ficha no los muestra.

## Corregido

| Dato | Antes | Ahora | Fuente |
|---|---|---|---|
| Línea **W22 monofásico**, chip de protección | `IP21` | `IP55 / IP21` | 7 de las 8 referencias del inventario son IP55 y solo la de 5 HP / 1800 RPM (SKU 11177088) es IP21. La ficha WEG del W22 monofásico (0,5 HP, 4 polos, 60 Hz) indica *Grado de protección IP55, IC411 – TEFC*. Migración `2026_11_10_120000_fix_w22_monofasico_ip_chip.php` |

## Verificado, sin cambios

| Dato | Resultado | Fuente |
|---|---|---|
| W22 IEC carcasa hierro: IP55, IE3, TEFC | Coincide | Catálogo técnico WEG W22 mercado latinoamericano (50024297) y fichas de producto WEG |
| W21 IEC carcasa aluminio: IP55, IE3, hasta 30 HP | Coincide (la serie llega a 37 kW) | Folletos WEG W21 (50096575, 50124117) |
| W01 semiabierto IP21 y W01 cerrado IP55 | Coincide con el inventario (nombres «Ip21» / «Ip55») | Inventario; no se encontró la ficha W01 en fuentes abiertas |
| Velocidades 1200 / 1800 / 3600 RPM | Coherentes con 6, 4 y 2 polos a 60 Hz (velocidad sincrónica; WEG nombra el motor por ella) | IEC 60034 |
| HP, RPM e IP de cada referencia frente al nombre del inventario | 0 diferencias en las 34 líneas (revisión automática de todas las referencias) | `backend/database/data/catalogo.json` |

## Pendiente de confirmar

| Dato | Observación |
|---|---|
| **CFW100, «3 HP · 7,2 A»** (SKU 14870192) y chip «0,5–3 HP» | El folleto oficial del CFW100 indica 0,18–1,5 kW (0,25–2 HP). La referencia de 3 HP del inventario no coincide con ese rango: confirmar con el proveedor si el nombre es correcto antes de cambiarlo |
| CFW300 chip «0,5–5 HP» | El catálogo WEG cubre hasta 10 HP; el chip refleja lo que hay en inventario, no el rango de la familia |
| CFW500 chip «5–15 HP» | El catálogo WEG da 1/3 a 40 HP; el chip refleja el inventario |
| Corriente y tensión de motores, polos, frame y eficiencia | No están en los datos de la tienda; agregarlos exige las fichas por referencia (bloqueadas en weg.net) |
| SSW05 / SSW07 / CFW900 | Corrientes y potencias del inventario sin contraste contra ficha oficial |
