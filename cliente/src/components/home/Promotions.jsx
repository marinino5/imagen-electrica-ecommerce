import { useCallback, useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import useEmblaCarousel from 'embla-carousel-react'
import { ChevronLeft, ChevronRight, Timer } from 'lucide-react'
import { usePromotions } from '../../hooks/usePromotions'
import { formatCOP } from '../../lib/format'
import LineVisual from '../catalog/LineVisual'
import SectionLabel from '../ui/SectionLabel'
import Hl from '../ui/Hl'
import Reveal from '../ui/Reveal'
import '../../styles/home.css'

const until = new Intl.DateTimeFormat('es-CO', { day: 'numeric', month: 'long', timeZone: 'America/Bogota' })
// La vigencia llega como 'YYYY-MM-DD': vence al final de ese día en hora de Bogotá (UTC-5)
const endsAt = (iso) => new Date(`${iso}T23:59:59-05:00`)
const untilText = (iso) => until.format(new Date(`${iso}T12:00:00`))

function badge(p) {
  if (p.discount_percent) return `-${String(Math.round(p.discount_percent * 10) / 10).replace('.', ',')} %`
  return 'Precio especial'
}

// Conteo regresivo solo si vence en menos de 7 días
function Countdown({ iso }) {
  const [left, setLeft] = useState(() => endsAt(iso) - Date.now())
  useEffect(() => {
    const t = setInterval(() => setLeft(endsAt(iso) - Date.now()), 1000)
    return () => clearInterval(t)
  }, [iso])
  if (left <= 0 || left > 7 * 86400000) return null
  const d = Math.floor(left / 86400000)
  const h = Math.floor((left % 86400000) / 3600000)
  const m = Math.floor((left % 3600000) / 60000)
  const s = Math.floor((left % 60000) / 1000)
  const pad = (n) => String(n).padStart(2, '0')
  return (
    <p className="promo__count" role="timer" aria-label="Tiempo que queda de la promoción">
      <Timer size={15} aria-hidden="true" /> Termina en {d > 0 ? `${d} d ` : ''}{pad(h)}:{pad(m)}:{pad(s)}
    </p>
  )
}

// Promociones del momento: salen del panel (tabla promotions). Sin promociones vigentes la sección no existe.
export default function Promotions() {
  const { data: promos } = usePromotions()
  const [ref, embla] = useEmblaCarousel({ align: 'start', containScroll: 'trimSnaps' })
  const [can, setCan] = useState({ prev: false, next: false })
  const update = useCallback(() => embla && setCan({ prev: embla.canScrollPrev(), next: embla.canScrollNext() }), [embla])
  useEffect(() => {
    if (!embla) return
    update()
    embla.on('select', update).on('reInit', update)
    return () => embla.off('select', update).off('reInit', update)
  }, [embla, update])

  if (promos.length === 0) return null

  return (
    <section className="rd-sec bg-warm hm-promos" aria-labelledby="h-promos">
      <div className="wrap">
        <div className="hm-head">
          <div>
            <SectionLabel n="04">Por tiempo limitado</SectionLabel>
            <h2 id="h-promos" className="rd-h2">Promociones <Hl>del momento</Hl></h2>
          </div>
          <div className="hm-arrows">
            <button type="button" className="hm-arrow" onClick={() => embla?.scrollPrev()} disabled={!can.prev} aria-label="Promociones anteriores"><ChevronLeft size={20} /></button>
            <button type="button" className="hm-arrow" onClick={() => embla?.scrollNext()} disabled={!can.next} aria-label="Promociones siguientes"><ChevronRight size={20} /></button>
          </div>
        </div>
        <Reveal>
          <div className="hm-rail" ref={ref}>
            <ul className="hm-rail__track">
              {promos.map((p) => {
                const to = `/productos/${p.line.slug}${p.sku ? `?ref=${encodeURIComponent(p.sku)}` : ''}`
                return (
                  <li key={p.id} className="hm-rail__slide promo-slide">
                    <article className="promo lift">
                      <div className="promo__media">
                        {p.line.image_url ? <img className="zoom prod-img" src={p.line.image_url} alt="" width="500" height="500" loading="lazy" /> : <LineVisual line={p.line} />}
                        <span className="promo__badge">{badge(p)}</span>
                      </div>
                      <div className="promo__body">
                        <p className="promo__line">{[p.line.brand_label, p.line.name].filter(Boolean).join(' · ')}</p>
                        <h3><Link to={to}>{p.title}</Link></h3>
                        {p.description && <p className="promo__desc">{p.description}</p>}
                        <p className="promo__price">
                          {p.final_price !== null ? (
                            <>
                              <strong>{p.is_from_price ? 'Desde ' : ''}{formatCOP(p.final_price)}</strong>
                              {p.original_price !== null && p.original_price !== p.final_price && <s>{formatCOP(p.original_price)}</s>}
                            </>
                          ) : (
                            <strong>Consulta el precio</strong>
                          )}
                        </p>
                        <p className="promo__until">Vigente hasta el {untilText(p.ends_at)} · IVA incluido</p>
                        <Countdown iso={p.ends_at} />
                      </div>
                    </article>
                  </li>
                )
              })}
            </ul>
          </div>
        </Reveal>
      </div>
    </section>
  )
}
