import { useCallback, useEffect, useState } from 'react'
import { catalogApi } from '../api'

// Las ~33 líneas se piden una sola vez (caben en una página) y los filtros se aplican en el navegador.
let cache = null
let pending = null

export function useLines() {
  const [state, setState] = useState({ data: cache ?? [], loading: !cache, error: null })
  const [tick, setTick] = useState(0)

  useEffect(() => {
    if (cache) return
    let active = true
    pending ??= catalogApi
      .lines({})
      .then((res) => (cache = res.data))
      .finally(() => (pending = null))
    pending
      .then((data) => active && setState({ data, loading: false, error: null }))
      .catch((error) => active && setState({ data: [], loading: false, error }))
    return () => {
      active = false
    }
  }, [tick])

  const reload = useCallback(() => {
    setState((s) => ({ ...s, loading: true, error: null }))
    setTick((t) => t + 1)
  }, [])

  return { ...state, reload }
}

// Búsqueda en el servidor (encuentra también por SKU y por nombre de referencia). Devuelve el conjunto de slugs o null.
export function useLineSearch(term) {
  const [slugs, setSlugs] = useState(null)

  useEffect(() => {
    const q = term.trim()
    if (!q) {
      setSlugs(null)
      return
    }
    const controller = new AbortController()
    const timer = setTimeout(() => {
      catalogApi
        .lines({ q }, controller.signal)
        .then((res) => setSlugs(new Set(res.data.map((l) => l.slug))))
        .catch(() => {})
    }, 300)
    return () => {
      clearTimeout(timer)
      controller.abort()
    }
  }, [term])

  return slugs
}
