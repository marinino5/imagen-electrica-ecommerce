import { useCallback, useEffect, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import useEmblaCarousel from 'embla-carousel-react'
import { useReducedMotion } from 'framer-motion'
import { ChevronLeft, ChevronRight, TrendingUp } from 'lucide-react'
import { useLines } from '../../hooks/useLines'
import { useMediaQuery, useViewportWidth } from '../../hooks/useMediaQuery'
import { clamp01, useScrollFrame } from '../../hooks/useScrollFrame'
import { usePromotions } from '../../hooks/usePromotions'
import { formatCOP } from '../../lib/format'
import { lineImage, priceNote } from '../../lib/lines'
import LineVisual from '../catalog/LineVisual'
import QuickAdd from '../catalog/QuickAdd'
import SectionLabel from '../ui/SectionLabel'
import Hl from '../ui/Hl'
import Pill from '../ui/Pill'
import Reveal from '../ui/Reveal'
import Squares from '../ui/Squares'
import '../../styles/line-card.css'
import '../../styles/home2.css'

const dateFmt = new Intl.DateTimeFormat('es-CO', { day: 'numeric', month: 'long', timeZone: 'America/Bogota' })
// La vigencia llega como 'YYYY-MM-DD'
const untilText = (iso) => dateFmt.format(new Date(`${iso}T12:00:00`))
const pct = (p) => `-${String(Math.round(p.discount_percent * 10) / 10).replace('.', ',')} %`

// Tarjeta de promoción: producto recortado y, debajo, precio anterior tachado, precio nuevo grande, etiqueta naranja
// «-X %» y fecha de vigencia. Los datos vienen de /api/v1/promotions.
function PromoCard({ p }) {
  const to = `/productos/${p.line.slug}${p.sku ? `?ref=${encodeURIComponent(p.sku)}` : ''}`
  const hasOld = p.original_price !== null && p.final_price !== null && p.original_price !== p.final_price
  return (
    <article className="deal lift">
      <div className="deal__media">
        {p.line.image_url ? <img src={p.line.image_url} alt="" width="500" height="500" loading="lazy" /> : <LineVisual line={p.line} />}
        <span className="deal__tag deal__tag--promo">{p.discount_percent ? pct(p) : 'Precio especial'}</span>
      </div>
      <div className="deal__body">
        <p className="deal__line">{[p.line.brand_label, p.line.name].filter(Boolean).join(' · ')}</p>
        <h3><Link to={to}>{p.title}</Link></h3>
        <div className="deal__price">
          {p.final_price !== null ? (
            <>
              {hasOld && <s><span className="sr-only">Precio anterior </span>{formatCOP(p.original_price)}</s>}
              <strong>{p.is_from_price ? <small>Desde </small> : null}{formatCOP(p.final_price)}</strong>
            </>
          ) : (
            <strong className="deal__ask">Consulta el precio</strong>
          )}
        </div>
        <p className="deal__until">Vigente hasta el {untilText(p.ends_at)} · IVA incluido</p>
        <div className="deal__actions">
          <Link to={to} className="pc__more" aria-label={`Ver ficha de ${p.title}`}>Ver ficha</Link>
          <QuickAdd line={p.line} image={p.line.image_url} preselect={p.sku} />
        </div>
      </div>
    </article>
  )
}

// Sin promoción: la línea más pedida, con la etiqueta «Tendencia» y su precio «Desde»
function TrendCard({ line }) {
  const img = lineImage(line)
  return (
    <article className="deal lift">
      <div className="deal__media">
        {img ? <img src={img} alt="" width="500" height="500" loading="lazy" /> : <LineVisual line={line} />}
        <span className="deal__tag deal__tag--trend"><TrendingUp size={14} aria-hidden="true" /> Tendencia</span>
      </div>
      <div className="deal__body">
        <p className="deal__line">{line.brand_label ?? 'Imagen Eléctrica'}</p>
        <h3><Link to={`/productos/${line.slug}`}>{line.name}</Link></h3>
        <div className="deal__price">
          {line.price_from !== null ? <strong><small>Desde </small>{formatCOP(line.price_from)}</strong> : <strong className="deal__ask">Cotiza con un asesor</strong>}
        </div>
        <p className="deal__until">{priceNote(line)}</p>
        <div className="deal__actions">
          <Link to={`/productos/${line.slug}`} className="pc__more" aria-label={`Ver ficha de ${line.name}`}>Ver ficha</Link>
          <QuickAdd line={line} image={img} />
        </div>
      </div>
    </article>
  )
}

// «En promoción y tendencia»: primero las promociones vigentes (tabla promotions del panel); después, las fichas más
// pedidas que no estén ya en promoción, para no repetir la misma foto. Sin datos, la sección no existe.
//
// Tres presentaciones según el dispositivo:
//  · pin      (escritorio): la sección se fija 100vh y, al bajar, las tarjetas se desplazan en horizontal con una barra de
//             progreso naranja. El progreso (--p, 0 → 1) lo escribe useScrollFrame y el CSS mueve la pista (home2.css).
//  · carousel (celular): carrusel con deslizamiento táctil (Embla), sin fijar nada.
//  · grid     (escritorio con movimiento reducido): grilla estática, todas las tarjetas visibles y sin animación.
export default function HomeDeals() {
  const { data: lines, loading } = useLines()
  const { data: promos } = usePromotions()
  const wide = useMediaQuery('(min-width: 900px)')
  const reduced = useReducedMotion()
  const [ref, embla] = useEmblaCarousel({ align: 'start', containScroll: 'trimSnaps' })
  const [can, setCan] = useState({ prev: false, next: false })
  const section = useRef(null)
  const vw = useViewportWidth()
  const promoted = new Set(promos.map((p) => p.line.slug))
  const trending = lines.filter((l, i) => l.is_featured && !promoted.has(l.slug) && lines.findIndex((x) => x.slug === l.slug) === i)
  const count = promos.length + trending.length

  // Recorrido horizontal total (px), calculado con las mismas medidas del CSS (tarjeta clamp(260, 21vw, 320), hueco de 24 y
  // margen lateral alineado al contenedor de 1280): así no depende de medir el DOM mientras las tarjetas aún cargan
  const cardW = Math.min(320, Math.max(260, vw * 0.21))
  const pad = Math.max(16, (vw - 1280) / 2 + 16)
  const n = loading ? 3 : count
  const dx = Math.max(0, Math.round(pad * 2 + n * cardW + Math.max(0, n - 1) * 24 - vw))

  // Con pocas tarjetas que caben en pantalla no hay nada que recorrer: se muestran quietas
  const mode = !wide ? 'carousel' : reduced || (!loading && dx <= 24) ? 'grid' : 'pin'

  const update = useCallback(() => embla && setCan({ prev: embla.canScrollPrev(), next: embla.canScrollNext() }), [embla])
  useEffect(() => {
    if (!embla) return
    update()
    embla.on('select', update).on('reInit', update)
    return () => embla.off('select', update).off('reInit', update)
  }, [embla, update])
  useEffect(() => { embla?.reInit() }, [embla, count, mode])

  // Progreso 0 → 1 mientras la sección fijada recorre su altura extra
  useScrollFrame(() => {
    const el = section.current
    if (!el || mode !== 'pin') return
    const top = el.getBoundingClientRect().top
    el.style.setProperty('--p', clamp01(dx > 0 ? -top / dx : 0).toFixed(4))
  }, mode === 'pin', [dx, mode])

  if (!loading && count === 0) return null

  const cards = loading
    ? [0, 1, 2].map((i) => <li key={i} className="hm-rail__slide deal-slide"><div className="skeleton" style={{ height: 440, borderRadius: 8 }} /></li>)
    : (
      <>
        {promos.map((p) => <li key={`p${p.id}`} className="hm-rail__slide deal-slide"><PromoCard p={p} /></li>)}
        {trending.map((l) => <li key={l.slug} className="hm-rail__slide deal-slide"><TrendCard line={l} /></li>)}
      </>
    )

  const heading = (
    <div className="hm-head">
      <div>
        <SectionLabel n="02">Ofertas y lo más pedido</SectionLabel>
        <h2 id="h-ofertas" className="rd-h2">En promoción y <Hl>tendencia</Hl></h2>
      </div>
      <div className="hm-arrows">
        {mode === 'carousel' && (
          <>
            <button type="button" className="hm-arrow" onClick={() => embla?.scrollPrev()} disabled={!can.prev} aria-label="Anteriores"><ChevronLeft size={20} /></button>
            <button type="button" className="hm-arrow" onClick={() => embla?.scrollNext()} disabled={!can.next} aria-label="Siguientes"><ChevronRight size={20} /></button>
          </>
        )}
        <Pill as="link" to="/productos" tone="o" size="sm">Ver catálogo</Pill>
      </div>
    </div>
  )

  if (mode === 'pin') {
    return (
      <section ref={section} className="hm-deals hm-deals--pin bgt bgt-c" aria-labelledby="h-ofertas" style={{ '--dx': dx, '--p': 0 }}>
        <div className="hm-pin">
          <Squares items={[{ w: 200, h: 240, tone: 'lila', rate: 0.1, top: '10%', right: '6%' }, { w: 140, tone: 'violet', rate: -0.08, bottom: '8%', left: '3%' }]} />
          <div className="wrap">{heading}</div>
          <div className="hm-hscroll">
            <ul className="hm-hscroll__track">{cards}</ul>
          </div>
          <div className="wrap">
            <div className="hm-progress" role="presentation"><span /></div>
          </div>
        </div>
      </section>
    )
  }

  return (
    <section className={`rd-sec bgt bgt-c hm-deals hm-deals--${mode}`} aria-labelledby="h-ofertas">
      <Squares items={[{ w: 200, h: 240, tone: 'lila', rate: 0.1, top: '10%', right: '6%' }, { w: 140, tone: 'violet', rate: -0.08, bottom: '8%', left: '3%' }]} />
      <div className="wrap">
        {heading}
        {mode === 'carousel' ? (
          <Reveal>
            <div className="hm-rail" ref={ref}>
              <ul className="hm-rail__track">{cards}</ul>
            </div>
          </Reveal>
        ) : (
          <ul className="hm-dealgrid">{cards}</ul>
        )}
      </div>
    </section>
  )
}
