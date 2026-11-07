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

/** Foto oficial del producto (image_url); si no tiene, la tienda muestra el diseño de marca, nunca una imagen genérica. */
export const productImage = (product) => product.image_url ?? null
