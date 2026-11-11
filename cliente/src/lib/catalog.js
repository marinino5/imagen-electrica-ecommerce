// Reglas de presentación del catálogo (la decisión final de pedido/cotización la toma el backend).

/**
 * Un producto se compra en línea si es de venta directa y tiene precio. El stock NO decide mientras la tienda no esté conectada con
 * Siigo (todo está en 0): la API manda `stock_limit` solo cuando el stock ya se usa, y entonces la cantidad no puede pasar de ahí.
 */
export function isDirectPurchase(product, quantity = 1) {
  const limit = product.stock_limit ?? null
  return product.sale_mode === 'direct' && product.price !== null && product.in_stock !== false && (limit === null || quantity <= limit)
}

/** Foto oficial del producto (image_url); si no tiene, la tienda muestra el diseño de marca, nunca una imagen genérica. */
export const productImage = (product) => product.image_url ?? null
