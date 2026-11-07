import { useEffect, useId, useLayoutEffect, useRef, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { Check, FileText, MapPin, Plus, ShoppingCart, Store, Truck } from 'lucide-react'
import { DELIVERY, FAQS, MODES, STEPS } from '../components/comprar/modeData'
import LaptopMock from '../components/comprar/LaptopMock'
import PhoneMock from '../components/comprar/PhoneMock'
import Bgfx from '../components/ui/Bgfx'
import ColombiaMap from '../components/ui/ColombiaMap'
import Hl from '../components/ui/Hl'
import Mix from '../components/ui/Mix'
import Pill from '../components/ui/Pill'
import { useInView } from '../components/ui/useInView'
import '../styles/como-comprar.css'
import '../styles/ie-pages.css'

const clamp = (n, lo, hi) => Math.min(hi, Math.max(lo, n))
const reduced = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches

/**
 * El «circuito»: una línea naranja de 3 px serpentea por la sección y une los cuatro pasos. Se dibuja sobre las posiciones
 * reales de los bornes (se mide el DOM), de modo que sigue encajando al cambiar el ancho o cargar las imágenes. Con el scroll
 * se «enciende» (stroke-dashoffset) con un punto luminoso en la punta; cada borne y su tarjeta se activan cuando la corriente
 * llega a ellos. Sin movimiento (prefers-reduced-motion) queda encendido por completo.
 */
function Circuit({ mode }) {
  const steps = STEPS[mode]
  const box = useRef(null)
  const base = useRef(null)
  const lit = useRef(null)
  const dot = useRef(null)
  const nodes = useRef([])
  const geo = useRef({ pts: [], len: 0 })
  const [d, setD] = useState('')
  const [size, setSize] = useState({ w: 0, h: 0 })
  const [reach, setReach] = useState(reduced() ? steps.length : 0)

  const paint = () => {
    const el = box.current
    const path = lit.current
    if (!el || !path || !geo.current.len) return
    const { len, pts } = geo.current
    const r = el.getBoundingClientRect()
    const target = reduced() ? r.height : clamp(window.innerHeight * 0.62 - r.top, 0, r.height)
    // La curva solo baja: se busca por bisección el largo cuya punta llega a la altura `target`
    let lo = 0
    let hi = len
    for (let i = 0; i < 22; i += 1) {
      const mid = (lo + hi) / 2
      if (path.getPointAtLength(mid).y < target) lo = mid
      else hi = mid
    }
    path.style.strokeDashoffset = String(len - lo)
    const tip = path.getPointAtLength(lo)
    if (dot.current) {
      dot.current.setAttribute('cx', tip.x)
      dot.current.setAttribute('cy', tip.y)
      dot.current.style.opacity = lo > 2 && lo < len - 2 ? '1' : '0'
    }
    setReach(pts.filter((p) => p.y <= target + 4).length)
  }

  // Mide los bornes y dibuja el recorrido: recto en el celular, con curvas alternas en escritorio
  useLayoutEffect(() => {
    const el = box.current
    if (!el) return undefined
    const build = () => {
      const r = el.getBoundingClientRect()
      const wide = window.matchMedia('(min-width: 900px)').matches
      const pts = nodes.current.filter(Boolean).map((n) => {
        const b = n.getBoundingClientRect()
        return { x: b.left + b.width / 2 - r.left, y: b.top + b.height / 2 - r.top }
      })
      if (pts.length < 2) return
      const amp = wide ? 46 : 0
      let path = `M${pts[0].x} 0V${pts[0].y}`
      for (let i = 0; i < pts.length - 1; i += 1) {
        const p = pts[i]
        const q = pts[i + 1]
        const dir = i % 2 === 0 ? -1 : 1
        const dy = q.y - p.y
        path += `C${p.x + amp * dir} ${p.y + dy * 0.3} ${q.x + amp * dir} ${q.y - dy * 0.3} ${q.x} ${q.y}`
      }
      path += `V${r.height}`
      geo.current.pts = pts
      setSize({ w: r.width, h: r.height })
      setD(path)
    }
    build()
    const ro = new ResizeObserver(build)
    ro.observe(el)
    return () => ro.disconnect()
  }, [mode])

  useEffect(() => {
    if (!d || !lit.current) return undefined
    const len = lit.current.getTotalLength()
    geo.current.len = len
    lit.current.style.strokeDasharray = String(len)
    lit.current.style.strokeDashoffset = String(len)
    let frame = 0
    const onScroll = () => {
      if (!frame) frame = requestAnimationFrame(() => { frame = 0; paint() })
    }
    paint()
    window.addEventListener('scroll', onScroll, { passive: true })
    window.addEventListener('resize', onScroll)
    return () => {
      cancelAnimationFrame(frame)
      window.removeEventListener('scroll', onScroll)
      window.removeEventListener('resize', onScroll)
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [d])

  return (
    <section className="cc bg-lila-fade" aria-labelledby="h-pasos">
      <Bgfx d b />
      <div className="wrap">
        <h2 id="h-pasos" className="rd-h2 cc__title">Así funciona la <Hl>{mode === 'direct' ? 'compra en línea' : 'cotización'}</Hl></h2>
        <ol className="cc__list" ref={box}>
          <svg className="cc__wire" width={size.w} height={size.h} viewBox={`0 0 ${size.w || 1} ${size.h || 1}`} aria-hidden="true" focusable="false">
            <path ref={base} className="cc__wire-base" d={d} />
            <path ref={lit} className="cc__wire-lit" d={d} />
            <circle ref={dot} className="cc__spark" r="7" cx="0" cy="0" />
          </svg>
          {steps.map((s, i) => (
            <li key={s.title} className={`cc__step ${i % 2 === 1 ? 'is-flip' : ''} ${i < reach ? 'is-on' : ''}`}>
              <span className="cc__node" ref={(n) => { nodes.current[i] = n }} aria-hidden="true">{i + 1}</span>
              <article className="cc__card tc">
                <h3><span className="sr-only">Paso {i + 1}: </span>{s.title}</h3>
                <p>{s.text}</p>
                <div className={`cc__media cc__media--${s.mock === 'laptop' ? 'laptop' : s.photo ? 'photo' : 'phone'}`}>
                  {s.photo && <img src={s.photo} alt={s.alt} width="800" height="600" loading="lazy" />}
                  {!s.photo && s.mock === 'laptop' && <LaptopMock alt={s.alt} />}
                  {!s.photo && s.mock !== 'laptop' && <PhoneMock mock={s.mock} chat={s.chat} alt={s.alt} />}
                </div>
              </article>
            </li>
          ))}
        </ol>
      </div>
    </section>
  )
}

function Delivery() {
  const icons = { pickup: Store, local: Truck, national: MapPin }
  const [ref, seen] = useInView({ threshold: 0.25 })
  return (
    <section className="rd-sec bg-lav how-delivery" aria-labelledby="h-entregas">
      <Bgfx e c="r" arcTop="14%" />
      <div className="wrap">
        <h2 id="h-entregas" className="rd-h2">Recíbelo <Hl>donde estés</Hl></h2>
        <div className="how-delivery__grid">
          <ul className="how-delivery__cards">
            {DELIVERY.map((d) => {
              const Icon = icons[d.id]
              return (
                <li key={d.id}>
                  <div className="how-dcard lift">
                    <span className="how-dcard__ic"><Icon size={24} aria-hidden="true" /></span>
                    <div>
                      <span className="how-dcard__tag">{d.tag}</span>
                      <h3>{d.title}</h3>
                      <p>{d.text}</p>
                    </div>
                  </div>
                </li>
              )
            })}
          </ul>
          <div className={`how-map ${seen ? 'is-in' : ''}`} ref={ref}>
            <ColombiaMap className="how-map__svg" routes />
            <p><strong>Santander · Bucaramanga</strong> Recoges en la tienda y llevamos a todo el país.</p>
          </div>
        </div>
      </div>
    </section>
  )
}

function FaqItem({ q, a, link }) {
  const id = useId()
  const [open, setOpen] = useState(false)
  return (
    <div className={`faq__item ${open ? 'is-open' : ''}`}>
      <h3>
        <button type="button" aria-expanded={open} aria-controls={id} onClick={() => setOpen((o) => !o)}>
          <span>{q}</span>
          <span className="faq__plus" aria-hidden="true"><Plus size={20} /></span>
        </button>
      </h3>
      <div id={id} role="region" className="faq__panel" aria-hidden={!open}>
        <div><p>{a}</p>{link && <p><Link to={link.to} className="faq__link" tabIndex={open ? 0 : -1}>{link.label} →</Link></p>}</div>
      </div>
    </div>
  )
}

function Faq({ mode }) {
  const items = FAQS.filter((f) => f.modes.includes(mode))
  return (
    <section className="rd-sec bg-warm how-faq" aria-labelledby="h-faq">
      <Bgfx a="motor:l" d />
      <div className="wrap how-faq__wrap">
        <div>
          <h2 id="h-faq" className="rd-h2">Resolvemos tus <Hl>dudas</Hl></h2>
        </div>
        <div className="faq">
          {items.map((item) => <FaqItem key={item.q} {...item} />)}
        </div>
      </div>
    </section>
  )
}

export default function ComoComprar() {
  const [params] = useSearchParams()
  const [mode, setMode] = useState(params.get('modo') === 'cotizacion' ? 'quote' : 'direct')
  const current = MODES[mode]

  return (
    <>
      {/* Hero dividido: texto y selector a la izquierda; a la derecha una foto en marco redondeado que «enciende» el hilo del circuito */}
      <section className="how-hero2 bg-lila-fade" aria-labelledby="h-how">
        <Bgfx a="contactor:r" e />
        <div className="wrap how-hero2__grid">
          <div className="how-hero2__text">
            <h1 id="h-how" className="ie-h1"><Mix light="Comprar es">así de <Hl>fácil</Hl></Mix></h1>
            <p className="rd-lead" key={mode}>{current.lead}</p>
            <p className="how-q" id="how-q">Elige cómo quieres comprar</p>
            <div className="how-opts" role="radiogroup" aria-labelledby="how-q">
              {Object.values(MODES).map((m) => {
                const Icon = m.id === 'direct' ? ShoppingCart : FileText
                const on = mode === m.id
                return (
                  <button key={m.id} type="button" role="radio" aria-checked={on} className={`how-opt ${on ? 'is-on' : ''}`} onClick={() => setMode(m.id)}>
                    <span className="how-opt__ic"><Icon size={20} aria-hidden="true" /></span>
                    <strong>{m.label}</strong>
                    <small>{m.short}</small>
                    {on && <span className="how-opt__check" aria-hidden="true"><Check size={14} strokeWidth={3} /></span>}
                  </button>
                )
              })}
            </div>
            <ul className="how-needs" aria-live="polite" key={`needs-${mode}`}>
              {current.needs.map((n) => <li key={n}>{n}</li>)}
            </ul>
            <Pill as="link" to={current.cta.to} tone="o" key={`cta-${mode}`}>{current.cta.label}</Pill>
          </div>
          <figure className="how-hero2__photo">
            <img src="/img/fotos/busca-portatil.webp" alt="Persona buscando una referencia en un portátil junto a una máquina industrial" width="1280" height="960" fetchPriority="high" loading="eager" />
            <svg className="how-hero2__wire" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true" focusable="false">
              <path pathLength="1" d="M0 0V100H100" />
            </svg>
          </figure>
        </div>
      </section>

      <Circuit mode={mode} key={mode} />
      <Delivery />
      <Faq mode={mode} key={`faq-${mode}`} />
    </>
  )
}
