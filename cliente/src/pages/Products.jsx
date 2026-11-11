import { useEffect, useMemo, useRef, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { ArrowRight, MessageCircle, Search, SlidersHorizontal, X } from 'lucide-react'
import { site } from '../config/site'
import { useFamilies } from '../hooks/useFamilies'
import { useMediaQuery } from '../hooks/useMediaQuery'
import { useScrollSpy } from '../hooks/useScrollSpy'
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
import Squares from '../components/ui/Squares'
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
    // Sin filtros de potencia, corriente ni orden (se quitaron del catálogo): valores neutros
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

  // Alto real de la barra fija (filtros en escritorio; botón + chips de familia en celular). Las cabeceras de familia y la
  // columna se fijan 16 px debajo de ella: si la barra cambia de alto (filtros en dos líneas), se recalculan solas.
  useEffect(() => {
    const wrap = resultsRef.current
    if (!wrap) return undefined
    const measure = () => {
      const bar = wrap.querySelector('.cat-fbar')
      const barShown = bar && getComputedStyle(bar).display !== 'none'
      const h = barShown ? bar.offsetHeight : (wrap.querySelector('.cat-mbar')?.offsetHeight ?? 0) + 6 + (wrap.querySelector('.cat-mfam')?.offsetHeight ?? 0)
      wrap.style.setProperty('--fbar-h', `${h}px`)
    }
    measure()
    const ro = new ResizeObserver(measure)
    wrap.querySelectorAll('.cat-fbar, .cat-mbar, .cat-mfam').forEach((el) => ro.observe(el))
    window.addEventListener('resize', measure)
    return () => {
      ro.disconnect()
      window.removeEventListener('resize', measure)
    }
  }, [])

  // «?buscar=1» (icono de lupa del encabezado): enfoca el buscador de la barra de filtros; en celular, donde esa barra
  // vive dentro de la hoja de filtros, abre la hoja
  useEffect(() => {
    if (!params.get('buscar')) return
    const input = finderRef.current
    if (input && input.offsetParent) input.focus({ preventScroll: true })
    else setSheet(true)
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
  // Una búsqueda frecuente llena el buscador de la barra de filtros y baja a los resultados
  const search = (q) => {
    set({ q })
    scrollToResults()
    const input = finderRef.current
    if (input && input.offsetParent) input.focus({ preventScroll: true })
  }

  const filterProps = { st, set, toggleBrand, brands: meta.brands, finderRef }

  // «Todo el catálogo» (sin familia ni categoría) se agrupa por familia, en el orden del portafolio; cada grupo lleva una
  // cabecera fija. Con una familia elegida hay un solo grupo y no hay cabeceras.
  const grouped = !st.family && !st.category
  const groups = useMemo(() => {
    if (!grouped) return []
    const map = new Map()
    shown.forEach((l) => {
      const slug = l.family?.slug ?? 'otros'
      if (!map.has(slug)) map.set(slug, { slug, name: l.family?.name ?? 'Otros', lines: [] })
      map.get(slug).lines.push(l)
    })
    return [...map.values()]
  }, [grouped, shown])
  const phone = useMediaQuery('(max-width: 960px)')
  // Línea (px desde arriba) donde quedan las cabeceras fijas: la familia cuyo grupo ya la cruzó es la que se está viendo
  const spy = useScrollSpy(groups.map((g) => g.slug), phone ? 240 : 150)
  // Salta a una familia; las imágenes perezosas de las fichas de arriba cambian la altura mientras se desplaza, así que se
  // reajusta una vez al terminar para que la cabecera quede justo bajo la barra fija
  const jump = (slug) => {
    const el = document.getElementById(`fgroup-${slug}`)
    el?.scrollIntoView({ behavior: 'smooth', block: 'start' })
    window.setTimeout(() => el?.scrollIntoView({ behavior: 'smooth', block: 'start' }), 700)
  }

  // Una sola franja editorial por página (así su foto nunca se repite): va en el primer grupo con más de seis fichas, después de la sexta
  const edGroup = grouped ? groups.findIndex((g) => g.lines.length > 6) : 0
  const cellsFor = (lines, g) =>
    lines.flatMap((line, i) => {
      const cell = <Reveal key={line.slug} i={i % 3}><LineCard line={line} /></Reveal>
      return i === 5 && lines.length > 6 && g === edGroup ? [cell, <EditorialStrip key="ed" e={EDITORIAL} />] : [cell]
    })

  return (
    <>
      <CatalogBanner onQuick={search} quick={QUICK} />

      <div className="cat-body bgt bgt-a">
      <Squares items={[{ w: 200, h: 240, tone: 'lila', rate: 0.08, top: '6%', right: '-60px' }, { w: 140, tone: 'orange', rate: -0.1, top: '48%', left: '-50px' }]} />
      <div className="wrap cat-wrap" ref={resultsRef}>
        {/* Barra de herramientas: hijos directos del bloque alto para que se queden fijos mientras se recorre el catálogo */}
        <div className="cat-mbar">
          <button type="button" className="cat-mbar__f" onClick={() => setSheet(true)} aria-haspopup="dialog">
            <SlidersHorizontal size={18} aria-hidden="true" /> Filtrar{activeCount ? <b className="cat-mbar__n">{activeCount}</b> : null}
          </button>
        </div>
        <div className="cat-mfam"><FamilyChips families={families} st={st} set={set} grouped={grouped} spy={spy} onJump={jump} /></div>
        <div className="cat-fbar"><CatalogFilters {...filterProps} /></div>

        <section className="cat-layout" aria-label="Familias, filtros y fichas">
          <aside className="cat-side" aria-label="Familias">
            <div className="cat-side__scroll"><FamilyTree families={families} st={st} set={set} grouped={grouped} spy={spy} onJump={jump} /></div>
          </aside>

          <div className="cat-main">
            {family && !st.q && (
              <header className="fam-banner">
                {photo && <img src={photo.sm} alt="" width="220" height="220" />}
                <div>
                  <h2 className="fam-banner__h">{family.name}</h2>
                  <p>{family.description ?? FAMILY_BLURB[family.slug]}</p>
                  <p className="fam-banner__n">{family.lines_count} {family.lines_count === 1 ? 'ficha' : 'fichas'}</p>
                </div>
              </header>
            )}

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
                Quita algún filtro o cuéntanos qué buscas y te lo cotizamos.
                <strong className="cat-empty__ask">¿No encontraste lo que buscas? Cotízalo aquí</strong>
                <div className="cat-empty__cta"><Pill as="link" to="/cotizar" tone="o">Cotizar lo que necesito</Pill></div>
              </EmptyState>
            )}
            {!loading && !error && shown.length > 0 && (grouped ? (
              <div className="cat-groups">
                {groups.map((g, gi) => {
                  const fam = families.find((f) => f.slug === g.slug)
                  const img = familyPhoto(g.slug)
                  return (
                    <section key={g.slug} id={`fgroup-${g.slug}`} className="fgroup" aria-labelledby={`fgh-${g.slug}`}>
                      <header className="fgh">
                        {img && <img src={img.sm} alt="" width="64" height="64" loading="lazy" />}
                        <h2 id={`fgh-${g.slug}`}>{fam?.name ?? g.name}</h2>
                        <span className="fgh__n">{g.lines.length} {g.lines.length === 1 ? 'línea' : 'líneas'}</span>
                        <Link to={`/productos?familia=${g.slug}`} className="fgh__more" aria-label={`Ver solo ${fam?.name ?? g.name}`}><span>Ver solo esta familia</span> <ArrowRight size={16} aria-hidden="true" /></Link>
                      </header>
                      <div className="cat-grid">{cellsFor(g.lines, gi)}</div>
                    </section>
                  )
                })}
              </div>
            ) : <div className="cat-grid">{cellsFor(shown, 0)}</div>)}
          </div>
        </section>
      </div>
      </div>

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

// Tarjeta editorial de ancho completo entre las fichas (foto: electricista conectando un tablero, Pixabay)
const EDITORIAL = {
  title: '¿No sabes qué motor necesitas? Un asesor te ayuda',
  text: 'Cuéntanos la carga, la tensión y el tipo de arranque y te recomendamos la referencia exacta.',
  photo: '/img/fotos/electricista-tablero.webp',
  alt: 'Electricista conectando un tablero eléctrico',
}
// Franja editorial: foto a la izquierda (40 %), texto y botón a la derecha, sobre violeta-950 con un arco naranja fino
function EditorialStrip({ e }) {
  return (
    <Reveal className="cat-editorial">
      <article className="ed">
        <img src={e.photo} alt={e.alt} width="480" height="220" loading="lazy" />
        <div className="ed__txt">
          <h3>{e.title}</h3>
          <p>{e.text}</p>
          <a className="ed__btn" href={site.whatsappUrl} target="_blank" rel="noreferrer"><MessageCircle size={18} aria-hidden="true" /> Escribir por WhatsApp</a>
        </div>
        <svg className="ed__arc" viewBox="0 0 220 220" aria-hidden="true" focusable="false"><path d="M220 0A220 220 0 0 0 0 220" /></svg>
      </article>
    </Reveal>
  )
}
