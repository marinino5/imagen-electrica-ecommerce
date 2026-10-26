// Cliente HTTP único de la tienda. Todo lo que sale hacia el backend pasa por aquí.
// - Base: VITE_API_URL (incluye /api/v1)
// - Token de Sanctum guardado en localStorage y enviado como Bearer
// - Errores normalizados en ApiError: sin conexión, 401, 422, etc.

const BASE_URL = (import.meta.env.VITE_API_URL ?? '').replace(/\/$/, '')
const TOKEN_KEY = 'ie.token'
// Render (plan gratis) puede tardar en "despertar": se espera más de lo normal
const TIMEOUT_MS = 45000

export class ApiError extends Error {
  /**
   * @param {'network'|'timeout'|'unauthorized'|'forbidden'|'not_found'|'validation'|'server'} kind
   */
  constructor(kind, message, { status = 0, errors = {} } = {}) {
    super(message)
    this.name = 'ApiError'
    this.kind = kind
    this.status = status
    this.errors = errors // 422: { campo: ['mensaje', ...] }
  }

  /** Primer mensaje de un campo (para mostrarlo bajo el input). */
  fieldError(field) {
    return this.errors?.[field]?.[0]
  }
}

export const tokenStore = {
  get() {
    try {
      return localStorage.getItem(TOKEN_KEY)
    } catch {
      return null
    }
  },
  set(token) {
    try {
      localStorage.setItem(TOKEN_KEY, token)
    } catch {
      /* sin almacenamiento: la sesión dura hasta recargar */
    }
  },
  clear() {
    try {
      localStorage.removeItem(TOKEN_KEY)
    } catch {
      /* nada que limpiar */
    }
  },
}

// El contexto de sesión registra aquí qué hacer cuando el token ya no sirve (401)
let unauthorizedHandler = null
export function setUnauthorizedHandler(handler) {
  unauthorizedHandler = handler
}

function buildUrl(path, params) {
  const url = `${BASE_URL}${path}`
  if (!params) return url
  const query = new URLSearchParams()
  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '' && value !== false) {
      query.set(key, value === true ? '1' : String(value))
    }
  })
  const qs = query.toString()
  return qs ? `${url}?${qs}` : url
}

/**
 * @param {string} method
 * @param {string} path  ruta relativa a VITE_API_URL, ej. '/products'
 * @param {{ params?: object, body?: object, auth?: boolean, signal?: AbortSignal }} options
 *        auth: true envía el token (rutas protegidas)
 */
export async function request(method, path, { params, body, auth = false, signal } = {}) {
  if (!BASE_URL) {
    throw new ApiError('server', 'Falta configurar VITE_API_URL (revisa el archivo .env).')
  }

  const headers = { Accept: 'application/json' }
  if (body !== undefined) headers['Content-Type'] = 'application/json'

  const token = auth ? tokenStore.get() : null
  if (token) headers.Authorization = `Bearer ${token}`

  const controller = new AbortController()
  let timedOut = false
  const timer = setTimeout(() => {
    timedOut = true
    controller.abort()
  }, TIMEOUT_MS)
  // Si quien llama cancela (ej. el usuario sigue escribiendo), se cancela también la petición
  signal?.addEventListener('abort', () => controller.abort())

  let response
  try {
    response = await fetch(buildUrl(path, params), {
      method,
      headers,
      body: body !== undefined ? JSON.stringify(body) : undefined,
      signal: controller.signal,
    })
  } catch (error) {
    if (signal?.aborted && !timedOut) throw error // cancelada a propósito: no es un error para mostrar
    if (timedOut) {
      throw new ApiError('timeout', 'El servidor tardó demasiado en responder. Inténtalo de nuevo en unos segundos.')
    }
    throw new ApiError('network', 'No hay conexión con el servidor. Revisa tu internet e inténtalo de nuevo.')
  } finally {
    clearTimeout(timer)
  }

  if (response.status === 204) return null

  let data = null
  try {
    data = await response.json()
  } catch {
    /* respuesta sin JSON */
  }

  if (response.ok) return data

  const message = data?.message
  switch (response.status) {
    case 401:
      // Con token enviado = sesión vencida. Sin token (ej. login) = credenciales incorrectas.
      if (token) {
        unauthorizedHandler?.()
        throw new ApiError('unauthorized', 'Tu sesión venció. Ingresa de nuevo.', { status: 401 })
      }
      throw new ApiError('unauthorized', message ?? 'No autorizado.', { status: 401 })
    case 403:
      throw new ApiError('forbidden', message ?? 'No tienes permiso para hacer esto.', { status: 403 })
    case 404:
      throw new ApiError('not_found', message ?? 'No se encontró lo que buscas.', { status: 404 })
    case 422:
      throw new ApiError('validation', message ?? 'Revisa los datos del formulario.', {
        status: 422,
        errors: data?.errors ?? {},
      })
    default:
      throw new ApiError('server', message ?? 'Ocurrió un error en el servidor. Inténtalo más tarde.', {
        status: response.status,
      })
  }
}

export const get = (path, options) => request('GET', path, options)
export const post = (path, body, options) => request('POST', path, { ...options, body })
export const put = (path, body, options) => request('PUT', path, { ...options, body })
export const patch = (path, body, options) => request('PATCH', path, { ...options, body })
