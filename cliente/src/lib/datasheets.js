// Ficha técnica oficial del fabricante (PDF en su centro de descargas) por slug de línea.
// Solo van enlaces comprobados; si una línea no está aquí, la ficha no muestra el bloque de descargas.
const W = 'https://static.weg.net/medias/downloadcenter'
export const DATASHEETS = {
  contactores: [`${W}/h53/hdd/WEG-cwb-la-nueva-generacion-de-contactores-weg-50043943-es.pdf`, 'Contactores CWB (WEG)'],
  'reles-termicos': [`${W}/h2c/he8/WEG-reles-de-sobrecarga-termicos-RW-50070232-es.pdf`, 'Relés de sobrecarga RW (WEG)'],
  guardamotores: [`${W}/h19/he1/WEG-guardamotores-MPW-50030559-es.pdf`, 'Guardamotores MPW (WEG)'],
  'reles-de-control-y-proteccion': [`${W}/hb4/h1d/WEG-reles-temporizadores-protetores-e-de-nivel-50009830-catalogo-portugues-br-dc.pdf`, 'Relés electrónicos (WEG)'],
  'arrancadores-directos': [`${W}/h3c/h14/WEG-chaves-de-partida-50009814-catalogo-portugues-br-dc.pdf`, 'Chaves de partida (WEG)'],
  'pulsadores-pilotos-y-selectores': [`${W}/h0b/h58/WEG-linea-de-mando-y-senalizacion-csw-panorama-general-50066475-es.pdf`, 'Mando y señalización CSW (WEG)'],
  'borneras-y-accesorios': [`${W}/h1a/hf0/WEG-bornes-linea-btw-50034142-catalogo-espanol.pdf`, 'Bornes BTW (WEG)'],
  minibreakers: [`${W}/hec/h5f/WEG-solucoes-integradas-para-instalacoes-eletricas-50009824-pt.pdf`, 'Instalaciones eléctricas (WEG)'],
  'variador-cfw100': [`${W}/hd9/h2d/WEG-CFW100-50049400-catalogo-es.pdf`, 'Catálogo CFW100 (WEG)'],
  'variador-cfw300': [`${W}/h24/he1/WEG-inversores-de-frequencia-10525554-catalogo-pt.pdf`, 'Catálogo de variadores (WEG)'],
  'variador-cfw500': [`${W}/h44/hc1/WEG-CFW500-catalog-50036260-es.pdf`, 'Catálogo CFW500 (WEG)'],
  'variador-cfw900': [`${W}/h24/he1/WEG-inversores-de-frequencia-10525554-catalogo-pt.pdf`, 'Catálogo de variadores (WEG)'],
  'arrancador-suave-ssw05': [`${W}/h4e/h02/WEG-ssw-05-soft-starter-usassw0509-brochure-english.pdf`, 'Folleto SSW05 (WEG)'],
  'arrancador-suave-ssw07': [`${W}/h8a/h9c/WEG-ssw-07-soft-starter-usassw07-brochure-english.pdf`, 'Folleto SSW07 (WEG)'],
  'plc-weg-plc200-y-plc500': [`${W}/h05/h0f/WEG-PLC-programmable-logic-controllers-50096533-en.pdf`, 'Catálogo de PLC (WEG)'],
  'w21-iec-carcasa-aluminio': [`${W}/h28/h24/WEG-WMO-W21-aluminium-multimounting-50111617-brochure-spanish-web.pdf`, 'Catálogo W21 aluminio (WEG)'],
  'w12-monofasico': [`${W}/h2e/h40/WEG-W12-motor-eletrico-monofasico-e-trifasico-50084475-brochure-portuguese-web.pdf`, 'Catálogo W12 (WEG)'],
  'w22-nema-premium-efficiency': [`${W}/hf8/h29/WEG-w22-three-phase-electric-motor-50029265-brochure-english-web.pdf`, 'Catálogo técnico W22 (WEG)'],
  'w22-iec-carcasa-hierro': [`${W}/hf8/h29/WEG-w22-three-phase-electric-motor-50029265-brochure-english-web.pdf`, 'Catálogo técnico W22 (WEG)'],
  'motorreductor-sinfin-wcg01': [`${W}/h5b/he9/WEG-CESTARI-catalogo-geral-de-reductores-50128743-brochure-es-web.pdf`, 'Catálogo general WEG Cestari'],
  'reductor-helicoidal-wcg20': [`${W}/h5b/he9/WEG-CESTARI-catalogo-geral-de-reductores-50128743-brochure-es-web.pdf`, 'Catálogo general WEG Cestari'],
}
export const datasheet = (slug) => DATASHEETS[slug] ?? null

// «Dónde se usa»: aplicaciones típicas con foto, por familia
export const WHERE_USED = {
  'motores-electricos': [
    ['Bandas transportadoras', '/img/aplicaciones/bandas.webp'],
    ['Bombeo de agua', '/img/aplicaciones/bombeo.webp'],
    ['Plantas industriales', '/img/fotos/planta-operando.webp'],
    ['Agroindustria', '/img/fotos/sector-agroindustria.webp'],
  ],
  'reductores-y-motorreductores': [
    ['Bandas transportadoras', '/img/aplicaciones/bandas.webp'],
    ['Minería y cantera', '/img/fotos/sector-mineria.webp'],
    ['Alimentos y bebidas', '/img/fotos/sector-alimentos.webp'],
  ],
  'control-y-automatizacion': [
    ['Tableros eléctricos', '/img/fotos/tablero-manos.webp'],
    ['Plantas de producción', '/img/fotos/sector-alimentos.webp'],
    ['Obras y construcción', '/img/fotos/sector-construccion.webp'],
  ],
  'ventilacion-y-climatizacion': [
    ['Bodegas', '/img/fotos/bodega-cajas.webp'],
    ['Talleres', '/img/fotos/sector-talleres.webp'],
    ['Plantas industriales', '/img/aplicaciones/ventilacion.webp'],
  ],
  default: [
    ['Obras y construcción', '/img/fotos/sector-construccion.webp'],
    ['Talleres', '/img/fotos/sector-talleres.webp'],
    ['Plantas industriales', '/img/fotos/planta-operando.webp'],
  ],
}
