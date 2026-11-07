// Reglas de presentación del catálogo (la decisión final de pedido/cotización la toma el backend).

/** Un producto se compra en línea si es de venta directa, tiene precio y stock para la cantidad. */
export function isDirectPurchase(product, quantity = 1) {
  return product.sale_mode === 'direct' && product.price !== null && product.stock >= quantity
}

/** Foto oficial del producto (image_url); si no tiene, la tienda muestra el diseño de marca, nunca una imagen genérica. */
export const productImage = (product) => product.image_url ?? null
