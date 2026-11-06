import { useEffect, useRef, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import { MessageCircle, Search, SlidersHorizontal, X } from 'lucide-react'
import { site } from '../config/site'
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
  const [sheet, setSheet] = useState(false)
  const searchRef = useRef(null)
  const [text, setText] = useState(params.get('q') ?? '')

  const query = Object.fromEntries(FILTERS.map((k) => [k, params.get(k) ?? '']))
  const page = Number(params.get('page')) || 1
  const key = params.toString()

  useEffect(() => setText(params.get('q') ?? ''), [params])
  useEffect(() => {
    if (!sheet) return
    const onKey = (e) => e.key === 'Escape' && setSheet(false)
    document.addEventListener('keydown', onKey)
    document.body.style.overflow = 'hidden'
    return () => {
      document.removeEventListener('keydown', onKey)
      document.body.style.overflow = ''
    }
  }, [sheet])
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

  // Filtros activos sin contar la búsqueda ni el orden (los que viven en el panel)
  const filterCount = ['category', 'brand', 'sale_mode', 'in_stock'].filter((k) => query[k]).length

  const products = data?.data ?? []
  const meta = data?.meta

  return (
    <div className="container catalog">
      <header className="catalog__head">
        <h1 className="page-title">Nuestros <em>productos</em></h1>
        <p className="page-lead">Compra en línea lo que está disponible y solicita cotización de lo demás.</p>
      </header>

      <a className="catalog__banner" href={site.whatsappLink('Hola, busco una referencia que no encuentro en la tienda.')} target="_blank" rel="noreferrer">
        <MessageCircle size={20} aria-hidden="true" />
        <span><strong>¿No encuentras la referencia?</strong> Escríbele a {site.mainContact.name} y te la cotizamos.</span>
      </a>

      <form
        className="filters"
        role="search"
        onSubmit={(e) => {
          e.preventDefault()
          update({ q: text.trim() })
        }}
      >
        <div className="filters__search">
          <Search size={20} aria-hidden="true" />
          <input
            ref={searchRef}
            className="input"
            type="search"
            enterKeyHint="search"
            autoComplete="off"
            aria-label="Buscar por nombre o referencia"
            placeholder="Busca por nombre o referencia"
            value={text}
            onChange={(e) => setText(e.target.value)}
          />
          <button type="submit" className="btn btn--primary">Buscar</button>
        </div>

        <div className="filters__bar">
          <button type="button" className="btn btn--ghost filters__open" onClick={() => setSheet(true)} aria-haspopup="dialog">
            <SlidersHorizontal size={18} aria-hidden="true" /> Filtrar{filterCount > 0 ? ` (${filterCount})` : ''}
          </button>
          <select className="input filters__sort" aria-label="Ordenar por" value={query.sort} onChange={(e) => update({ sort: e.target.value })}>
            {sorts.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
          </select>
        </div>

        {sheet && <div className="filters__backdrop" onClick={() => setSheet(false)} aria-hidden="true" />}
        <div
          className={`filters__panel ${sheet ? 'is-open' : ''}`}
          role={sheet ? 'dialog' : undefined}
          aria-modal={sheet ? 'true' : undefined}
          aria-label="Filtros"
        >
          <div className="filters__panel-head">
            <h2>Filtros</h2>
            <button type="button" className="icon-btn" onClick={() => setSheet(false)} aria-label="Cerrar filtros"><X size={22} /></button>
          </div>

          <div className="field">
            <label htmlFor="f-cat">Línea de producto</label>
            <select id="f-cat" className="input" value={query.category} onChange={(e) => update({ category: e.target.value })}>
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
          </div>

          <div className="field">
            <label htmlFor="f-brand">Marca</label>
            <select id="f-brand" className="input" value={query.brand} onChange={(e) => update({ brand: e.target.value })}>
              <option value="">Todas las marcas</option>
              {brands.data?.data.map((b) => <option key={b.id} value={b.slug}>{b.name}</option>)}
            </select>
          </div>

          <div className="field">
            <label htmlFor="f-mode">Forma de compra</label>
            <select id="f-mode" className="input" value={query.sale_mode} onChange={(e) => update({ sale_mode: e.target.value })}>
              {saleModes.map((m) => <option key={m.value} value={m.value}>{m.label}</option>)}
            </select>
          </div>

          <label className="check filters__stock">
            <input type="checkbox" checked={!!query.in_stock} onChange={(e) => update({ in_stock: e.target.checked ? '1' : '' })} />
            <span>Solo disponibles</span>
          </label>

          <div className="filters__panel-foot">
            <button type="button" className="btn btn--ghost" onClick={() => { setParams({}); setSheet(false) }} disabled={filterCount === 0 && !query.q}>Limpiar</button>
            <button type="button" className="btn btn--accent" onClick={() => setSheet(false)}>
              {meta ? `Ver ${meta.total} ${meta.total === 1 ? 'producto' : 'productos'}` : 'Aplicar'}
            </button>
          </div>
        </div>
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
