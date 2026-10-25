// Reglas de presentación del catálogo (la decisión final de pedido/cotización la toma el backend).

/** Un producto se compra en línea si es de venta directa, tiene precio y stock para la cantidad. */
export function isDirectPurchase(product, quantity = 1) {
  return product.sale_mode === 'direct' && product.price !== null && product.stock >= quantity
}

export function availability(product) {
  if (product.sale_mode === 'quote') return { label: 'Solo cotización', tone: 'muted' }
  if (product.sale_mode === 'on_request') return { label: 'Bajo pedido', tone: 'warning' }
  if (product.in_stock) return { label: 'Disponible', tone: 'success' }
  return { label: 'Sin stock: cotiza', tone: 'warning' }
}

// Imágenes de línea que ya existen en public/img/categorias, por slug de la categoría principal
const LINE_IMAGES = {
  motorreductores: 'motorreductores',
  'motores-electricos': 'motores',
  'variadores-y-arrancadores': 'variadores-arrancadores',
  ventilacion: 'ventilacion',
  iluminacion: 'iluminacion',
  cables: 'cables',
  compresores: 'compresores',
  plc: 'automatizacion',
}

export const lineImage = (rootSlug) =>
  LINE_IMAGES[rootSlug] ? `/img/categorias/${LINE_IMAGES[rootSlug]}.webp` : null

/** Foto del producto; si no tiene, la imagen genérica de su línea. */
export function productImage(product, categories) {
  if (product.image_url) return product.image_url
  const own = categories.byId.get(product.category?.id)
  const root = own?.parent_id ? categories.byId.get(own.parent_id) : own
  return lineImage(root?.slug)
}

// Qué incluye cada línea, tomado de sus subcategorías del catálogo
const LINE_INFO = {
  motorreductores: 'Sinfín corona y coaxiales para bandas, mezcladores y transportadores.',
  'motores-electricos': 'Motores trifásicos y monofásicos para bombeo, ventilación y maquinaria.',
  'variadores-y-arrancadores': 'Variadores de frecuencia, arrancadores suaves y guardamotores para controlar y proteger.',
  ventilacion: 'Extractores y ventiladores axiales para renovar el aire de tu planta.',
  iluminacion: 'Reflectores y tubos LED para bodegas, patios y áreas de trabajo.',
  cables: 'Cables THHN y encauchetados, por metro o por rollo.',
  compresores: 'Compresores de pistón para talleres y plantas.',
  plc: 'PLC compactos y relés programables para automatizar procesos.',
}

export const lineInfo = (rootSlug) => LINE_INFO[rootSlug] ?? 'Material eléctrico industrial con asesoría técnica.'
