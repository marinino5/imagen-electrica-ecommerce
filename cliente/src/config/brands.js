// Marcas que se muestran como vitrina (franja del Inicio y bloque de Nosotros), en este orden.
// La base de datos puede tener más marcas (las de las referencias del inventario); aquí solo se
// listan las que la empresa quiere destacar. `slugs` une marcas que se muestran como una sola.
export const showcaseBrands = [
  { name: 'WEG', slugs: ['weg'], logo: '/img/marcas/weg.png', lines: 'motores, variadores, control y automatización', big: true },
  { name: 'WEG Cestari', slugs: ['weg-cestari'], logo: '/img/marcas/weg-cestari.svg', optical: 1.35, lines: 'reductores y motorreductores' },
  { name: 'Nexans / CENTELSA', slugs: ['nexans', 'centelsa-by-nexans'], logo: '/img/marcas/nexans.svg', lines: 'cables y conductores THHN / THWN-2' },
  { name: 'Sylvania', slugs: ['sylvania'], logo: null, lines: 'iluminación industrial LED' },
  { name: 'Soler & Palau', slugs: ['soler-palau'], logo: '/img/marcas/soler-palau.svg', optical: 1.55, lines: 'ventilación y extracción' },
  { name: 'AIRAL', slugs: ['airal'], logo: '/img/marcas/airal.png', lines: 'extractores y ventiladores axiales' },
  { name: 'ABB', slugs: ['abb'], logo: '/img/marcas/abb.svg', lines: 'motores y automatización' },
  { name: 'Siemens', slugs: ['siemens'], logo: '/img/marcas/siemens.svg', lines: 'contactores y maniobra' },
  { name: 'Fluke', slugs: ['fluke'], logo: '/img/marcas/fluke.svg', lines: 'instrumentos de medición y testeo' },
  { name: 'Nord', slugs: ['nord'], logo: '/img/marcas/nord.svg', lines: 'motores y motorreductores' },
  { name: 'Nidec', slugs: ['nidec'], logo: '/img/marcas/nidec.svg', lines: 'motores eléctricos' },
  { name: 'York', slugs: ['york'], logo: null, lines: 'compresores' },
  { name: 'Delta', slugs: ['delta'], logo: '/img/marcas/delta.svg', lines: 'variadores y automatización' },
]

// Combina la lista fija con los logos que el API tenga (si una marca aún no tiene logo, se muestra su nombre)
export function withLogos(apiBrands = []) {
  return showcaseBrands.map((b) => ({
    ...b,
    // Logo oficial del fabricante guardado en /img/marcas; si la marca aún no tiene archivo, el que traiga la API
    logo_url: b.logo ?? apiBrands.find((x) => b.slugs.includes(x.slug) && x.logo_url)?.logo_url ?? null,
  }))
}

// Marca → logo (para los filtros y las tarjetas del catálogo). La clave es el nombre en minúsculas.
export const brandLogo = (name) => {
  const n = String(name ?? '').toLowerCase()
  const hit = showcaseBrands.find((b) => b.logo && (b.name.toLowerCase() === n || b.slugs.some((s) => n.replace(/[^a-z]/g, '').includes(s.replace(/[^a-z]/g, '')))))
  return hit?.logo ?? null
}
