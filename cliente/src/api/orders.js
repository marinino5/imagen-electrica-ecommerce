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
