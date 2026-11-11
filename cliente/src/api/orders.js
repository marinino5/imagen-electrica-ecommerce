import { get, post } from './http'

export const ordersApi = {
  /**
   * Envía el carrito. El backend decide qué es pedido y qué es cotización
   * y responde una lista con una o dos solicitudes.
   */
  create: (payload) => post('/orders', payload, { auth: true }),
  list: (params) => get('/orders', { params, auth: true }),
  show: (code) => get(`/orders/${encodeURIComponent(code)}`, { auth: true }),
}

export const checkoutApi = {
  /**
   * Compra con datos de facturación electrónica y entrega. Sin cuenta (invitado) o con ella: pasa auth: true solo si la sesión es válida.
   * Responde { data: [pedidos], meta: { guest, account_exists, status_urls } }.
   */
  create: (payload, { auth = false } = {}) => post('/checkout', payload, { auth }),
}

export const shippingApi = {
  /** Tipo de envío, zona y valor según el municipio de entrega (DANE). Gratis al llegar a la compra mínima. */
  quote: (cityCode, subtotal) => get('/shipping/quote', { params: { city_code: cityCode, subtotal } }),
}

export const guestOrdersApi = {
  /** Estado de un pedido hecho sin cuenta: `link` = { expires, signature } del correo. 403 si el enlace no sirve o venció. */
  show: (code, link) => get(`/guest/orders/${encodeURIComponent(code)}`, { params: link }),
  /** «Crea tu cuenta con un clic»: con los datos de la compra, solo falta la contraseña. Responde { data: usuario, token }. */
  createAccount: (code, link, payload) => post(`/guest/orders/${encodeURIComponent(code)}/account`, { ...link, ...payload }),
}

export const quotesApi = {
  /** Cotización sin cuenta: público, con límite por IP. items: [{ product_id | line, quantity }] */
  create: (payload) => post('/quote-requests', payload),
  /** Cotización libre (sin elegir productos), con adjunto opcional: payload es un FormData. Responde { reference, response_time }. */
  createFree: (formData) => post('/quote-requests/free', formData),
}

export const newsletterApi = {
  /** Suscripción al boletín del pie: público, con límite por IP. Requiere la autorización de datos (Ley 1581). */
  subscribe: (payload) => post('/newsletter', payload),
}
