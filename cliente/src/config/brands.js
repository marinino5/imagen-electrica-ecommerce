// Marcas que se muestran como vitrina (franja del Inicio y bloque de Nosotros), en este orden.
// La base de datos puede tener más marcas (las de las referencias del inventario); aquí solo se
// listan las que la empresa quiere destacar. `slugs` une marcas que se muestran como una sola.
export const showcaseBrands = [
  { name: 'WEG', slugs: ['weg'], big: true },
  { name: 'WEG Cestari', slugs: ['weg-cestari'] },
  { name: 'Nexans / CENTELSA', slugs: ['nexans', 'centelsa-by-nexans'] },
  { name: 'Sylvania', slugs: ['sylvania'] },
  { name: 'Soler & Palau', slugs: ['soler-palau'] },
  { name: 'AIRAL', slugs: ['airal'] },
  { name: 'ABB', slugs: ['abb'] },
  { name: 'Fluke', slugs: ['fluke'] },
  { name: 'York', slugs: ['york'] },
  { name: 'Delta', slugs: ['delta'] },
]

// Combina la lista fija con los logos que el API tenga (si una marca aún no tiene logo, se muestra su nombre)
export function withLogos(apiBrands = []) {
  return showcaseBrands.map((b) => ({
    ...b,
    logo_url: apiBrands.find((x) => b.slugs.includes(x.slug) && x.logo_url)?.logo_url ?? null,
  }))
}
