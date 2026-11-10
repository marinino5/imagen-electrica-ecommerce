# Pendientes para la empresa (Fase 1B)

Referencias que **no se publicaron** y qué hace falta para publicarlas. Nada se completó con datos adivinados ni con SKU provisionales.

- Filas en la hoja «Productos nuevos»: 292. Publicadas: **94**. Con estado «Pendiente»: **67**. «Listo» sin publicar por falta de foto: **131**.
- **Precios**: ninguna referencia nueva tiene precio; todas salen en «Cotizar». Llenar la columna `precio_con_iva` de `precios-pendientes.csv` y cargarla con `php artisan catalog:load-prices docs/inventario/precios-pendientes.csv` (pasa a compra en línea las que tengan precio y stock).

## 1. Estado «Pendiente» (problemas en el Excel)

| SKU | Descripción | Marca | Cantidad | Problema |
|---|---|---|---|---|
| — | BOBINA PARA CONTACTOR CWB9…38 110 V | WEG | 2 | Sin código (SKU) |
| — | BOBINA PARA CONTACTOR CWB9…38 220 V | WEG | 27 | Sin código (SKU) |
| — | BOBINA PARA CONTACTOR CWB9…38 24 V | WEG | 10 | Sin código (SKU) |
| — | BOBINA PARA CONTACTOR CWB9…38 440 V | WEG | 8 | Sin código (SKU) |
| REV0011 | AMARILLO 220V | Sin marca | 21 | Descripción incompleta (falta tipo y marca) |
| — | AZUL 110V | Sin marca | 4 | Sin código (SKU); Descripción incompleta (falta tipo y marca) |
| REV0008 | BLANCO 220V | Sin marca | 4 | Descripción incompleta (falta tipo y marca) |
| — | MINI PILOTO SALZER 110V BLANCO | Salzer | 26 | Sin código (SKU) |
| — | MINI PILOTO SALZER 110V ROJO | Salzer | 3 | Sin código (SKU) |
| — | MINI PILOTO VCP 110V BLANCO | Vcp | 20 | Sin código (SKU) |
| — | MINI PILOTO VCP 110V CARCASA RAYADA | Vcp | 16 | Sin código (SKU) |
| — | MINI PILOTO VCP 220V CABEZA BLANCA | Vcp | 19 | Sin código (SKU) |
| — | MINI PILOTO VCP 220V CARCASA TRANSPARENTE | Vcp | 9 | Sin código (SKU) |
| — | PULSADOR  VERDE 1 NA LINEA ANTIGUA MOELLER | Moeller | 3 | Sin código (SKU) |
| — | ROJO 220V | Sin marca | 7 | Sin código (SKU); Descripción incompleta (falta tipo y marca) |
| — | ROJO 24V | Sin marca | 6 | Sin código (SKU); Descripción incompleta (falta tipo y marca) |
| PEV0002 | VERDE 110V | Sin marca | 9 | Descripción incompleta (falta tipo y marca) |
| LA42DWQ-22 | POTENCIOMETRO 10K SPK | Spk | 7 | La empresa lo marcó: verificar descrip |
| YPN-901/32 | INVERSOR DE GIRO 3 POLOS 32 AMP 3 POS PAKOSAN | Pakosan | 1 | La empresa lo marcó: verificar descrip |
| 76075 | INVERSOR DE GIRO 3 POSICIONES 63 A 4 POLOS OPAS | Opas | 1 | Código repetido en otra referencia |
| 76075 | RELE DE ALTERNANCIA PARA 2 MOTORES RM-PCR1 | Sin marca | 1 | Código repetido en otra referencia |
| A600P600 | SELECTOR 4 POSICIONES PALANCA BRETER | Breter | 1 | La empresa lo marcó: verificar descrip |
| — | BASE PARA RELEVO 8 PINES | Sin marca | 8 | Sin código (SKU) |
| — | MINI RELEVO 14 PINES 24 VDC | Sin marca | 2 | Sin código (SKU) |
| 672568 | RELE MODULAR GENERAL ELECTRIC | General Electric | 4 | La empresa lo marcó: buscar |
| — | RELEVO 11 PINES 220 VAC VRPL11 | Sin marca | 2 | Sin código (SKU) |
| — | RELEVO 11 PINES 24 V TRP 6932 | Sin marca | 1 | Sin código (SKU) |
| — | RELEVO 11 PINES 24 VAC VRPL11 | Sin marca | 2 | Sin código (SKU) |
| — | RELEVO 8 PINES 110 VAC VRPL8 | Sin marca | 1 | Sin código (SKU) |
| — | RELEVO 8 PINES 220 VAC VRPL8 | Sin marca | 4 | Sin código (SKU) |
| — | RELEVO 8 PINES 24 VAC VRPL8-24 | Sin marca | 2 | Sin código (SKU) |
| — | RELEVO 8 PINES 24 VDC | Sin marca | 10 | Sin código (SKU) |
| — | RELEVO 8 PINES 24 VDC RELPOL | Relpol | 2 | Sin código (SKU) |
| Z-15GQ21-B | FINAL CARRERA  SPK | Spk | 15 | La empresa lo marcó: verificar descrip |
| LXJMH-8108 | FINAL DE CARRERA DELIXI | Delixi | 2 | La empresa lo marcó: verificar descrip |
| ME-8107 | FINAL DE CARRERA IMP ME-8107 | Imp | 1 | La empresa lo marcó: verificar codigo |
| TZ8111 | FINAL DE CARRERA IMP ME-8111 | Imp | 2 | La empresa lo marcó: verificar descrip |
| XCK-M115 | FINAL DE CARRERA SPK  DE RODILLO | Spk | 3 | La empresa lo marcó: verificar descrip |
| XCK-J10541 | FINAL DE CARRERA SPK  DE RODILLO | Spk | 4 | La empresa lo marcó: verificar descrip |
| TZ-8104 | FINAL DE CARRERA SPK 5 AMP 250V ROLDANA | Spk | 1 | La empresa lo marcó: verificar descrip |
| TZ-8169 | FINAL DE CARRERA SPK 5 AMP 250V VARILLA LARGA | Spk | 9 | La empresa lo marcó: verificar descrip |
| XZ-8/107 | FINAL DE CARRERA SPK 5 AMP 250V VARILLA POR PARTE XURUI | Spk | 1 | Código repetido en otra referencia; La empresa lo marcó: verificar descrip |
| XZ-8/107 | FINAL DE CARRERA VARILLA LISA MULTI DIRECCINAL XURUI | Xurui | 4 | Código repetido en otra referencia |
| V-155-1C25 | MICRO SWTICH XURUI | Xurui | 3 | La empresa lo marcó: verificar descrip |
| SM-40 SPK | AISLADOR BARRAJE 40 MM SPK 12KV | Spk | 6 | La empresa lo marcó: verificar codigo |
| — | AISLADOR BARRAJE SM-25 25 MM 6KV 500LB VOLTIO | Sin marca | 5 | Sin código (SKU); La empresa lo marcó: verificar codigo |
| 34085 | AISLADOR ESCALONADO H60XL90A20 MM X 2 UND | Sin marca | 4.5 | Cantidad decimal (4,5): confirmar unidades |
| 2020-01-07 00:00:00 | CINTA AISLANTE NEGRA PVC SOCOL | Socol | 1 | Código dañado por Excel (aparece como fecha) |
| 06-CINVE01 | CINTA VERDE TOPEX | Topex | 1 | La empresa lo marcó: verificar descrip |
| 70915 | INDICADOR DE PANEL NIPPEN | Nippen | 1 | La empresa lo marcó: verificar descrip |
| — | BRIDA B14 T 63 W11 | WEG | 1 | Sin código (SKU) |
| — | BRIDA B14 T 63 W21 | WEG | 1 | Sin código (SKU) |
| — | BRIDA B14 T 80 W21 | WEG | 1 | Sin código (SKU) |
| — | BRIDA B5 T 63 W21-22 | WEG | 2 | Sin código (SKU) |
| — | BRIDA B5 T 71 W21-22 | WEG | 3 | Sin código (SKU) |
| — | BRIDA B5 T 80 W11 | WEG | 2 | Sin código (SKU) |
| 801383 | MOTORREDUCTOR 5 HP R:54 SAL:32 RPM C58 | Sin marca | — | Sin cantidad |
| 51202 | — | Sin marca | 7 | Sin descripción |
| 17430 | — | Sin marca | 5 | Sin descripción |
| 17460 | — | Sin marca | 3 | Sin descripción |
| 17452 | — | Sin marca | 2 | Sin descripción |
| 17507 | — | Sin marca | 10 | Sin descripción |
| 17523 | — | Sin marca | 4 | Sin descripción |
| 17525 | — | Sin marca | 10 | Sin descripción |
| CMC101 | — | Sin marca | 5 | Sin descripción; La empresa lo marcó: buscar |
| 91392 | BOBINA ELECTROVALVULA 100VAC STNC | Stnc | 4 | La empresa lo marcó: verificar proveedor  |
| 99226 | CORTAFRIO EBCHQ | Ebchq | 1 | La empresa lo marcó: verificar |

## 2. «Listo» sin foto

Referencias con datos completos para las que no apareció una foto del fabricante o de un distribuidor. Las fuentes revisadas están en `docs/inventario/fotos-pendientes.json`.

| SKU | Descripción | Marca | Cantidad |
|---|---|---|---|
| M22-DR-G | PULSADOR  1 NA LINEA ANTIGUA MOELLER CABEZA GRIS | Moeller | 2 |
| 51790 | POTENCIOMETRO 1K EBCHQ | Ebchq | 2 |
| 51902 | POTENCIOMETRO 2K VISHAY 10 VUELTAS | Vishay | 1 |
| 51794 | POTENCIOMETRO 5K EBCHQ | Ebchq | 3 |
| CPPR05K | POTENCIOMETRO 5K EMAS | Emas | 2 |
| 25506 | CAJA STAR STOP 2NA+1NC 3 BOTONES EBCHQ | Ebchq | 4 |
| SA-TB5 | CAJA TRIANGULAR 5H 22 MM ALUMINIO AUTONICS | Autonics | 4 |
| 25778 | ESTACION DE MANDO 4 PULS+1 EMER 4NO+1NC IP54 PG13.5 | Sin marca | 2 |
| 25780 | ESTACION DE MANDO 5 PULS+1 EMER 5NO+1NC IP54 PG13.5 | Sin marca | 3 |
| 25761 | ESTACION DE MANDO 6 PULS 6NO 4BL-2NE IP54 PG13.5 | Sin marca | 1 |
| XT-21A | INTERRUPTOR CODILLO ON/FF 15 AMP 250VAC POS FIJA 4 TERM | Sin marca | 7 |
| XT-23BF | INTERRUPTOR CODILLO ON/OFF 15 AMP 250 VAC 6 TERM RETORNO | Sin marca | 11 |
| SFMP-1 | INTERRUPTOR DE PEDAL 15 AMP 250 V | Sin marca | 1 |
| 30315 | INTERRUPTOR ON/OFF 20 AMP 2 POS 6 TERM CONMUTA | Sin marca | 1 |
| CS-102 | INTERRUPTOR STAR/STOP 30 AMP 250 VAC | Sin marca | 3 |
| BS 5419 | INVERSOR DE GIRO 3 POSICIONES 25A 7 POLOS VERDE  BERTER | Berter | 1 |
| EP-32A | INVERSOR DE GIRO 3 POSICIONES 32A 4 POLOS THCK | Thck | 1 |
| CR830AN | INVERSOR DE GIRO 3 POSICIONES CON CARCASA MARGIRIUS 30A 220/440V | Margirius | 3 |
| BS216B | MOTOR STARTER ON/OFF PUSH BOTTON SPK 15 AMP | Spk | 2 |
| BS230B | MOTOR STARTER ON/OFF PUSH BOTTON SPK 20 AMP | Spk | 6 |
| KH-305 | MOTOR STARTER ON/OFF PUSH BOTTON SPK 30 AMP | Spk | 3 |
| 25600 | PULSADOR PARADA EMRGENCIA C/CAJA PLASTICA EBCHQ | Ebchq | 3 |
| TA24A25 | RELE DE ESTADO SOLIDO OPTEC | Optec | 1 |
| 51616 | BASE PARA MINI RELEVO 14 PINES RELPOL | Relpol | 19 |
| PF113A-E | BASE PARA RELEVO 11 PINES | Sin marca | 11 |
| 51312 | BASE PARA RELEVO 11 PINES RELPOL | Relpol | 3 |
| 51218 | BASE PARA RELEVO 8 PINES RELPOL | Relpol | 4 |
| 46976 | MINI RELEVO 5 PINES 230 VAC | Sin marca | 1 |
| MY2N | MINI RELEVO 8 PINES 110 VAC | Sin marca | 2 |
| 47026 | MINI RELEVO 8 PINES 120 VAC | Sin marca | 6 |
| 47028 | MINI RELEVO 8 PINES 220VAC | Sin marca | 9 |
| 47024 | MINI RELEVO 8 PINES 24 VAC | Sin marca | 3 |
| CR-U125DC3 | RELEVO 11 PINES 125 VDC ABB | Abb | 3 |
| 47300 | RELEVO 8 PINES 24 VAC RELPOL | Relpol | 15 |
| XZ7-110 | FINAL DE CARRERA 10 AMP 250VAC PIVOTE LARGO | Sin marca | 5 |
| DM1F53Z11 | FINAL DE CARRERA PALANCA AJUSTABLE ROLDANA METAL COMEPI | Comepi | 1 |
| HY-R704-2W | FINAL DE CARRERA PALANCA CORTA ROLDANA ESCUALIZABLE HANYOUNG | Hanyoung | 2 |
| HY-R704A | FINAL DE CARRERA PALANCA CORTA ROLDANA METAL HANYOUNG | Hanyoung | 3 |
| HY-R704C | FINAL DE CARRERA PALANCA LARGA ROLDANA METAL HANYOUNG | Hanyoung | 8 |
| DM1F12Z11 | FINAL DE CARRERA VASTAGO ROLDANA METALICO 1NA+1NC COMEPI | Comepi | 1 |
| HY-L707C | MICRO SWTICH HANYOUNG PALANCA LARGA 15 AMP 125V | Hanyoung | 7 |
| XV-155-1C25 | MICRO SWTICH PALANCA CORTA ROLDANA 16A/250V CONM | Sin marca | 7 |
| IF5G220ZM05 | BALIZA FIJA INTERMITENTE VERDE CON BUZZER | Sin marca | 2 |
| 16120 | BALIZA LICUADORA VERDE EBCHQ | Ebchq | 1 |
| 50949 | BALIZA ROJA CON BUZZER SPK | Spk | 3 |
| 50945 | BALIZA VERDE SIN BUZZER SPK | Spk | 2 |
| 34076 | AISLADOR DE BARRAJE 76 MM EBCHQ | Ebchq | 3 |
| 34092 | AISLADOR ESCALONADO H100XL230A30 MM X 2 UND | Sin marca | 1 |
| 34090 | AISLADOR ESCALONADO H148XL164A30 MM X 2 UND | Sin marca | 5 |
| TNC 47K | TRANSFORMADOR IN:110V A 9-6-0V 2AMP | Sin marca | 1 |
| 51310 | CONTADOR DIGITAL 48X48 TCM7CM  TMCON | Tmcon | 2 |
| 69020 | CONTROL DE TEMPERATURA DIGITAL ON OFF PID 96X96 AUTONICS LC4J | Autonics | 1 |
| FP-400-201000 | CONTROLADOR DE TEMPERATURA ACUSHNET | Acushnet | 1 |
| TCD96D | CONTROLADOR DE TEMPERATURA ANALOGO/DIGITAL 110/220V DAXUS | Daxus | 1 |
| TC538CX | CONTROLADOR DE TEMPERATURA MARCA SELEC | Selec | 2 |
| TF9-M1 | CONTROLADOR DE TEMPERATURA TF 110/220V SOMMV | Sommv | 1 |
| TC4Y | HOROMETRO DIGITAN AUTONICS | Autonics | 1 |
| 51251 | TEMPORIZADOR ANALOGICO DOBLE 60 MIN 220V EPK | Epk | 5 |
| LE3S | TEMPORIZADOR ANALOGO/DIGITAL AUTONICS | Autonics | 2 |
| 51135 | TEMPORIZADOR DIGITAL 0.01S-99H 48X48 SOMMV HF4-RB41A-P | Sommv | 3 |
| ASY-3D | TEMPORIZADOR DIGITAL 220 VAC SPK | Spk | 1 |
| ELT63 | BOMBILLO AHORRADOR 200W DANILUX 220V | Danilux | 2 |
| ESF-9W | BOMBILLO AHORRADOR 9W E27 LEXMANA | Lexmana | 4 |
| LB658-16 | BOMBILLO LED 16W LEXMANA E27 | Lexmana | 2 |
| 1222 | BOMBILLO LED 20W ILUMAX E27 | Ilumax | 5 |
| 423084 | BOMBILLO LED 30W ILUMAX  E27 | Ilumax | 2 |
| LB2066 | BOMBILLO LED 5W LEXMANA E27 | Lexmana | 36 |
| GU10-5W | BOMBILLO LED 5W SKT GU10 SOLED | Soled | 1 |
| 73321 | BOMBILLO LED 5W VIRI BRIGHT E27 | Viri Bright | 5 |
| 803526 | BOMBILLO LED 60W ILUMAX E27 | Ilumax | 2 |
| LTL52-7 | BOMBILLO LED 7W LEXMANA GU10 | Lexmana | 7 |
| H1109 | BOMBILLO LED 9W SKT GU10 ECOSPOT | Ecospot | 1 |
| DAIRU | BOMBILLO LED REGULABLE 4W E14 | Sin marca | 2 |
| 41522-4 | BOMOBILLO 1000W PHILLIPS MH1000/U | Phillips | 2 |
| 3066 | CINTA LED NEON KARLUZ 5 MTS LILA | Karluz | 3 |
| SL002 | PROYECTOR LED NAVIDAD LEXMANA | Lexmana | 2 |
| 2141 | LUMINARIA ALUMBRADO PUBLICO 120W CON PANEL SOLAR | Ilumax | 4 |
| RL002-60 | LUMINARIA ALUMBRADO PUBLICO 60W CON PANEL SOALR | Lexmana | 1 |
| RL002-90W | LUMINARIA ALUMBRADO PUBLICO 90W CON PANEL SOLAR | Lexmana | 2 |
| ISL89 | PANEL SOLAR PARA CINTA LED 24V MERCURY | Mercury | 1 |
| LG501 | REFLECTOR LED SOLAR 100W LEXMANA | Lexmana | 1 |
| 2168 | REFLECTOR LED SOLAR 200W | Sin marca | 2 |
| LPS506V | REFLECTOR LED SOLAR 300W CON PANEL INDP | Komled | 1 |
| B0Z2 | LAMPARA LED TORTUGA 12W EXCELITE | Excelite | 1 |
| 1993 | PANEL CIRCULAR LED 24W INCRUSTAR 12" ILUMAX | Ilumax | 14 |
| C1321L01 | PANEL CIRCULAR S/P 18W 8" LUMEK | Lumek | 1 |
| LED18WC | PANEL LED CUADRADA 18W SOBREPONER SIGLA | Sigla | 1 |
| 56752-021 | LUMINARIA TRAMONTINA 200W | Tramontina | 1 |
| ELT34 | REFLECTOR LED 150W LUZ BLANCA ÉLITE | Élite | 3 |
| ELT35 | REFLECTOR LED 200W LUZ BLANCA ÉLITE | Élite | 8 |
| XLFL-20W | REFLECTOR LED 20W LEXMANA | Lexmana | 4 |
| MP2.5-1 | BASE GU10 DE INCRUSTAR C:BLANCA DANILUX 2.5" | Danilux | 1 |
| MP3-1 | BASE GU10 DE INCRUSTAR C:BLANCA DANILUX 3" | Danilux | 1 |
| VEL0310 | LUMINARIA EMERGENCIA LED | Sin marca | 1 |
| SEN-INST | SENSOR CON INTERRUPTOR LEXMANA IP20 | Lexmana | 2 |
| R006 | SPOT LED 10W C: BLANCA LUZ BLANCA 6500K TRACK LIGHT | Sin marca | 3 |
| CINTA23 | CINTA #23 SCOTCH 3M 19MM X 9.15 MTS | 3M | 1 |
| CIN136 | CINTA AISLANTE AZUL PVC CILES | Ciles | 3 |
| CIN18M-NG | CINTA AISLANTE NEGRA PVC 18 MTS | Sin marca | 5 |
| C10MTR-RJ | CINTA AISLANTE ROJA PVC 3M 10 MTS | 3M | 4 |
| 12200 | CINTA DE ENMASCARAR SOCOL 24MM X 40 MTS | Socol | 1 |
| 12400 | CINTA DE ENMASCARAR SOCOL 48MM X 40 MTS | Socol | 1 |
| CINTA33 | CINTA SUPER #33 SCOTCH 3M 19MM X 21.1 MTS | 3M | 9 |
| BFT-25 | CONECTOR VARILLA PUESTA TIERRA 1/4 | Sin marca | 25 |
| 2082 | TOMA CORRIENTE INTELIGENTE ILUMAX | Ilumax | 4 |
| WID0538 | TOMA DOBLE GRADO HOSPITAL +TIERRA 15 AMP 125V NARANJA | Leviton | 2 |
| WID0005 | TOMA SENCILLA CL CREMA 20 AMP 250V NEMA 6-20R PATA TRABADA | Leviton | 14 |
| MT4W-DA-41 | AMPERIMETRO DIGITAL DC 110/220V 96X48 | Sin marca | 3 |
| PM8908C | DETECTOR DE VOLTAJE NCV 12-1000VAC INDICADOR LED Y SONORO | Sin marca | 1 |
| 54200 | TRANSFORMADOR DE NUCLEO DE CORRIENTE 200/5A EBCHQ | Ebchq | 3 |
| 54600 | TRANSFORMADOR DE NUCLEO DE CORRIENTE 500/5A EBCHQ | Ebchq | 3 |
| 761766 | VOLTIMETRO ANALOGO CHINT 0-300 | Chint | 1 |
| 15897769 | BRIDA  T 160 W21 | WEG | 2 |
| 11469949 | BRIDA B14 T 100 W21 | WEG | 3 |
| 11469917 | BRIDA B14 T80 W21-22 | WEG | 2 |
| 11469948 | BRIDA B14 T90 W21-22 | WEG | 5 |
| 11466109 | BRIDA B5 T 100  W21-22 | WEG | 2 |
| 11535442 | BRIDA B5 T 100 W21-22 | WEG | 1 |
| 11466112 | BRIDA B5 T 112 W22 | WEG | 2 |
| 12199645 | BRIDA B5 T 143/5T EXP PRF NEMA | Sin marca | 1 |
| 11480445 | BRIDA B5 T 160 | Sin marca | 2 |
| 11467385 | BRIDA B5 T 180 | Sin marca | 2 |
| 11467459 | BRIDA B5 T 200 | Sin marca | 5 |
| 11439093 | BRIDA B5 T 225 | Sin marca | 2 |
| 11480444 | BRIDA B5 T 71 | Sin marca | 5 |
| 11466086 | BRIDA B5 T 80 W21 | WEG | 1 |
| 15941752 | CAJA REDUCTORA 2 HP R:43 SAL:39 RPM WEG CESTARI | WEG | 4 |
| VEN-VW30 | EXTRACTOR PORTATIL 12" 500W 110V 3.400 MT3/H | Ventum | 1 |
| VEN-VK750-TW | VENTILADOR INDUSTRIAL DE PARED 30" | Ventum | 2 |
| FO-436G | FILTRO PARKER | Parker | 7 |
| 9V-6LR61 | PILA CUADRADA 9V VARTA | Varta | 1 |

## 3. Resumen: qué necesitamos de la empresa

1. **Precios con IVA** de las 94 referencias publicadas (`precios-pendientes.csv`). Mientras no lleguen, se cotizan.
2. **Códigos (SKU)**: 31 filas «Pendiente» no traen código; 4 comparten código con otra referencia; 1 lo traía convertido en fecha.
3. **Descripciones**: 8 filas sin descripción y 6 con descripción incompleta (solo color y voltaje, sin tipo ni marca).
4. **Revisión de lo que la empresa marcó** («VERIFICAR…», «BUSCAR»): 21 filas.
5. **Cantidad**: 2 filas con cantidad decimal o vacía.
6. **Fotos o fichas**: 131 referencias «Listo» no se publicaron por no encontrar una foto del fabricante ni de un distribuidor colombiano (sección 2).
7. **Dato por confirmar**: el código `TC4Y` (descripción «HOROMETRO DIGITAN AUTONICS») es el de un controlador de temperatura de Autonics, no el de un horómetro; no se publicó hasta confirmar cuál es.
