// Reglas de presentación del catálogo por líneas (familia → categoría → línea → referencias).
import { formatCOP } from './format'
import { HP_CATEGORIES } from './families'

// Foto propia de la línea (image_url). Sin ella no se reutiliza la ilustración de la categoría: la tarjeta
// muestra el diseño de marca (LineVisual), así dos líneas distintas nunca comparten la misma imagen.
export const lineImage = (line) => line?.image_url ?? null

// ---- Marca ↔ imagen ----
// Una línea puede reunir varias marcas («ABB · Match Drive · Nidec · Nord · WEG»). Las marcas que se muestran son las de
// sus referencias; en «Motores de otras marcas» no va WEG porque ya tiene sus propias líneas.
export const lineBrands = (line) => {
  const all = (line?.brand_label ?? '').split(' · ').map((b) => b.trim()).filter((b) => b && b !== 'None')
  return /otras marcas/i.test(line?.name ?? '') ? all.filter((b) => b.toLowerCase() !== 'weg') : all
}

// Las fotos del catálogo se nombran «<marca>-<producto>»: la foto de un motor WEG no puede ilustrar una referencia de ABB.
// `brand` = marca que se está mostrando; si la foto es de WEG y la marca es otra, no se usa (se ve el diseño de marca).
export const imageMatchesBrand = (url, brand) => {
  if (!url || !brand) return true
  const file = url.split('/').pop().toLowerCase()
  const isWeg = file.startsWith('weg-') && !file.startsWith('weg-cestari')
  if (!isWeg) return true
  return brand.trim().toLowerCase() === 'weg'
}

// Foto de la tarjeta: solo si coincide con alguna de las marcas visibles
export const cardImage = (line) => {
  const url = lineImage(line)
  const brands = lineBrands(line)
  return brands.length && !brands.some((b) => imageMatchesBrand(url, b)) ? null : url
}

// Miniatura de 480 px de la foto de la línea (mega-menú, tarjetas pequeñas): el formato recortado trae `-sm`
export const lineThumb = (line) => {
  const url = lineImage(line)
  return url && url.startsWith('/img/catalogo/') ? url.replace(/\.webp$/, '-sm.webp') : url
}

export const hpText = (hp) => `${String(hp).replace('.', ',')} HP`

// Rangos de potencia y de corriente para los chips de filtro
export const HP_RANGES = [
  { key: 'h1', label: 'Hasta 1 HP', min: 0, max: 1 },
  { key: 'h5', label: 'Más de 1 a 5 HP', min: 1.001, max: 5 },
  { key: 'h15', label: 'Más de 5 a 15 HP', min: 5.001, max: 15 },
  { key: 'h50', label: 'Más de 15 a 50 HP', min: 15.001, max: 50 },
  { key: 'h51', label: 'Más de 50 HP', min: 50.001, max: Infinity },
]
export const AMP_RANGES = [
  { key: 'a10', label: 'Hasta 10 A', min: 0, max: 10 },
  { key: 'a40', label: 'Más de 10 a 40 A', min: 10.001, max: 40 },
  { key: 'a100', label: 'Más de 40 a 100 A', min: 40.001, max: 100 },
  { key: 'a101', label: 'Más de 100 A', min: 100.001, max: Infinity },
]

const hasHp = (line, range) => line.hp_values.some((h) => h >= range.min && h <= range.max)
const hasAmp = (line, range) => line.amp_min !== null && line.amp_min <= range.max && line.amp_max >= range.min

export const buyMode = (line) => (line.sale_mode === 'direct' ? 'on' : 'q')

export const modeTag = (line) =>
  line.sale_mode === 'direct' ? 'Compra en línea' : line.sale_mode === 'quote' ? 'Cotización' : 'Bajo pedido'

export const priceText = (line) => (line.price_from !== null ? `Desde ${formatCOP(line.price_from)}` : 'Precio por cotización')
export const priceNote = (line) =>
  line.price_from !== null ? 'IVA incluido' : line.refs_count ? 'Respuesta de un asesor' : 'Bajo pedido · cotizar'

/**
 * Filtra las líneas. st: { family, category, mode ('on'|'q'|''), brands (array), hp, amp, stock, slugs (Set|null) }
 * slugs es el resultado de la búsqueda en el servidor (incluye SKU y nombre de referencia).
 */
export function filterLines(lines, st) {
  const tech = techFilters(st)
  const hp = tech.hp ? HP_RANGES.find((r) => r.key === st.hp) : null
  const amp = tech.amp ? AMP_RANGES.find((r) => r.key === st.amp) : null
  return lines.filter(
    (l) =>
      (!st.family || l.family?.slug === st.family) &&
      (!st.category || l.category?.slug === st.category) &&
      (!st.mode || buyMode(l) === st.mode) &&
      (!st.brands.length || st.brands.some((b) => (l.brand_label ?? '').split(' · ').includes(b))) &&
      (!hp || hasHp(l, hp)) &&
      (!amp || hasAmp(l, amp)) &&
      (!st.stock || l.stock_total > 0) &&
      (!st.slugs || st.slugs.has(l.slug)),
  )
}

/**
 * Orden de las fichas. 'Recomendados' agrupa por familia en el orden del portafolio (Motores primero) y,
 * dentro de cada familia, respeta el orden que trae la API; no deja la lista de control al principio.
 * `families` es la lista de /families (ya viene en el orden del portafolio).
 */
export function sortLines(lines, order, families = []) {
  const copy = [...lines]
  if (order === 'refs') return copy.sort((a, b) => b.refs_count - a.refs_count)
  if (order === 'stock') return copy.sort((a, b) => b.stock_total - a.stock_total)
  const rank = new Map(families.map((f, i) => [f.slug, i]))
  const at = (l) => rank.get(l.family?.slug) ?? families.length
  return copy.sort((a, b) => at(a) - at(b)) // Array.sort es estable: dentro de la familia queda el orden original
}

/**
 * Qué filtros técnicos se ofrecen según lo que se está viendo. No son filtros generales:
 * Potencia solo en Motores y en Variadores/Arrancadores; Corriente solo en Control y automatización.
 */
export function techFilters(st) {
  return {
    hp: !st.q && (st.family === 'motores-electricos' || HP_CATEGORIES.includes(st.category)),
    amp: !st.q && st.family === 'control-y-automatizacion' && !HP_CATEGORIES.includes(st.category),
  }
}

/** Qué rangos de HP y de A tienen al menos una línea (para mostrar solo los que sirven). */
export function availableRanges(lines) {
  return {
    hp: HP_RANGES.filter((r) => lines.some((l) => hasHp(l, r))),
    amp: AMP_RANGES.filter((r) => lines.some((l) => hasAmp(l, r))),
  }
}

/** Referencia lista para el carrito: lleva los datos de la línea para mostrarla y volver a la ficha. */
export function cartItem(line, variant) {
  return {
    ...variant,
    brand: variant.brand ?? line.brand,
    category: line.category,
    line_slug: line.slug,
    line_name: line.name,
    image_url: lineImage(line),
  }
}

/** Texto corto que distingue una referencia de otras con la misma potencia y velocidad. */
export function variantTag(v) {
  const parts = [v.amp ? `${String(v.amp).replace('.', ',')} A` : null, v.volt ? `${v.volt} V` : null, v.ip]
  return parts.filter(Boolean).join(' · ') || v.sku
}

/** Entre referencias gemelas, lo que las distingue en el nombre ("IE3" frente a "IE2 T:80"); si no hay, corriente, tensión o SKU. */
export function twinTag(v, twins) {
  const words = (name) => name.split(/\s+/)
  const shared = words(twins[0].name).filter((w) => twins.every((t) => words(t.name).some((x) => x.toLowerCase() === w.toLowerCase())))
  const own = words(v.name).filter((w) => !shared.some((x) => x.toLowerCase() === w.toLowerCase()))
  const tag = own.join(' ').trim()
  return tag || variantTag(v)
}

/** Línea sin referencias (bajo pedido): se cotiza como un renglón propio, sin SKU. */
export function lineQuoteItem(line) {
  return {
    id: `line-${line.id}`,
    sku: null,
    name: line.name,
    slug: line.slug,
    unit: 'unidad',
    unit_label: 'Unidad',
    price: null,
    tax_rate: 19,
    stock: 0,
    sale_mode: 'on_request',
    brand: line.brand_label ?? null,
    category: line.category,
    line_slug: line.slug,
    line_name: line.name,
    image_url: lineImage(line),
  }
}
