import { useCallback, useEffect, useState } from 'react'
import { catalogApi } from '../api'

// Familias del portafolio con sus categorías, marcas y totales. Se piden una sola vez y se comparten.
let cache = null
let pending = null

const EMPTY = { data: [], meta: { brands: [], lines: 0, references: 0 } }

export function useFamilies() {
  const [state, setState] = useState({ ...(cache ?? EMPTY), loading: !cache, error: null })
  const [tick, setTick] = useState(0)

  useEffect(() => {
    if (cache) return
    let active = true
    pending ??= catalogApi
      .families()
      .then((res) => (cache = res))
      .finally(() => (pending = null))
    pending
      .then((res) => active && setState({ ...res, loading: false, error: null }))
      .catch((error) => active && setState({ ...EMPTY, loading: false, error }))
    return () => {
      active = false
    }
  }, [tick])

  const reload = useCallback(() => {
    setState((s) => ({ ...s, loading: true, error: null }))
    setTick((t) => t + 1)
  }, [])

  return { data: state.data, meta: state.meta, loading: state.loading, error: state.error, reload }
}
