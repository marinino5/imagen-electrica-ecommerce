import { get, post, put } from './http'

export const authApi = {
  /** Registro de cliente. Responde { data: usuario, token }. */
  register: (payload) => post('/register', payload),
  login: (email, password) => post('/login', { email, password }),
  logout: () => post('/logout', undefined, { auth: true }),
  /** Usuario con los datos de su empresa (customer). */
  me: () => get('/me', { auth: true }),
  /** Pide el enlace de recuperación. Siempre responde 200 con el mismo mensaje, exista o no el correo. */
  forgotPassword: (email) => post('/password/forgot', { email }),
  /** Cambia la contraseña con el token del correo. 422 si el enlace no sirve o venció. */
  resetPassword: (payload) => post('/password/reset', payload),
  updateMe: (payload) => put('/me', payload, { auth: true }),
  /** Datos de facturación (nombres de Siigo). Con `needs_invoice: false` se borran. Responde el usuario completo. */
  updateBilling: (payload) => put('/me/billing', payload, { auth: true }),
  /** Reemplaza la lista de direcciones de entrega guardadas (hasta 5). */
  updateAddresses: (addresses) => put('/me/addresses', { addresses }, { auth: true }),
}
