import { Activity, Cable, CircuitBoard, Cog, Fan, Gauge, Lightbulb, Package, Plug, Settings2 } from 'lucide-react'

// Icono grande de cada familia: se usa en el diseño de marca cuando una ficha aún no tiene foto.
const ICONS = {
  'motores-electricos': Cog,
  'reductores-y-motorreductores': Settings2,
  'control-y-automatizacion': CircuitBoard,
  'ventilacion-y-climatizacion': Fan,
  'cables-y-conductores': Cable,
  iluminacion: Lightbulb,
  compresores: Gauge,
  'medicion-y-testeo': Activity,
  'material-electrico': Plug,
  'otros-productos': Package,
}

export const familyIcon = (slug) => ICONS[slug] ?? Cog

// Foto de cada familia (bento del Inicio, mega-menú, miniaturas del catálogo y banner). Siempre es la imagen del
// producto recortada sin fondo, en el formato único de la tienda (1000 × 1000, producto al 78 %, misma línea base y
// misma sombra: ver cliente/scripts/normalizar-producto.py). `sm` es la versión de 480 px para miniaturas.
const cut = (file, alt, ext = 'webp') => ({ src: `/img/catalogo/${file}.${ext}`, sm: ext === 'svg' ? `/img/catalogo/${file}.svg` : `/img/catalogo/${file}-sm.${ext}`, product: true, alt })
export const FAMILY_PHOTO = {
  'motores-electricos': cut('weg-w22-nema-premium', 'Motor eléctrico WEG W22'),
  'reductores-y-motorreductores': cut('weg-cestari-wcg20', 'Motorreductor WEG Cestari'),
  'control-y-automatizacion': cut('weg-cfw500', 'Variador de frecuencia WEG CFW500'),
  'ventilacion-y-climatizacion': cut('soler-palau-extractor-axial', 'Extractor axial tubular industrial galvanizado Soler & Palau'),
  'cables-y-conductores': cut('centelsa-cable-thhn', 'Rollo de cable THHN/THWN-2 Nexans Línea Plus (Centelsa)'),
  iluminacion: cut('sylvania-start-highbay', 'Campana industrial LED Sylvania START Highbay'),
  compresores: cut('abac-compresor-vertical', 'Compresor de pistón vertical ABAC de 270 litros'),
  'medicion-y-testeo': cut('fluke-multimetro-117', 'Multímetro Fluke 117'),
  'material-electrico': cut('leviton-wid0002', 'Toma doble blanca Leviton NEMA 5-15R'),
  // «Otros productos» no lleva foto de producto: su portada es un diseño sobrio (icono sobre lila), ver .fam--sober
}
export const familyPhoto = (slug) => FAMILY_PHOTO[slug] ?? null

// Foto de aplicación (el equipo en uso) para la galería de la ficha, por familia
export const FAMILY_APP_PHOTO = {
  'motores-electricos': { src: '/img/fotos/planta-operando.webp', alt: 'Operario en una planta industrial' },
  'reductores-y-motorreductores': { src: '/img/aplicaciones/bandas.webp', alt: 'Banda transportadora en una planta' },
  'control-y-automatizacion': { src: '/img/fotos/electricista-tablero.webp', alt: 'Electricista conectando un tablero' },
  'ventilacion-y-climatizacion': { src: '/img/aplicaciones/ventilacion.webp', alt: 'Ventilador industrial instalado' },
  'cables-y-conductores': { src: '/img/fotos/tablero-cerca.webp', alt: 'Cableado de un tablero eléctrico' },
  iluminacion: { src: '/img/fotos/almacen-oscuro.webp', alt: 'Almacén con estanterías' },
  compresores: { src: '/img/aplicaciones/bombeo.webp', alt: 'Tubería y equipos de una planta' },
  'medicion-y-testeo': { src: '/img/aplicaciones/fluke-en-tablero.webp', alt: 'Multímetro Fluke midiendo en un tablero' },
  'material-electrico': { src: '/img/fotos/tablero-cerca.webp', alt: 'Cableado de un tablero eléctrico' },
  'otros-productos': { src: '/img/fotos/almacen-cajas.webp', alt: 'Cajas en una estantería del almacén' },
}

// Qué familias muestran cada filtro técnico (el resto no los ofrece)
export const HP_FAMILIES = ['motores-electricos']
export const HP_CATEGORIES = ['variadores-de-frecuencia', 'arrancadores-suaves']
export const AMP_CATEGORIES = ['controles-de-maniobra-y-proteccion', 'accesorios-de-maniobra']

export const familyNumber = (index) => String(index + 1).padStart(2, '0')

// Descripción corta de cada familia para el banner del catálogo (la API solo trae descripción en algunas)
export const FAMILY_BLURB = {
  'motores-electricos': 'Motores monofásicos y trifásicos de uso general, alta eficiencia y a prueba de explosión.',
  'reductores-y-motorreductores': 'Reductores de sinfín y corona y helicoidales para bandas, mezcladoras y elevadores.',
  'control-y-automatizacion': 'Variadores, arrancadores, contactores, relés, PLC y protección para tableros.',
  'ventilacion-y-climatizacion': 'Extractores y ventiladores industriales para almacenes, talleres y plantas.',
  'cables-y-conductores': 'Conductores THHN / THWN-2 para instalaciones eléctricas.',
  iluminacion: 'Iluminación industrial LED para naves, almacenes y talleres.',
  compresores: 'Compresores de pistón para taller e industria.',
  'medicion-y-testeo': 'Multímetros, instrumentos de panel y equipos de medición para electricistas y técnicos.',
  'material-electrico': 'Tomas, clavijas, cintas aislantes y puesta a tierra para instalaciones eléctricas.',
  'otros-productos': 'Insumos, herramientas y repuestos que no son material eléctrico.',
}
