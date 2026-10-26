import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { authApi, setUnauthorizedHandler, tokenStore } from '../api'

const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const navigate = useNavigate()
  const [user, setUser] = useState(null)
  // 'loading' mientras se valida el token guardado al abrir la tienda
  const [status, setStatus] = useState(tokenStore.get() ? 'loading' : 'guest')

  const clearSession = useCallback(() => {
    tokenStore.clear()
    setUser(null)
    setStatus('guest')
  }, [])

  // Token vencido o revocado (401): se limpia la sesión y se lleva a /ingresar con un aviso
  useEffect(() => {
    setUnauthorizedHandler(() => {
      clearSession()
      navigate('/ingresar', {
        replace: true,
        state: { notice: 'Tu sesión venció. Ingresa de nuevo para continuar.' },
      })
    })
    return () => setUnauthorizedHandler(null)
  }, [clearSession, navigate])

  // Al abrir la tienda con un token guardado, se recupera el usuario
  useEffect(() => {
    if (!tokenStore.get()) return
    authApi
      .me()
      .then((res) => {
        setUser(res.data)
        setStatus('authenticated')
      })
      .catch((error) => {
        // 401 ya lo limpia el manejador; ante un fallo de red se conserva el token y se reintenta luego
        if (error.kind === 'unauthorized') return
        setStatus('guest')
      })
  }, [])

  // Registro e ingreso devuelven el token; el usuario se completa con sus datos de empresa
  const startSession = useCallback(async (res) => {
    tokenStore.set(res.token)
    let current = res.data
    if (!current.customer && current.role === 'cliente') {
      current = (await authApi.me()).data
    }
    setUser(current)
    setStatus('authenticated')
    return current
  }, [])

  const login = useCallback(async (email, password) => startSession(await authApi.login(email, password)), [startSession])
  const register = useCallback(async (payload) => startSession(await authApi.register(payload)), [startSession])

  const logout = useCallback(async () => {
    try {
      await authApi.logout()
    } catch {
      /* aunque falle la red, la sesión local se cierra */
    }
    clearSession()
  }, [clearSession])

  const value = useMemo(
    () => ({ user, status, isAuthenticated: status === 'authenticated', login, register, logout }),
    [user, status, login, register, logout],
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth() {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth debe usarse dentro de AuthProvider')
  return ctx
}
