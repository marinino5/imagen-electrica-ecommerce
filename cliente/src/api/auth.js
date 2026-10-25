import { get, post, put } from './http'

export const authApi = {
  /** Registro de cliente. Responde { data: usuario, token }. */
  register: (payload) => post('/register', payload),
  login: (email, password) => post('/login', { email, password }),
  logout: () => post('/logout', undefined, { auth: true }),
  /** Usuario con los datos de su empresa (customer). */
  me: () => get('/me', { auth: true }),
  updateMe: (payload) => put('/me', payload, { auth: true }),
}
