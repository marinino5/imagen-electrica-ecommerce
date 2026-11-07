import { useEffect, useMemo, useRef, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { ArrowRight, MessageCircle, Search, SlidersHorizontal, X } from 'lucide-react'
import { site } from '../config/site'
import { useFamilies } from '../hooks/useFamilies'
import { useLineSearch, useLines } from '../hooks/useLines'
import { filterLines, sortLines } from '../lib/lines'
import Pill from '../components/ui/Pill'
import Reveal from '../components/ui/Reveal'
import ErrorState from '../components/ui/ErrorState'
import EmptyState from '../components/ui/EmptyState'
import LineCard from '../components/catalog/LineCard'
import CatalogFilters from '../components/catalog/CatalogFilters'
import FamilyTree from '../components/catalog/FamilyTree'
import FamilyChips from '../components/catalog/FamilyChips'
import CatalogBanner from '../components/catalog/CatalogBanner'
import { FAMILY_BLURB, familyPhoto } from '../lib/families'
import SectionLabel from '../components/ui/SectionLabel'
import '../styles/catalog.css'

const QUICK = [
  ['Motor 5 HP', '5 HP'],
  ['Variador CFW300', 'CFW300'],
  ['Contactores', 'Contactores'],
  ['Monofásicos', 'Monofásico'],
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
    // Sin filtros de potencia, corriente, disponibilidad ni orden (se quitaron del catálogo): valores neutros
    hp: '',
    amp: '',
    stock: false,
    order: '',
  }
}

function writeState(st) {
  const p = new URLSearchParams()
  if (st.family) p.set('familia', st.family)
  if (st.category) p.set('categoria', st.category)
  if (st.q) p.set('q', st.q)
  if (st.mode) p.set('modo', st.mode === 'on' ? 'en-linea' : 'cotizar')
  if (st.brands.length) p.set('marca', st.brands.join(','))
  return p
}

export default function Products() {
  const [params, setParams] = useSearchParams()
  const { data: families, meta } = useFamilies()
  const { data: lines, loading, error, reload } = useLines()
  const st = useMemo(() => readState(params, families), [params, families])
  const slugs = useLineSearch(st.q)
  const [sheet, setSheet] = useState(false)
  const resultsRef = useRef(null)
  const finderRef = useRef(null)

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
    setParams(writeState(next), { replace: 'q' in changes })
  }
  const toggleBrand = (name) => set({ brands: st.brands.includes(name) ? st.brands.filter((b) => b !== name) : [...st.brands, name] })
  const clearAll = () => setParams(writeState({ ...st, family: '', category: '', q: '', mode: '', brands: [] }))

  const filtered = useMemo(() => filterLines(lines, { ...st, slugs }), [lines, st, slugs])
  const shown = useMemo(() => sortLines(filtered, st.order, families), [filtered, st.order, families])

  const family = families.find((f) => f.slug === st.family)
  const category = family?.categories.find((c) => c.slug === st.category)
  const title = st.q ? `Resultados para “${st.q}”` : (category?.name ?? family?.name ?? 'Todo el catálogo')
  const refs = shown.reduce((n, l) => n + l.refs_count, 0)
  const photo = family ? familyPhoto(family.slug) : null

  // Chips de filtros activos, cada uno con su X
  const chips = [
    st.q && { key: 'q', label: `Búsqueda: ${st.q}`, off: () => set({ q: '' }) },
    family && { key: 'family', label: family.name, off: () => set({ family: '', category: '' }) },
    category && { key: 'category', label: category.name, off: () => set({ category: '' }) },
    st.mode && { key: 'mode', label: st.mode === 'on' ? 'Compra en línea' : 'Cotizar', off: () => set({ mode: '' }) },
    ...st.brands.map((b) => ({ key: `b-${b}`, label: b, off: () => toggleBrand(b) })),
  ].filter((c) => c && c.label)
  const activeCount = chips.length

  const scrollToResults = () => resultsRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  const search = (q) => {
    set({ q })
    scrollToResults()
  }

  const filterProps = { st, set, toggleBrand, brands: meta.brands, finderRef }

  // La grilla intercala una tarjeta editorial de ancho completo cada 8 fichas
  const cells = []
  shown.forEach((line, i) => {
    cells.push(<Reveal key={line.slug} i={i % 4}><LineCard line={line} /></Reveal>)
    if ((i + 1) % 8 === 0 && i + 1 < shown.length) cells.push(<EditorialCard key={`ed-${i}`} n={(i + 1) / 8} />)
  })

  return (
    <>
      <CatalogBanner finderRef={finderRef} initial={st.q} onSearch={search} quick={QUICK} />

      <section className="wrap cat-layout" aria-label="Familias, filtros y fichas">
        <aside className="cat-side" aria-label="Familias">
          <div className="cat-side__scroll"><FamilyTree families={families} st={st} set={set} /></div>
        </aside>

        <div className="cat-main" ref={resultsRef}>
          <div className="cat-mfam"><FamilyChips families={families} st={st} set={set} /></div>

          {family && !st.q && (
            <header className="fam-banner">
              {photo && <img src={photo.sm} alt="" width="220" height="220" />}
              <div>
                <SectionLabel>Familia</SectionLabel>
                <h2 className="fam-banner__h">{family.name}</h2>
                <p>{family.description ?? FAMILY_BLURB[family.slug]}</p>
                <p className="fam-banner__n">{family.lines_count} {family.lines_count === 1 ? 'ficha' : 'fichas'}</p>
              </div>
            </header>
          )}

          <div className="cat-mbar">
            <button type="button" className="cat-mbar__f" onClick={() => setSheet(true)} aria-haspopup="dialog">
              <SlidersHorizontal size={18} aria-hidden="true" /> Filtrar{activeCount ? <b className="cat-mbar__n">{activeCount}</b> : null}
            </button>
          </div>
          <div className="cat-fbar"><CatalogFilters {...filterProps} /></div>

          {chips.length > 0 && (
            <ul className="cat-active" aria-label="Filtros activos">
              {chips.map((c) => (
                <li key={c.key}>
                  <button type="button" onClick={c.off} aria-label={`Quitar filtro: ${c.label}`}>{c.label} <X size={14} aria-hidden="true" /></button>
                </li>
              ))}
              <li><button type="button" className="cat-active__all" onClick={clearAll}>Limpiar</button></li>
            </ul>
          )}

          <div className="cat-rhead">
            {!(family && !st.q) && <h2 className="cat-rhead__h">{title}</h2>}
            <p aria-live="polite">
              {loading ? 'Cargando…' : error ? 'No pudimos cargar el catálogo' : `${shown.length} ${shown.length === 1 ? 'ficha' : 'fichas'} · ${refs} referencias`}
            </p>
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
          {!loading && !error && shown.length > 0 && <div className="cat-grid">{cells}</div>}
        </div>
      </section>

      {/* Hoja inferior (celular): los filtros en un panel que se desliza desde abajo */}
      <div className={`cat-sheet ${sheet ? 'open' : ''}`} role={sheet ? 'dialog' : undefined} aria-modal={sheet ? 'true' : undefined} aria-label="Filtrar el catálogo" aria-hidden={!sheet}>
        <div className="cat-sheet__bg" aria-hidden="true" onClick={() => setSheet(false)} />
        <div className="cat-sheet__panel">
          <div className="cat-sheet__grab" aria-hidden="true" />
          <div className="cat-sheet__head">
            <h2>Filtrar{activeCount ? ` (${activeCount})` : ''}</h2>
            <button type="button" className="cat-sheet__x" onClick={() => setSheet(false)} aria-label="Cerrar filtros"><X size={20} /></button>
          </div>
          <div className="cat-sheet__scroll"><CatalogFilters {...filterProps} sheet /></div>
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

// Tarjeta editorial de ancho completo entre las fichas
const EDITORIAL = [
  { title: '¿No sabes qué motor necesitas? Un asesor te ayuda', text: 'Cuéntanos la carga, la tensión y el tipo de arranque y te recomendamos la referencia exacta.', photo: '/img/fotos/planta-operando.webp', alt: 'Operario revisando un motor en una planta industrial' },
  { title: 'Armas un tablero y necesitas una lista completa', text: 'Envíanos tu lista y te cotizamos contactores, relés, guardamotores y borneras en un solo mensaje.', photo: '/img/fotos/tablero-manos.webp', alt: 'Manos de un técnico trabajando en un tablero eléctrico' },
  { title: '¿Dudas entre dos referencias?', text: 'Dinos la aplicación y te mostramos la diferencia, con disponibilidad en bodega y precio.', photo: '/img/fotos/electricista-tablero.webp', alt: 'Electricista comparando referencias en un tablero' },
  { title: '¿Compras para una obra o una planta?', text: 'Coordinamos la entrega en tu obra y te acompañamos con la compra recurrente.', photo: '/img/fotos/despacho-montacargas.webp', alt: 'Bodega con montacargas y estantería de material' },
]
function EditorialCard({ n }) {
  const e = EDITORIAL[(n - 1) % EDITORIAL.length]
  return (
    <Reveal className="cat-editorial">
      <article className="ed">
        <img src={e.photo} alt={e.alt} width="480" height="320" loading="lazy" />
        <div className="ed__txt">
          <SectionLabel>Asesoría</SectionLabel>
          <h3>{e.title}</h3>
          <p>{e.text}</p>
          <a className="pill pill--o" href={site.whatsappUrl} target="_blank" rel="noreferrer">
            <span className="pill__label">Escribir por WhatsApp</span>
            <span className="pill__ic" aria-hidden="true"><MessageCircle size={18} /></span>
          </a>
        </div>
      </article>
    </Reveal>
  )
}
