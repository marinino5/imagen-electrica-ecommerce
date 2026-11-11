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

## Revisión producto por producto (Fase 2)

Cambios hechos al reestructurar el catálogo (`docs/inventario/reestructurar_catalogo.py`; detalle por línea en `docs/revision-productos.md`).

| Dato | Cambio | Fuente |
|---|---|---|
| Tipo y serie de cada referencia | Se agregan `tipo` y `serie` a `specs` (p. ej. «Contactor tripolar · CWB»). La serie solo se pone cuando sale del nombre del inventario o del código de la referencia | Nombre/SKU del inventario de la empresa |
| Contactor CWB: corriente de cada variante (9, 12, 18, 25, 32, 38, 40, 50, 65, 80, 95 A) | Confirmada: la designación CWB9, CWB12… corresponde a la corriente nominal; CWB9 = 9 A (Ith 25 A) | Catálogo WEG «Operación y protección de motores hasta 18,5 kW», p. 19 |
| Mini contactores CWC07 / CWC09 / CWC12 | Se agrega la corriente (7, 9 y 12 A) tomada de la designación del tipo; la serie CWC cubre CWC07, 09, 012, 016 y 025 | Catálogo WEG «CWC Series – Miniature Contactors» (static.weg.net, `ha3/h37`) |
| Relé temporizador RTW17 | Variantes «3–30 s»: retardo a la conexión (RTW17-A), a la desconexión y estrella-triángulo. Rango 0,1 s a 60 min según modelo | Catálogo WEG «Electronic Relays Modular Line», p. 13 |
| Guardamotor MPW18 / MPW40 | Se separan en dos líneas (antes una sola con los conectores y bloques) | Catálogo WEG de operación y protección, pp. 53-77 |
| «Relé de control y protección» con un RW17 4–6,3 A | Era un relé térmico RW17: pasa a la línea «Relé térmico WEG RW17» | Nombre del inventario («Rele Terimico Rw17») |
| Bloques frontal y lateral para MPW18/40/100 | Estaban en «Pulsadores, pilotos y selectores»: pasan a «Accesorios para guardamotores WEG MPW» | Nombre del inventario |
| Marca de XZ-15GD-B y XV-156-1C25 | De «Sin marca» a **Xurui**: el logo Xurui y el código de la referencia están impresos en el producto de la foto | Foto del producto |
| Marca de WCG01 y WCG20 | De «Sin marca»/«WEG» a **WEG Cestari** (la línea WCG es de WEG Cestari) | Catálogo general de redutores WEG Cestari |
| Inversor de giro 264181 | Era parte de «Inversores de giro» con Merz: pasa a «Inversor de giro EBCHQ» (otra marca) | Inventario |
| Batería MA5-12 | Se retira la foto: la etiqueta de la batería de la foto dice «MA 4-6» (6 V), no 12 V 5 Ah | Foto del producto |
| Totalizadores 14256837 y 11339798 | Ambas se describen como «Dwb160 200 Amp»: se conservan las dos con el SKU en la etiqueta hasta confirmar si es un duplicado | Inventario |
| Duplicados RMW17 (12735387 y 12735389) | Misma descripción «220 Vac trifásica»: se conservan con el SKU en la etiqueta | Inventario |

**No verificado** (no hay ficha técnica oficial accesible desde este equipo; la mayoría de sitios de fabricantes bloquea la descarga automática): especificaciones de las referencias de Autonics, Leviton, Xurui, EBCHQ, Relpol, Chint, Nippen y demás marcas de la Fase 1B. Se muestran solo los datos de la descripción del inventario. Series MDW (minibreakers) y CSW/BTW/MSW se nombran con el código que usa el inventario o el catálogo; la serie de los minibreakers no está en los catálogos revisados y por eso la línea se llama «Minibreaker WEG».
