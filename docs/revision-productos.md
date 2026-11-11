# Revisión producto por producto

Estado tras la revisión del catálogo (114 líneas, 445 referencias). Se generó con `docs/inventario/generar_revision.py`.

- **Imagen correcta**: «sí» = foto del producto y de la marca; «aprox.» = foto oficial de la misma familia y marca pero no de ese modelo (se reemplaza al conseguir la foto exacta); «no» = sin foto o con foto retirada. Las fotos de referencias nuevas de distribuidores colombianos se revisaron una por una (sin marca de agua, sin mostrador ni bodega); su origen exacto está en `docs/licencias-imagenes.md`.
- **Especificaciones completas**: toda referencia trae tipo y marca; la serie se agrega cuando sale del nombre o del código de la referencia. Los datos técnicos son los de la descripción del inventario y, para los WEG, se contrastaron con el catálogo oficial (ver `docs/correcciones-especificaciones.md`).
- **Ficha técnica funciona**: «Imprimir ficha» abre un PDF con logo, nombre, imagen, referencia, tabla de especificaciones, SKU y contacto. Se comprobó en Chrome generando el PDF de cada una de las líneas publicadas (todas válidas, una página, con logo, tabla y contacto). **Safari** (macOS/iOS) no se pudo probar desde este equipo: usa la misma hoja de estilos de impresión (`@media print`, `-webkit-print-color-adjust`), pero hay que abrir una ficha en Safari antes de la sustentación.

Resumen: imagen sí 89 · aprox. 20 · no 5 · especificaciones completas 78 · parciales 29.

| Línea | Referencias | Imagen correcta | Especificaciones completas | Ficha técnica funciona | Cambios hechos |
|---|---|---|---|---|---|
| Arrancador directo WEG DLW | 11 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «Arrancadores directos»; tipo/serie en especificaciones |
| Contactor Siemens 3RT2 | 1 | no (sin foto oficial; línea oculta) | sí (tipo, marca, serie y datos técnicos) | sí | antes «Contactores»; tipo/serie en especificaciones |
| Contactor WEG CWB con bobina DC | 3 | aprox. (foto de la misma familia y marca, no de ese modelo exacto) | sí (tipo, marca, serie y datos técnicos) | sí | antes «Contactores»; etiquetas de variante corregidas; tipo/serie en especificaciones |
| Contactor WEG CWBC para banco de capacitores | 6 | aprox. (foto de la misma familia y marca, no de ese modelo exacto) | sí (tipo, marca, serie y datos técnicos) | sí | antes «Contactores»; etiquetas de variante corregidas; tipo/serie en especificaciones |
| Contactor WEG CWB | 11 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «Contactores»; etiquetas de variante corregidas; tipo/serie en especificaciones |
| Mini contactor auxiliar WEG CWCA0 | 1 | aprox. (foto de la misma familia y marca, no de ese modelo exacto) | sí (tipo, marca, serie y datos técnicos) | sí | antes «Contactores»; etiquetas de variante corregidas; tipo/serie en especificaciones |
| Mini contactor WEG CWC | 9 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «Contactores»; etiquetas de variante corregidas; tipo/serie en especificaciones |
| Accesorios para contactores WEG CWB | 3 | aprox. (foto de la misma familia y marca, no de ese modelo exacto) | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Accesorios de maniobra» + «Contactores»; etiquetas de variante corregidas; tipo/serie en especificaciones |
| Relé térmico WEG RW17 | 9 | aprox. (foto de la misma familia y marca, no de ese modelo exacto) | sí (tipo, marca, serie y datos técnicos) | sí | antes «Relés de control y protección» + «Relés térmicos»; etiquetas de variante corregidas; tipo/serie en especificaciones |
| Relé térmico WEG RW27 | 13 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «Relés térmicos»; etiquetas de variante corregidas; tipo/serie en especificaciones |
| Relé térmico WEG RW67 | 6 | aprox. (foto de la misma familia y marca, no de ese modelo exacto) | sí (tipo, marca, serie y datos técnicos) | sí | antes «Relés térmicos»; tipo/serie en especificaciones |
| Relé electrónico de protección WEG RWB40 | 1 | aprox. (foto de la misma familia y marca, no de ese modelo exacto) | sí (tipo, marca, serie y datos técnicos) | sí | antes «Relés de control y protección»; etiquetas de variante corregidas; tipo/serie en especificaciones |
| Relé temporizador WEG RTW17 | 3 | sí | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Relés de control y protección»; etiquetas de variante corregidas; tipo/serie en especificaciones |
| Relé vigilante de tensión WEG RMW17 | 3 | aprox. (foto de la misma familia y marca, no de ese modelo exacto) | sí (tipo, marca, serie y datos técnicos) | sí | antes «Relés de control y protección»; etiquetas de variante corregidas; tipo/serie en especificaciones |
| Relé vigilante WEG RNW | 2 | aprox. (foto de la misma familia y marca, no de ese modelo exacto) | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Relés de control y protección»; etiquetas de variante corregidas; tipo/serie en especificaciones |
| Guardamotor WEG MPW18 | 9 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «Guardamotores»; etiquetas de variante corregidas; tipo/serie en especificaciones |
| Guardamotor WEG MPW40 | 11 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «Guardamotores»; etiquetas de variante corregidas; tipo/serie en especificaciones |
| Accesorios para guardamotores WEG MPW | 8 | aprox. (foto de la misma familia y marca, no de ese modelo exacto) | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Guardamotores» + «Pulsadores, pilotos y selectores»; tipo/serie en especificaciones |
| Paradas de emergencia WEG CSW | 4 | aprox. (foto de la misma familia y marca, no de ese modelo exacto) | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Pulsadores, pilotos y selectores»; tipo/serie en especificaciones |
| Selectores WEG CSW | 6 | aprox. (foto de la misma familia y marca, no de ese modelo exacto) | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Pulsadores, pilotos y selectores»; etiquetas de variante corregidas; tipo/serie en especificaciones |
| Pilotos luminosos WEG CSW | 11 | aprox. (foto de la misma familia y marca, no de ese modelo exacto) | sí (tipo, marca, serie y datos técnicos) | sí | antes «Pulsadores, pilotos y selectores»; tipo/serie en especificaciones |
| Bloques de contacto WEG CSW | 2 | aprox. (foto de la misma familia y marca, no de ese modelo exacto) | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Pulsadores, pilotos y selectores»; etiquetas de variante corregidas; tipo/serie en especificaciones |
| Pulsadores WEG CSW | 13 | aprox. (foto de la misma familia y marca, no de ese modelo exacto) | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Accesorios de maniobra» + «Pulsadores, pilotos y selectores»; etiquetas de variante corregidas; tipo/serie en especificaciones |
| Bornes y accesorios WEG BTW | 26 | aprox. (foto de la misma familia y marca, no de ese modelo exacto) | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Accesorios de maniobra» + «Borneras y accesorios»; tipo/serie en especificaciones |
| Contacto auxiliar Siemens 3RH2 | 1 | no (sin foto oficial; línea oculta) | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Accesorios de maniobra»; etiquetas de variante corregidas; tipo/serie en especificaciones |
| Seccionador WEG MSW | 2 | aprox. (foto de la misma familia y marca, no de ese modelo exacto) | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Accesorios de maniobra»; etiquetas de variante corregidas; tipo/serie en especificaciones |
| Minibreaker WEG | 31 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Minibreakers»; etiquetas de variante corregidas; tipo en especificaciones |
| Totalizador WEG DWB | 15 | aprox. (foto de la misma familia y marca, no de ese modelo exacto) | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Totalizadores»; etiquetas de variante corregidas; tipo/serie en especificaciones |
| Variador de frecuencia WEG CFW100 | 7 | sí | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Variador CFW100»; tipo/serie en especificaciones |
| Variador de frecuencia WEG CFW300 | 8 | sí | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Variador CFW300»; tipo/serie en especificaciones |
| Variador de frecuencia WEG CFW500 | 5 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «Variador CFW500»; tipo/serie en especificaciones |
| Módulo de expansión de entradas y salidas WEG | 1 | aprox. (foto de la misma familia y marca, no de ese modelo exacto) | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Accesorios para variadores»; etiquetas de variante corregidas; tipo en especificaciones |
| Variador de frecuencia WEG CFW900 | 2 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «Variador CFW900»; tipo/serie en especificaciones |
| Arrancador suave WEG SSW05 | 3 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «Arrancador suave SSW05»; tipo/serie en especificaciones |
| Arrancador suave WEG SSW07 | 2 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «Arrancador suave SSW07»; tipo/serie en especificaciones |
| Motor monofásico WEG W01 semiabierto | 11 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «W01 monofásico semiabierto»; tipo/serie en especificaciones |
| Motor monofásico WEG W01 cerrado | 11 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «W01 monofásico cerrado»; tipo/serie en especificaciones |
| Motor monofásico WEG W12 | 4 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «W12 monofásico»; tipo/serie en especificaciones |
| Motor monofásico WEG W22 | 8 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «W22 monofásico»; tipo/serie en especificaciones |
| Motor trifásico WEG W21 IEC · carcasa aluminio | 29 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «W21 IEC · carcasa aluminio»; tipo/serie en especificaciones |
| Motor trifásico WEG W22 IEC · carcasa hierro | 23 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «W22 IEC · carcasa hierro»; tipo/serie en especificaciones |
| Motor trifásico WEG W22 NEMA Premium | 2 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «W22 NEMA Premium Efficiency»; tipo/serie en especificaciones |
| Motor WEG W22 a prueba de explosión | 6 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «W22 a prueba de explosión»; tipo/serie en especificaciones |
| Motor trifásico WEG de bobinado | 1 | aprox. (foto de la misma familia y marca, no de ese modelo exacto) | sí (tipo, marca y datos técnicos) | sí | tipo en especificaciones |
| Motor trifásico ABB | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Motores ABB»; tipo en especificaciones |
| Motor trifásico Nidec US Motors | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Motores Nidec US Motors»; tipo en especificaciones |
| Motor trifásico Nord | 3 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Motores Nord»; tipo en especificaciones |
| Motor monofásico Match Drive | 2 | no (sin foto oficial; línea oculta) | sí (tipo, marca y datos técnicos) | sí | antes «Motores Match Drive»; tipo en especificaciones |
| Motorreductor WEG Cestari WCG01 · sinfín y corona | 2 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «Motorreductor sinfín WCG01»; tipo/serie en especificaciones |
| Reductor helicoidal WEG Cestari WCG20 | 6 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «Reductor helicoidal WCG20»; tipo/serie en especificaciones |
| PLC WEG PLC500 | 0 | sí | línea bajo pedido (sin referencias) | sí | nombre |
| PLC WEG PLC200 | 0 | no (sin foto oficial; línea oculta) | línea bajo pedido (sin referencias) | sí | nombre |
| Extractores AirMax GL y TTT500 | 0 | sí | línea bajo pedido (sin referencias) | sí | nombre |
| Cable THHN/THWN-2 Centelsa | 0 | sí | línea bajo pedido (sin referencias) | sí | nombre |
| Campana LED Sylvania START Highbay | 0 | sí | línea bajo pedido (sin referencias) | sí | nombre |
| Compresor de pistón vertical ABAC PRO | 0 | sí | línea bajo pedido (sin referencias) | sí | nombre |
| Instrumentos de medición Fluke | 0 | sí | línea bajo pedido (sin referencias) | sí | nombre |
| Interruptor de pedal Xurui XF | 3 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «Inversores de giro, estaciones de mando e interruptores»; tipo/serie en especificaciones |
| Interruptor on/off Margirius | 2 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Inversores de giro, estaciones de mando e interruptores»; tipo en especificaciones |
| Inversor de giro Merz | 4 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Inversores de giro, estaciones de mando e interruptores»; tipo en especificaciones |
| Inversor de giro EBCHQ | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Inversores de giro, estaciones de mando e interruptores»; tipo en especificaciones |
| Estación de mando de pulsadores y emergencia | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Inversores de giro, estaciones de mando e interruptores»; tipo en especificaciones |
| Selector de muletilla EBCHQ de 3 posiciones | 1 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «Inversores de giro, estaciones de mando e interruptores»; tipo/serie en especificaciones |
| Arrancador on/off con pulsador EBCHQ | 1 | sí | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Inversores de giro, estaciones de mando e interruptores»; tipo en especificaciones |
| Protector electrónico de voltaje trifásico Magom | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Inversores de giro, estaciones de mando e interruptores»; tipo en especificaciones |
| Base para relé de 8 pines | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Relés enchufables y bases»; tipo en especificaciones |
| Base para relé de 11 pines Telemecanique | 1 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «Relés enchufables y bases»; tipo/serie en especificaciones |
| Base para relé de 11 pines | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Relés enchufables y bases»; tipo en especificaciones |
| Mini relé de 14 pines Relpol | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Relés enchufables y bases»; tipo en especificaciones |
| Mini relé de 14 pines | 2 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Relés enchufables y bases»; tipo en especificaciones |
| Mini relé slim de 5 pines | 2 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Relés enchufables y bases»; tipo en especificaciones |
| Relé de 8 pines EBCHQ | 2 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Relés enchufables y bases»; tipo en especificaciones |
| Final de carrera Xurui XZ-8 | 1 | sí | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Finales de carrera y micro switches»; tipo/serie en especificaciones |
| Final de carrera de varilla ajustable | 1 | sí | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Finales de carrera y micro switches»; tipo en especificaciones |
| Micro switch KAP | 1 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «Finales de carrera y micro switches»; tipo/serie en especificaciones |
| Micro switch Xurui | 3 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «Finales de carrera y micro switches»; tipo/serie en especificaciones |
| Baliza multivoltaje tricolor SPK | 1 | sí | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Balizas»; tipo en especificaciones |
| Aislador de barraje SM | 2 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «Aisladores de barraje y distribución»; tipo/serie en especificaciones |
| Aislador de barraje escalonado | 1 | sí | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Aisladores de barraje y distribución»; tipo en especificaciones |
| Bloque de distribución Onka | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Aisladores de barraje y distribución»; tipo en especificaciones |
| Transformador de control Wattio | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Transformadores de control»; tipo en especificaciones |
| Controlador de temperatura Autonics | 2 | sí | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Temporizadores, controladores de temperatura y horómetros»; tipo/serie en especificaciones |
| Temporizador estrella-triángulo Autonics | 2 | sí | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Temporizadores, controladores de temperatura y horómetros»; tipo/serie en especificaciones |
| Temporizador Autonics | 2 | sí | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Temporizadores, controladores de temperatura y horómetros»; tipo/serie en especificaciones |
| Horómetro Autonics LE8N | 1 | sí | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Temporizadores, controladores de temperatura y horómetros»; tipo/serie en especificaciones |
| Programador semanal EBCHQ para riel DIN | 1 | sí | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Temporizadores, controladores de temperatura y horómetros»; tipo en especificaciones |
| Termostato Plastim | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Temporizadores, controladores de temperatura y horómetros»; tipo en especificaciones |
| Bombillo halógeno H3 | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Bombillos LED y ahorradores»; tipo en especificaciones |
| Bombillo LED E27 Sylvania | 2 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Bombillos LED y ahorradores»; tipo en especificaciones |
| Bombillo LED E27 Lexmana | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Bombillos LED y ahorradores»; tipo en especificaciones |
| Bombillo LED E27 Excelite | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Bombillos LED y ahorradores»; tipo en especificaciones |
| Bombillo LED RGB GU10 Mercury | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Bombillos LED y ahorradores»; tipo en especificaciones |
| Panel LED circular de incrustar Mercury | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Paneles LED»; tipo en especificaciones |
| Panel LED circular de sobreponer Sylvania | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Paneles LED»; tipo en especificaciones |
| Panel LED cuadrado de incrustar | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Paneles LED»; tipo en especificaciones |
| Panel LED redondo de incrustar | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Paneles LED»; tipo en especificaciones |
| Lámpara de alumbrado público VCP | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Reflectores LED»; tipo en especificaciones |
| Reflector LED Titanium | 1 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «Reflectores LED»; tipo/serie en especificaciones |
| Reflector LED Élite | 1 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «Reflectores LED»; tipo/serie en especificaciones |
| Spot LED Karluz | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Spots, bases y emergencia»; tipo en especificaciones |
| Cinta aislante PVC 3M Temflex | 4 | sí | sí (tipo, marca, serie y datos técnicos) | sí | antes «Cintas aislantes»; tipo/serie en especificaciones |
| Cinta de enmascarar Cellux | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Insumos, herramientas y repuestos»; tipo en especificaciones |
| Batería sellada Magna | 1 | no (foto retirada: era de otra batería; falta fotografiar la 12 V 5 Ah) | sí (tipo, marca y datos técnicos) | sí | antes «Insumos, herramientas y repuestos»; tipo en especificaciones |
| Tapón macho Swagelok | 1 | sí | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Insumos, herramientas y repuestos»; tipo/serie en especificaciones |
| Interruptor inteligente Ilumax | 2 | sí | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Interruptores y tomas inteligentes»; tipo en especificaciones |
| Tapa para toma Leviton | 4 | sí | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Tomas, clavijas y tapas Leviton»; tipo en especificaciones |
| Toma doble Leviton | 2 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Tomas, clavijas y tapas Leviton»; tipo en especificaciones |
| Clavija Leviton | 3 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Tomas, clavijas y tapas Leviton»; tipo en especificaciones |
| Toma industrial Leviton | 2 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Tomas, clavijas y tapas Leviton»; tipo en especificaciones |
| Amperímetro análogo Chint | 1 | sí | parcial (tipo, marca y referencia; faltan datos técnicos que la empresa no entregó) | sí | antes «Medidores e instrumentos de panel»; tipo en especificaciones |
| Analizador de red multifunción Nippen | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Medidores e instrumentos de panel»; tipo en especificaciones |
| Motobomba IHM | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Motobombas»; tipo en especificaciones |
| Caja reductora WEG Cestari | 3 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Cajas reductoras WEG-Cestari»; tipo en especificaciones |
| Reductor Siti | 1 | sí | sí (tipo, marca y datos técnicos) | sí | antes «Reductores de otras marcas»; tipo en especificaciones |
