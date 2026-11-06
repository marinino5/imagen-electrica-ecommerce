import { useEffect, useMemo, useRef, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { ArrowRight, MessageCircle, SlidersHorizontal, X } from 'lucide-react'
import { site } from '../config/site'
import { useFamilies } from '../hooks/useFamilies'
import { useLineSearch, useLines } from '../hooks/useLines'
import { availableRanges, filterLines, lineImage, sortLines } from '../lib/lines'
import Cover from '../components/ui/Cover'
import Pill from '../components/ui/Pill'
import Eyebrow from '../components/ui/Eyebrow'
import Reveal from '../components/ui/Reveal'
import ErrorState from '../components/ui/ErrorState'
import EmptyState from '../components/ui/EmptyState'
import LineCard from '../components/catalog/LineCard'
import CatalogFilters from '../components/catalog/CatalogFilters'
import LinesNav from '../components/catalog/LinesNav'
import MiniCart from '../components/catalog/MiniCart'
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

function Plates() {
  const plates = [
    ['motores', 'Motores'],
    ['variadores-arrancadores', 'Variadores'],
    ['motorreductores', 'Motorreductores'],
  ]
  return (
    <div className="cat-stack" aria-hidden="true">
      {plates.map(([key, label]) => (
        <div key={key} className="bz cat-plate">
          <div className="core">
            <img src={`/img/categorias/${key}.webp`} alt="" width="900" height="675" />
            <span>{label}</span>
          </div>
        </div>
      ))}
    </div>
  )
}

export default function Products() {
  const [params, setParams] = useSearchParams()
  const { data: families, meta } = useFamilies()
  const { data: lines, loading, error, reload } = useLines()
  const st = useMemo(() => readState(params, families), [params, families])
  const slugs = useLineSearch(st.q)
  const [sheet, setSheet] = useState(false)
  const [tab, setTab] = useState('lin')
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
    // Buscar por texto recorre todo el catálogo; elegir línea limpia el texto
    if ('q' in changes && changes.q) Object.assign(next, { family: '', category: '' })
    if (('family' in changes || 'category' in changes) && !('q' in changes)) next.q = ''
    setParams(writeState(next), { replace: 'q' in changes })
  }
  const toggleBrand = (name) => set({ brands: st.brands.includes(name) ? st.brands.filter((b) => b !== name) : [...st.brands, name] })
  const reset = () => setParams(writeState({ ...st, mode: '', brands: [], hp: '', amp: '', stock: false }))

  const filtered = useMemo(() => filterLines(lines, { ...st, slugs }), [lines, st, slugs])
  const shown = useMemo(() => sortLines(filtered, st.order), [filtered, st.order])
  // Los chips de potencia y corriente solo ofrecen rangos que existen en la selección actual de familia y categoría
  const base = useMemo(() => filterLines(lines, { ...st, mode: '', brands: [], hp: '', amp: '', stock: false, slugs }), [lines, st, slugs])
  const ranges = useMemo(() => availableRanges(base), [base])
  const activeCount = (st.mode ? 1 : 0) + st.brands.length + (st.hp ? 1 : 0) + (st.amp ? 1 : 0) + (st.stock ? 1 : 0)
  const refs = shown.reduce((n, l) => n + l.refs_count, 0)
  const family = families.find((f) => f.slug === st.family)
  const category = family?.categories.find((c) => c.slug === st.category)
  const title = st.q ? `Resultados para “${st.q}”` : (category?.name ?? family?.name ?? 'Todo el catálogo')

  const submitFinder = (e) => {
    e.preventDefault()
    set({ q: term.trim() })
    resultsRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
  const quick = (text) => {
    set({ q: text })
    resultsRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }

  const filtersPanel = (
    <CatalogFilters st={st} set={set} toggleBrand={toggleBrand} brands={meta.brands} ranges={ranges} onReset={reset} activeCount={activeCount} />
  )

  return (
    <>
      <Cover
        eyebrow="Catálogo industrial"
        lines={['Todo para', 'mover tu planta', { text: 'en un solo lugar', accent: true }]}
        lead="Motores, variadores, control y protección de las marcas que distribuimos. Busca por referencia o por lo que necesitas mover."
        actions={
          <div className="bz cat-finder">
            <div className="core">
              <form className="cat-finder__row" role="search" onSubmit={submitFinder}>
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
                <Pill tone="o" type="submit">Buscar</Pill>
              </form>
              <div className="cat-finder__quick">
                <small>Búsquedas frecuentes</small>
                {QUICK.map(([label, text]) => (
                  <button key={label} type="button" onClick={() => quick(text)}>{label}</button>
                ))}
              </div>
            </div>
          </div>
        }
      >
        <Plates />
      </Cover>

      <ul className="wrap cat-ribbon" aria-label="El catálogo en cifras">
        <li><b>{meta.references || '351'}</b>referencias en inventario</li>
        <li><b>{meta.lines || '33'}</b>líneas de producto</li>
        <li><b>{meta.families || '8'}</b>familias del portafolio</li>
        <li><b>+{site.years}</b>años en Bucaramanga</li>
      </ul>

      <section className="wrap cat-layout" aria-label="Líneas y filtros">
        {/* Columna fija: filtros arriba, líneas debajo y el mini carrito al final */}
        <aside className="bz cat-side" aria-label="Filtros y líneas">
          <div className="core">
            <div className="cat-side__scroll">
              {filtersPanel}
              <LinesNav families={families} st={st} set={set} />
            </div>
            <MiniCart />
          </div>
        </aside>

        <div className="cat-main" ref={resultsRef}>
          <div className="cat-mbar">
            <button type="button" className="cat-mbar__f" onClick={() => setSheet(true)} aria-haspopup="dialog">
              <SlidersHorizontal size={18} aria-hidden="true" /> Líneas y filtros{activeCount ? ` (${activeCount})` : ''}
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
              <Eyebrow>{family?.name ?? 'Catálogo'}</Eyebrow>
              <h2 className="h2-sys">{title}</h2>
              <p aria-live="polite">
                {loading ? 'Cargando…' : `${shown.length} ${shown.length === 1 ? 'ficha' : 'fichas'} · ${refs} referencias`}
              </p>
            </div>
            <label className="cat-sort">
              <span className="sr-only">Ordenar</span>
              <select value={st.order} onChange={(e) => set({ order: e.target.value })}>
                {ORDERS.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
              </select>
            </label>
          </div>

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
                <Reveal key={line.slug} delay={Math.min(i, 6) * 0.04} className={i === 0 && shown.length > 2 ? 'cat-feature-wrap' : ''}>
                  <LineCard line={line} featured={i === 0 && shown.length > 2} />
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

      {/* Hoja inferior (celular): pestañas Líneas y Filtros */}
      <div className={`cat-sheet ${sheet ? 'open' : ''}`} role={sheet ? 'dialog' : undefined} aria-modal={sheet ? 'true' : undefined} aria-label="Líneas y filtros" aria-hidden={!sheet}>
        <div className="cat-sheet__bg" onClick={() => setSheet(false)} />
        <div className="cat-sheet__panel">
          <div className="cat-sheet__grab" aria-hidden="true" />
          <div className="cat-sheet__tabs" role="tablist">
            <button type="button" role="tab" aria-selected={tab === 'lin'} className={tab === 'lin' ? 'on' : ''} onClick={() => setTab('lin')}>Líneas</button>
            <button type="button" role="tab" aria-selected={tab === 'fil'} className={tab === 'fil' ? 'on' : ''} onClick={() => setTab('fil')}>
              Filtros{activeCount ? ` (${activeCount})` : ''}
            </button>
            <button type="button" className="cat-sheet__x" onClick={() => setSheet(false)} aria-label="Cerrar"><X size={20} /></button>
          </div>
          <div className="cat-sheet__scroll">
            {tab === 'lin' ? <LinesNav families={families} st={st} set={set} /> : filtersPanel}
          </div>
          <div className="cat-sheet__apply">
            <button type="button" className="cat-sheet__clear" onClick={() => { reset(); set({ family: '', category: '', q: '' }) }}>Limpiar</button>
            <button type="button" className="cat-sheet__ok" onClick={() => setSheet(false)}>
              Ver {shown.length} {shown.length === 1 ? 'ficha' : 'fichas'} <ArrowRight size={16} aria-hidden="true" />
            </button>
          </div>
        </div>
      </div>
    </>
  )
}
