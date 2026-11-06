import { useEffect, useMemo, useRef, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { ArrowRight, ChevronRight, MessageCircle, Search, SlidersHorizontal, X } from 'lucide-react'
import { site } from '../config/site'
import { useFamilies } from '../hooks/useFamilies'
import { useLineSearch, useLines } from '../hooks/useLines'
import { AMP_RANGES, HP_RANGES, availableRanges, filterLines, sortLines, techFilters } from '../lib/lines'
import Pill from '../components/ui/Pill'
import Eyebrow from '../components/ui/Eyebrow'
import Reveal from '../components/ui/Reveal'
import ErrorState from '../components/ui/ErrorState'
import EmptyState from '../components/ui/EmptyState'
import LineCard from '../components/catalog/LineCard'
import CatalogFilters from '../components/catalog/CatalogFilters'
import '../styles/catalog.css'

const QUICK = [
  ['Motor 5 HP', '5 HP'],
  ['Variador CFW300', 'CFW300'],
  ['Contactores', 'Contactores'],
  ['Monofásicos', 'Monofásico'],
]
const ORDERS = [
  ['', 'Recomendados'],
  ['refs', 'Más referencias'],
  ['stock', 'En bodega primero'],
]

// Los filtros viven en la URL: se pueden compartir y el botón Atrás no los pierde
function readState(params, families) {
  let family = params.get('familia') ?? ''
  let category = params.get('categoria') ?? ''
  // Enlaces anteriores: ?category=slug (familia o categoría) y ?sale_mode=quote
  const legacy = params.get('category')
  if (legacy && !family && !category) {
    const root = families.find((f) => f.slug === legacy)
    const parent = families.find((f) => f.categories.some((c) => c.slug === legacy))
    if (root) family = root.slug
    else if (parent) {
      family = parent.slug
      category = legacy
    }
  }
  const mode = params.get('modo') === 'en-linea' ? 'on' : params.get('modo') === 'cotizar' || params.get('sale_mode') === 'quote' ? 'q' : ''
  return {
    family,
    category,
    q: params.get('q') ?? '',
    mode,
    brands: (params.get('marca') ?? '').split(',').filter(Boolean),
    hp: params.get('potencia') ?? '',
    amp: params.get('corriente') ?? '',
    stock: params.get('bodega') === '1',
    order: params.get('orden') ?? '',
  }
}

function writeState(st) {
  const p = new URLSearchParams()
  if (st.family) p.set('familia', st.family)
  if (st.category) p.set('categoria', st.category)
  if (st.q) p.set('q', st.q)
  if (st.mode) p.set('modo', st.mode === 'on' ? 'en-linea' : 'cotizar')
  if (st.brands.length) p.set('marca', st.brands.join(','))
  if (st.hp) p.set('potencia', st.hp)
  if (st.amp) p.set('corriente', st.amp)
  if (st.stock) p.set('bodega', '1')
  if (st.order) p.set('orden', st.order)
  return p
}

export default function Products() {
  const [params, setParams] = useSearchParams()
  const { data: families, meta } = useFamilies()
  const { data: lines, loading, error, reload } = useLines()
  const st = useMemo(() => readState(params, families), [params, families])
  const slugs = useLineSearch(st.q)
  const [sheet, setSheet] = useState(false)
  const [term, setTerm] = useState(st.q)
  const resultsRef = useRef(null)
  const finderRef = useRef(null)

  useEffect(() => setTerm(st.q), [st.q])
  useEffect(() => {
    if (params.get('buscar')) finderRef.current?.focus()
  }, [params])

  // Hoja inferior (celular): Escape cierra y el fondo no se desplaza
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

  const set = (changes) => {
    const next = { ...st, ...changes }
    // Buscar por texto recorre todo el catálogo; elegir una familia o categoría limpia el texto
    if ('q' in changes && changes.q) Object.assign(next, { family: '', category: '' })
    if (('family' in changes || 'category' in changes) && !('q' in changes)) next.q = ''
    // Potencia y corriente solo existen en ciertas familias: al cambiar de familia se descartan
    if ('family' in changes || 'category' in changes) Object.assign(next, { hp: '', amp: '' })
    setParams(writeState(next), { replace: 'q' in changes })
  }
  const toggleBrand = (name) => set({ brands: st.brands.includes(name) ? st.brands.filter((b) => b !== name) : [...st.brands, name] })
  const reset = () => setParams(writeState({ ...st, mode: '', brands: [], hp: '', amp: '', stock: false }))
  const clearAll = () => setParams(writeState({ ...st, family: '', category: '', q: '', mode: '', brands: [], hp: '', amp: '', stock: false }))

  const tech = techFilters(st)
  const filtered = useMemo(() => filterLines(lines, { ...st, slugs }), [lines, st, slugs])
  const shown = useMemo(() => sortLines(filtered, st.order, families), [filtered, st.order, families])
  // Los chips de potencia y corriente solo ofrecen rangos que existen en la selección actual de familia y categoría
  const base = useMemo(() => filterLines(lines, { ...st, mode: '', brands: [], hp: '', amp: '', stock: false, slugs }), [lines, st, slugs])
  const ranges = useMemo(() => availableRanges(base), [base])

  const family = families.find((f) => f.slug === st.family)
  const category = family?.categories.find((c) => c.slug === st.category)
  const title = st.q ? `Resultados para “${st.q}”` : (category?.name ?? family?.name ?? 'Todo el catálogo')
  const refs = shown.reduce((n, l) => n + l.refs_count, 0)

  // Chips de filtros activos, cada uno con su X
  const chips = [
    st.q && { key: 'q', label: `Búsqueda: ${st.q}`, off: () => set({ q: '' }) },
    st.mode && { key: 'mode', label: st.mode === 'on' ? 'Compra en línea' : 'Cotizar', off: () => set({ mode: '' }) },
    ...st.brands.map((b) => ({ key: `b-${b}`, label: b, off: () => toggleBrand(b) })),
    st.stock && { key: 'stock', label: 'Con unidades en bodega', off: () => set({ stock: false }) },
    tech.hp && st.hp && { key: 'hp', label: HP_RANGES.find((r) => r.key === st.hp)?.label, off: () => set({ hp: '' }) },
    tech.amp && st.amp && { key: 'amp', label: AMP_RANGES.find((r) => r.key === st.amp)?.label, off: () => set({ amp: '' }) },
  ].filter((c) => c && c.label)
  const activeCount = chips.filter((c) => c.key !== 'q').length

  const scrollToResults = () => resultsRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  const submitFinder = (e) => {
    e.preventDefault()
    set({ q: term.trim() })
    scrollToResults()
  }
  const quick = (text) => {
    set({ q: text })
    scrollToResults()
  }

  const filtersPanel = (
    <CatalogFilters st={st} set={set} toggleBrand={toggleBrand} brands={meta.brands} families={families} ranges={ranges} tech={tech} onReset={reset} activeCount={activeCount} />
  )

  return (
    <>
      {/* Encabezado compacto: título, buscador y búsquedas frecuentes. Las cifras del catálogo viven solo en el Inicio. */}
      <section className="wrap cat-top" aria-labelledby="cat-title">
        <div className="cat-top__title">
          <Eyebrow>Catálogo industrial</Eyebrow>
          <h1 id="cat-title" className="cat-top__h1">Encuentra tu referencia</h1>
        </div>
        <div className="cat-top__find">
          <form className="cat-top__search" role="search" onSubmit={submitFinder}>
            <Search size={20} aria-hidden="true" />
            <input
              ref={finderRef}
              type="search"
              enterKeyHint="search"
              autoComplete="off"
              placeholder="Motor 5 HP 1800, CFW300, contactor 25 A…"
              aria-label="Buscar en el catálogo"
              value={term}
              onChange={(e) => setTerm(e.target.value)}
            />
            <button type="submit">Buscar</button>
          </form>
          <div className="cat-top__quick">
            <span>Búsquedas frecuentes</span>
            {QUICK.map(([label, text]) => (
              <button key={label} type="button" onClick={() => quick(text)}>{label}</button>
            ))}
          </div>
        </div>
      </section>

      <section className="wrap cat-layout" aria-label="Familias, filtros y fichas">
        {/* Columna fija: familias y filtros. El carrito no va aquí: vive en el encabezado y en la barra inferior. */}
        <aside className="bz cat-side" aria-label="Familias y filtros">
          <div className="core">
            <div className="cat-side__scroll">{filtersPanel}</div>
          </div>
        </aside>

        <div className="cat-main" ref={resultsRef}>
          <div className="cat-mbar">
            <button type="button" className="cat-mbar__f" onClick={() => setSheet(true)} aria-haspopup="dialog">
              <SlidersHorizontal size={18} aria-hidden="true" /> Filtrar{activeCount ? <b className="cat-mbar__n">{activeCount}</b> : null}
            </button>
            <label className="cat-mbar__sort">
              <span className="sr-only">Ordenar</span>
              <select value={st.order} onChange={(e) => set({ order: e.target.value })}>
                {ORDERS.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
              </select>
            </label>
          </div>

          <div className="cat-rhead">
            <div>
              <nav className="cat-crumbs" aria-label="Ruta">
                <ol>
                  <li><Link to="/productos">Catálogo</Link></li>
                  {family && (
                    <li>
                      <ChevronRight size={14} aria-hidden="true" />
                      {category ? <Link to={`/productos?familia=${family.slug}`}>{family.name}</Link> : <span aria-current="page">{family.name}</span>}
                    </li>
                  )}
                  {category && <li><ChevronRight size={14} aria-hidden="true" /><span aria-current="page">{category.name}</span></li>}
                </ol>
              </nav>
              <h2 className="h2-sys">{title}</h2>
              <p aria-live="polite">
                {loading ? 'Cargando…' : error ? 'No pudimos cargar el catálogo' : `${shown.length} ${shown.length === 1 ? 'ficha' : 'fichas'} · ${refs} referencias`}
              </p>
            </div>
            <label className="cat-sort">
              <span className="sr-only">Ordenar</span>
              <select value={st.order} onChange={(e) => set({ order: e.target.value })}>
                {ORDERS.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
              </select>
            </label>
          </div>

          {chips.length > 0 && (
            <ul className="cat-active" aria-label="Filtros activos">
              {chips.map((c) => (
                <li key={c.key}>
                  <button type="button" onClick={c.off} aria-label={`Quitar filtro: ${c.label}`}>
                    {c.label} <X size={14} aria-hidden="true" />
                  </button>
                </li>
              ))}
              {chips.length > 1 && (
                <li><button type="button" className="cat-active__all" onClick={() => setParams(writeState({ ...st, q: '', mode: '', brands: [], hp: '', amp: '', stock: false }))}>Quitar todos</button></li>
              )}
            </ul>
          )}

          {loading && (
            <div className="cat-grid" aria-busy="true" aria-label="Cargando líneas">
              {[0, 1, 2, 3, 4, 5].map((i) => <div key={i} className="skeleton cat-skel" />)}
            </div>
          )}
          {!loading && error && <ErrorState error={error} onRetry={reload} />}
          {!loading && !error && shown.length === 0 && (
            <EmptyState title="No encontramos fichas con esos filtros">
              Quita algún filtro o cuéntanos qué referencia buscas y te la cotizamos.
              <div className="cat-empty__cta"><Pill as="link" to="/carrito#cotizacion" tone="d">Solicitar cotización</Pill></div>
            </EmptyState>
          )}
          {!loading && !error && shown.length > 0 && (
            <div className="cat-grid">
              {shown.map((line, i) => (
                <Reveal key={line.slug} delay={Math.min(i, 6) * 0.04}>
                  <LineCard line={line} />
                </Reveal>
              ))}
              <Reveal className="cat-help-wrap">
                <div className="bz cat-help">
                  <div className="core">
                    <div>
                      <Eyebrow className="eyebrow--light">Cotización sin cuenta</Eyebrow>
                      <h3>¿No encuentras la referencia?</h3>
                      <p>Escríbenos qué necesitas mover, proteger o controlar y un asesor te responde con referencia y precio.</p>
                    </div>
                    <div className="cat-help__actions">
                      <Pill as="link" to="/carrito#cotizacion" tone="o">Solicitar cotización</Pill>
                      <a className="cat-help__wa" href={site.whatsappUrl} target="_blank" rel="noreferrer">
                        <MessageCircle size={16} aria-hidden="true" /> Escríbenos por WhatsApp
                      </a>
                    </div>
                  </div>
                </div>
              </Reveal>
            </div>
          )}
        </div>
      </section>

      {/* Hoja inferior (celular): todos los filtros en un panel que se desliza desde abajo */}
      <div className={`cat-sheet ${sheet ? 'open' : ''}`} role={sheet ? 'dialog' : undefined} aria-modal={sheet ? 'true' : undefined} aria-label="Filtrar el catálogo" aria-hidden={!sheet}>
        <div className="cat-sheet__bg" aria-hidden="true" onClick={() => setSheet(false)} />
        <div className="cat-sheet__panel">
          <div className="cat-sheet__grab" aria-hidden="true" />
          <div className="cat-sheet__head">
            <h2>Filtrar{activeCount ? ` (${activeCount})` : ''}</h2>
            <button type="button" className="cat-sheet__x" onClick={() => setSheet(false)} aria-label="Cerrar filtros"><X size={20} /></button>
          </div>
          <div className="cat-sheet__scroll">{filtersPanel}</div>
          <div className="cat-sheet__apply">
            <button type="button" className="cat-sheet__clear" onClick={clearAll}>Limpiar</button>
            <button type="button" className="cat-sheet__ok" onClick={() => setSheet(false)}>
              Ver {shown.length} {shown.length === 1 ? 'ficha' : 'fichas'} <ArrowRight size={16} aria-hidden="true" />
            </button>
          </div>
        </div>
      </div>
    </>
  )
}
