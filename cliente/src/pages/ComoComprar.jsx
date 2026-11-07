import { useEffect, useId, useRef, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import { FileText, MapPin, Plus, ShoppingCart, Store, Truck } from 'lucide-react'
import { DELIVERY, FAQS, MODES, STEPS } from '../components/comprar/modeData'
import PhoneMock from '../components/comprar/PhoneMock'
import ColombiaMap from '../components/ui/ColombiaMap'
import Hl from '../components/ui/Hl'
import Pill from '../components/ui/Pill'
import Reveal from '../components/ui/Reveal'
import SectionLabel from '../components/ui/SectionLabel'
import Arc from '../components/ui/Arc'
import { useInView } from '../components/ui/useInView'
import '../styles/como-comprar.css'

// Línea de tiempo vertical: la línea naranja se llena con el scroll y cada paso es un bloque foto + texto alternado
function Journey({ mode }) {
  const listRef = useRef(null)
  const steps = STEPS[mode]

  useEffect(() => {
    const el = listRef.current
    if (!el) return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      el.style.setProperty('--fill', '1')
      return
    }
    let frame = 0
    const update = () => {
      frame = 0
      const r = el.getBoundingClientRect()
      el.style.setProperty('--fill', String(Math.min(1, Math.max(0, (window.innerHeight * 0.6 - r.top) / r.height))))
    }
    const onScroll = () => {
      if (!frame) frame = requestAnimationFrame(update)
    }
    update()
    window.addEventListener('scroll', onScroll, { passive: true })
    window.addEventListener('resize', onScroll)
    return () => {
      cancelAnimationFrame(frame)
      window.removeEventListener('scroll', onScroll)
      window.removeEventListener('resize', onScroll)
    }
  }, [mode])

  return (
    <section className="rd-sec bg-white how-journey" aria-labelledby="h-pasos">
      <div className="wrap">
        <SectionLabel n="01">Paso a paso</SectionLabel>
        <h2 id="h-pasos" className="rd-h2">Así funciona la <Hl>{mode === 'direct' ? 'compra en línea' : 'cotización'}</Hl></h2>
        <ol className="tl" ref={listRef}>
          <span className="tl__line" aria-hidden="true"><span className="tl__fill" /></span>
          {steps.map((s, i) => {
            const flip = i % 2 === 1
            return (
              <li key={s.title} className={`tl__step ${flip ? 'is-flip' : ''}`}>
                <span className="tl__node" aria-hidden="true">{i + 1}</span>
                <Reveal side={flip ? 'r' : 'l'} className="tl__media">
                  {s.photo ? (
                    <div className="capsule tl__capsule"><img src={s.photo} alt={s.alt} width="800" height="600" loading="lazy" /></div>
                  ) : (
                    <div className="tl__mock"><PhoneMock mock={s.mock} chat={s.chat} alt={s.alt} /></div>
                  )}
                </Reveal>
                <Reveal side={flip ? 'l' : 'r'} delay={0.12} className="tl__text">
                  <h3><span className="sr-only">Paso {i + 1}: </span>{s.title}</h3>
                  <p>{s.text}</p>
                </Reveal>
              </li>
            )
          })}
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
      <Arc pos="tr" />
      <div className="wrap">
        <SectionLabel n="02">Entregas</SectionLabel>
        <h2 id="h-entregas" className="rd-h2">Recíbelo <Hl>donde estés</Hl></h2>
        <div className="how-delivery__grid">
          <ul className="how-delivery__cards">
            {DELIVERY.map((d, i) => {
              const Icon = icons[d.id]
              return (
                <Reveal as="li" key={d.id} i={i}>
                  <div className="how-dcard lift">
                    <span className="how-dcard__ic"><Icon size={24} aria-hidden="true" /></span>
                    <div>
                      <span className="how-dcard__tag">{d.tag}</span>
                      <h3>{d.title}</h3>
                      <p>{d.text}</p>
                    </div>
                  </div>
                </Reveal>
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

function FaqItem({ q, a }) {
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
        <div><p>{a}</p></div>
      </div>
    </div>
  )
}

function Faq({ mode }) {
  const items = FAQS.filter((f) => f.modes.includes(mode))
  return (
    <section className="rd-sec bg-warm how-faq" aria-labelledby="h-faq">
      <div className="wrap how-faq__wrap">
        <div>
          <SectionLabel n="03">Preguntas frecuentes</SectionLabel>
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
      <section className="how-hero on-dark" aria-labelledby="h-how">
        <img className="how-hero__bg" src="/img/fotos/bodega-pasillo.webp" alt="" width="1920" height="1280" fetchPriority="high" />
        <div className="wrap how-hero__grid">
          <div>
            <p className="how-hero__tag">Cómo comprar</p>
            <h1 id="h-how">Comprar es <strong>así de fácil</strong></h1>
            <p className="how-hero__lead" key={mode}>{current.lead}</p>
            <Pill as="link" to={current.cta.to} tone="o" key={`cta-${mode}`}>{current.cta.label}</Pill>
          </div>
          <div className="how-switch">
            <p className="how-switch__q" id="how-q">Elige cómo quieres comprar</p>
            <div className="how-switch__opts" role="radiogroup" aria-labelledby="how-q">
              {Object.values(MODES).map((m) => {
                const Icon = m.id === 'direct' ? ShoppingCart : FileText
                return (
                  <button key={m.id} type="button" role="radio" aria-checked={mode === m.id} className={mode === m.id ? 'is-on' : ''} onClick={() => setMode(m.id)}>
                    <span className="how-switch__ic"><Icon size={22} aria-hidden="true" /></span>
                    <strong>{m.label}</strong>
                    <small>{m.short}</small>
                  </button>
                )
              })}
            </div>
            <ul className="how-switch__needs" aria-live="polite" key={`needs-${mode}`}>
              {current.needs.map((n) => <li key={n}>{n}</li>)}
            </ul>
          </div>
        </div>
      </section>

      <Journey mode={mode} key={mode} />
      <Delivery />
      <Faq mode={mode} key={`faq-${mode}`} />
    </>
  )
}
