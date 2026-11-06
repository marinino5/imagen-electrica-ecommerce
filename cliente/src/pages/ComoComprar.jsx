import { useEffect, useId, useRef, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import { CreditCard, Headset, Plus, Store, Truck, MapPin } from 'lucide-react'
import { DELIVERY, FAQS, MODES, STEPS } from '../components/comprar/modeData'
import Bezel from '../components/ui/Bezel'
import ColombiaMap from '../components/ui/ColombiaMap'
import Cover from '../components/ui/Cover'
import Eyebrow from '../components/ui/Eyebrow'
import Pill from '../components/ui/Pill'
import '../styles/como-comprar.css'

// Captura real de la tienda dentro de un marco de navegador en bisel
function Frame({ step }) {
  if (!step.image) {
    const Icon = step.icon === 'card' ? CreditCard : Headset
    return (
      <div className="how-icon" aria-hidden="true">
        <Icon size={44} />
      </div>
    )
  }
  return (
    <Bezel className="how-frame">
      <div className="how-frame__bar" aria-hidden="true">
        <span><i /><i /><i /></span>
        <em>{step.path}</em>
      </div>
      <img src={`/img/como-comprar/${step.image}`} alt={step.alt} width={step.w} height={step.h} loading="lazy" />
    </Bezel>
  )
}

// Recorrido: una línea naranja une los pasos y se llena con el scroll (única animación de scroll de la página)
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
      const progress = (window.innerHeight * 0.6 - r.top) / r.height
      el.style.setProperty('--fill', String(Math.min(1, Math.max(0, progress))))
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
    <section className="wrap how-journey" aria-labelledby="h-pasos">
      <Eyebrow>Paso a paso</Eyebrow>
      <h2 id="h-pasos" className="h2-sys">{mode === 'direct' ? 'Así funciona la ' : 'Así funciona la '}<em>{mode === 'direct' ? 'compra en línea' : 'cotización'}</em></h2>
      <ol className="how-steps" ref={listRef}>
        <span className="how-line" aria-hidden="true"><span className="how-line__fill" /></span>
        {steps.map((s, i) => (
          <li key={s.title} className="how-step">
            <span className="how-step__node" aria-hidden="true">{i + 1}</span>
            <Bezel className="how-step__card">
              <div className="how-step__core">
                <div className="how-step__text">
                  <h3><span className="sr-only">Paso {i + 1}: </span>{s.title}</h3>
                  <p>{s.text}</p>
                </div>
                <Frame step={s} />
              </div>
            </Bezel>
          </li>
        ))}
      </ol>
    </section>
  )
}

function Delivery() {
  const icons = { pickup: Store, local: Truck, national: MapPin }
  return (
    <section className="wrap how-delivery" aria-labelledby="h-entregas">
      <Eyebrow>Entregas</Eyebrow>
      <h2 id="h-entregas" className="h2-sys">Recíbelo <em>donde estés</em></h2>
      <div className="how-delivery__grid">
        <ul className="how-delivery__cards">
          {DELIVERY.map((d) => {
            const Icon = icons[d.id]
            return (
              <li key={d.id}>
                <Bezel>
                  <div className="how-dcard">
                    <span className="how-dcard__ic"><Icon size={24} aria-hidden="true" /></span>
                    <div>
                      <span className="how-dcard__tag">{d.tag}</span>
                      <h3>{d.title}</h3>
                      <p>{d.text}</p>
                    </div>
                  </div>
                </Bezel>
              </li>
            )
          })}
        </ul>
        <Bezel className="how-map">
          <div className="how-map__core">
            <ColombiaMap className="how-map__svg" />
            <p><strong>Santander · Bucaramanga</strong> Recoges en la tienda y llevamos a todo el país.</p>
          </div>
        </Bezel>
      </div>
    </section>
  )
}

function FaqItem({ q, a }) {
  const id = useId()
  const [open, setOpen] = useState(false)
  return (
    <div className={`how-faq__item ${open ? 'is-open' : ''}`}>
      <h3>
        <button type="button" aria-expanded={open} aria-controls={id} onClick={() => setOpen((o) => !o)}>
          <span>{q}</span>
          <span className="how-faq__plus" aria-hidden="true"><Plus size={20} /></span>
        </button>
      </h3>
      <div id={id} role="region" className="how-faq__panel" aria-hidden={!open}>
        <div><p>{a}</p></div>
      </div>
    </div>
  )
}

function Faq({ mode }) {
  const items = FAQS.filter((f) => f.modes.includes(mode))
  const half = Math.ceil(items.length / 2)
  return (
    <section className="wrap how-faq" aria-labelledby="h-faq">
      <Eyebrow>Preguntas frecuentes</Eyebrow>
      <h2 id="h-faq" className="h2-sys">Resolvemos tus <em>dudas</em></h2>
      <div className="how-faq__cols">
        {[items.slice(0, half), items.slice(half)].map((col, c) => (
          <div key={c}>
            {col.map((item) => (
              <Bezel key={item.q} className="how-faq__bz"><FaqItem {...item} /></Bezel>
            ))}
          </div>
        ))}
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
      <Cover
        eyebrow="Cómo comprar"
        lines={['Comprar es', { text: 'así de fácil', accent: true }]}
        lead={current.lead}
        actions={<Pill as="link" to={current.cta.to} tone="o" key={mode}>{current.cta.label}</Pill>}
      >
        <Bezel className="how-switch">
          <div className="how-switch__core">
            <p className="how-switch__q" id="how-q">Elige cómo quieres comprar</p>
            <div className="how-switch__opts" role="radiogroup" aria-labelledby="how-q">
              {Object.values(MODES).map((m) => {
                const Icon = m.icon
                return (
                  <button key={m.id} type="button" role="radio" aria-checked={mode === m.id} className={mode === m.id ? 'is-on' : ''} onClick={() => setMode(m.id)}>
                    <span className="how-switch__ic"><Icon size={22} aria-hidden="true" /></span>
                    <strong>{m.label}</strong>
                    <small>{m.short}</small>
                  </button>
                )
              })}
            </div>
            <ul className="how-switch__needs" aria-live="polite">
              {current.needs.map((n) => <li key={n}>{n}</li>)}
            </ul>
          </div>
        </Bezel>
      </Cover>

      <Journey mode={mode} key={mode} />
      <Delivery />
      <Faq mode={mode} key={`faq-${mode}`} />
    </>
  )
}
