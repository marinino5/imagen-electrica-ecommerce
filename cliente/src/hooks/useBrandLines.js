import { useEffect, useMemo, useState } from 'react'
import { catalogApi } from '../api'
import { useCategories } from './useCategories'

// Líneas (categorías principales) que tiene cada marca en la tienda, calculadas con los productos reales de la API.
// Devuelve un Map: id de marca → nombres de línea en el orden del catálogo. Si falla la consulta, queda vacío.
export function useBrandLines() {
  const { data: categories } = useCategories()
  const [products, setProducts] = useState([])

  useEffect(() => {
    let active = true
    ;(async () => {
      const all = []
      for (let page = 1; page <= 10; page++) {
        const res = await catalogApi.products({ per_page: 50, page })
        all.push(...res.data)
        if (page >= (res.meta?.last_page ?? 1)) break
      }
      if (active) setProducts(all)
    })().catch(() => {})
    return () => {
      active = false
    }
  }, [])

  return useMemo(() => {
    const lines = new Map()
    if (categories.all.length === 0) return lines
    const order = new Map(categories.roots.map((r, i) => [r.id, i]))
    const found = new Map() // id de marca → Set de ids de línea
    for (const p of products) {
      if (!p.brand || !p.category) continue
      const own = categories.byId.get(p.category.id)
      const root = own?.parent_id ? categories.byId.get(own.parent_id) : own
      if (!root) continue
      if (!found.has(p.brand.id)) found.set(p.brand.id, new Set())
      found.get(p.brand.id).add(root.id)
    }
    for (const [brandId, ids] of found) {
      lines.set(brandId, [...ids].sort((a, b) => order.get(a) - order.get(b)).map((id) => categories.byId.get(id).name))
    }
    return lines
  }, [products, categories])
}
