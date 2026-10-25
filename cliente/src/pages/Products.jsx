import { useEffect, useRef, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import { Search, X } from 'lucide-react'
import { catalogApi } from '../api'
import { useApi } from '../hooks/useApi'
import { useCategories } from '../hooks/useCategories'
import ProductCard from '../components/ui/ProductCard'
import QuickView from '../components/ui/QuickView'
import ErrorState from '../components/ui/ErrorState'
import EmptyState from '../components/ui/EmptyState'
import ProductGridSkeleton from '../components/ui/ProductGridSkeleton'
import '../styles/pages.css'

const FILTERS = ['q', 'category', 'brand', 'sale_mode', 'in_stock', 'sort']

const saleModes = [
  { value: '', label: 'Todas las formas de compra' },
  { value: 'direct', label: 'Compra en línea' },
  { value: 'quote', label: 'Solo cotización' },
  { value: 'on_request', label: 'Bajo pedido' },
]
const sorts = [
  { value: '', label: 'Orden: nombre' },
  { value: 'newest', label: 'Más recientes' },
  { value: 'price_asc', label: 'Precio: menor a mayor' },
  { value: 'price_desc', label: 'Precio: mayor a menor' },
]

export default function Products() {
  // Los filtros viven en la URL: se puede compartir o volver atrás sin perderlos
  const [params, setParams] = useSearchParams()
  const { data: categories } = useCategories()
  const brands = useApi(() => catalogApi.brands(), [])
  const [quick, setQuick] = useState(null)
  const searchRef = useRef(null)
  const [text, setText] = useState(params.get('q') ?? '')

  const query = Object.fromEntries(FILTERS.map((k) => [k, params.get(k) ?? '']))
  const page = Number(params.get('page')) || 1
  const key = params.toString()

  useEffect(() => setText(params.get('q') ?? ''), [params])
  useEffect(() => {
    if (params.get('buscar')) searchRef.current?.focus()
  }, [params])

  const { data, loading, error, reload } = useApi(
    (signal) => catalogApi.products({ ...query, page, per_page: 12 }, signal),
    [key],
  )

  const update = (changes) => {
    const next = new URLSearchParams(params)
    next.delete('buscar')
    Object.entries(changes).forEach(([k, v]) => (v ? next.set(k, v) : next.delete(k)))
    if (!('page' in changes)) next.delete('page')
    setParams(next)
  }

  const activeChips = [
    query.q && { key: 'q', label: `Búsqueda: ${query.q}` },
    query.category && { key: 'category', label: categories.bySlug.get(query.category)?.name ?? query.category },
    query.brand && { key: 'brand', label: brands.data?.data.find((b) => b.slug === query.brand)?.name ?? query.brand },
    query.sale_mode && { key: 'sale_mode', label: saleModes.find((m) => m.value === query.sale_mode)?.label },
    query.in_stock && { key: 'in_stock', label: 'Solo disponibles' },
  ].filter(Boolean)

  const products = data?.data ?? []
  const meta = data?.meta

  return (
    <div className="container catalog">
      <header className="catalog__head">
        <h1 className="page-title">Nuestros <em>productos</em></h1>
        <p className="page-lead">Compra en línea lo que está disponible y solicita cotización de lo demás.</p>
      </header>

      <form
        className="filters card"
        role="search"
        onSubmit={(e) => {
          e.preventDefault()
          update({ q: text.trim() })
        }}
      >
        <div className="filters__search">
          <Search size={18} aria-hidden="true" />
          <input
            ref={searchRef}
            className="input"
            type="search"
            aria-label="Buscar por nombre o referencia"
            placeholder="Nombre o referencia"
            value={text}
            onChange={(e) => setText(e.target.value)}
          />
          <button type="submit" className="btn btn--primary btn--sm">Buscar</button>
        </div>

        <select className="input" aria-label="Línea de producto" value={query.category} onChange={(e) => update({ category: e.target.value })}>
          <option value="">Todas las líneas</option>
          {categories.roots.map((root) => (
            <optgroup key={root.id} label={root.name}>
              <option value={root.slug}>Toda la línea {root.name.toLowerCase()}</option>
              {categories.childrenOf(root.id).map((child) => (
                <option key={child.id} value={child.slug}>{child.name}</option>
              ))}
            </optgroup>
          ))}
        </select>

        <select className="input" aria-label="Marca" value={query.brand} onChange={(e) => update({ brand: e.target.value })}>
          <option value="">Todas las marcas</option>
          {brands.data?.data.map((b) => <option key={b.id} value={b.slug}>{b.name}</option>)}
        </select>

        <select className="input" aria-label="Forma de compra" value={query.sale_mode} onChange={(e) => update({ sale_mode: e.target.value })}>
          {saleModes.map((m) => <option key={m.value} value={m.value}>{m.label}</option>)}
        </select>

        <select className="input" aria-label="Ordenar por" value={query.sort} onChange={(e) => update({ sort: e.target.value })}>
          {sorts.map((s) => <option key={s.value} value={s.value}>{s.label}</option>)}
        </select>

        <label className="check filters__stock">
          <input type="checkbox" checked={!!query.in_stock} onChange={(e) => update({ in_stock: e.target.checked ? '1' : '' })} />
          <span>Solo disponibles</span>
        </label>
      </form>

      {activeChips.length > 0 && (
        <div className="chips" aria-label="Filtros activos">
          {activeChips.map((chip) => (
            <button key={chip.key} type="button" className="chip" onClick={() => update({ [chip.key]: '' })}>
              {chip.label} <X size={14} aria-hidden="true" /><span className="sr-only">Quitar filtro</span>
            </button>
          ))}
          <button type="button" className="chip chip--clear" onClick={() => setParams({})}>Limpiar todo</button>
        </div>
      )}

      <p className="catalog__count" aria-live="polite">
        {loading ? 'Buscando…' : meta ? `${meta.total} ${meta.total === 1 ? 'producto' : 'productos'}` : ''}
      </p>

      {loading && <ProductGridSkeleton />}
      {!loading && error && <ErrorState error={error} onRetry={reload} />}
      {!loading && !error && products.length === 0 && (
        <EmptyState title="No encontramos productos con esos filtros">
          Prueba quitando algún filtro, o escríbenos la referencia que buscas y te la cotizamos.
        </EmptyState>
      )}
      {!loading && !error && products.length > 0 && (
        <div className="product-grid">
          {products.map((p) => <ProductCard key={p.id} product={p} onQuickView={setQuick} />)}
        </div>
      )}

      {meta && meta.last_page > 1 && (
        <nav className="pager" aria-label="Paginación">
          <button type="button" className="btn btn--ghost btn--sm" disabled={page <= 1} onClick={() => update({ page: String(page - 1) })}>
            Anterior
          </button>
          <span>Página {meta.current_page} de {meta.last_page}</span>
          <button type="button" className="btn btn--ghost btn--sm" disabled={page >= meta.last_page} onClick={() => update({ page: String(page + 1) })}>
            Siguiente
          </button>
        </nav>
      )}

      <QuickView product={quick} onClose={() => setQuick(null)} />
    </div>
  )
}
