import { useCallback, useEffect, useState } from 'react'
import { catalogApi } from '../api'

// Las categorías se piden una sola vez y se comparten entre componentes
let cache = null
let pending = null

const EMPTY = { all: [], roots: [], byId: new Map(), bySlug: new Map(), childrenOf: () => [] }

function build(list) {
  const byId = new Map(list.map((c) => [c.id, c]))
  const bySlug = new Map(list.map((c) => [c.slug, c]))
  const roots = list.filter((c) => c.parent_id === null)
  const childrenOf = (id) => list.filter((c) => c.parent_id === id)
  return { all: list, roots, byId, bySlug, childrenOf }
}

export function useCategories() {
  const [state, setState] = useState({ data: cache ?? EMPTY, loading: !cache, error: null })
  const [tick, setTick] = useState(0)

  useEffect(() => {
    if (cache) return
    let active = true
    pending ??= catalogApi
      .categories()
      .then((res) => (cache = build(res.data)))
      .finally(() => (pending = null))
    pending
      .then((data) => active && setState({ data, loading: false, error: null }))
      .catch((error) => active && setState({ data: EMPTY, loading: false, error }))
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
