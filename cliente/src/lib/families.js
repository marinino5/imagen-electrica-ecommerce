import { Activity, Cable, CircuitBoard, Cog, Fan, Gauge, Lightbulb, Settings2 } from 'lucide-react'

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
}

export const familyIcon = (slug) => ICONS[slug] ?? Cog

// Portada de cada familia en el bento del Inicio (public/img/categorias). Si falta, se dibuja el diseño de marca.
export const FAMILY_COVER = {
  'motores-electricos': 'motores',
  'reductores-y-motorreductores': 'motorreductores',
  'control-y-automatizacion': 'variadores-arrancadores',
  'ventilacion-y-climatizacion': 'ventilacion',
  'cables-y-conductores': 'cables',
  iluminacion: 'iluminacion',
  compresores: 'compresores',
}

// Qué familias muestran cada filtro técnico (el resto no los ofrece)
export const HP_FAMILIES = ['motores-electricos']
export const HP_CATEGORIES = ['variadores-de-frecuencia', 'arrancadores-suaves']
export const AMP_CATEGORIES = ['controles-de-maniobra-y-proteccion', 'accesorios-de-maniobra']

export const familyNumber = (index) => String(index + 1).padStart(2, '0')
