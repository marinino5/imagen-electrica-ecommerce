import { useId, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { ArrowRight, MessageCircle, Plus } from 'lucide-react'
import { FAQ_TABS, ONLINE, QUOTE, RECEIVE, SHIPPING } from '../components/comprar/comprarData'
import PhoneMock from '../components/comprar/PhoneMock'
import Bgfx from '../components/ui/Bgfx'
import ColombiaMap from '../components/ui/ColombiaMap'
import Hl from '../components/ui/Hl'
import Pill from '../components/ui/Pill'
import Reveal from '../components/ui/Reveal'
import { useInView } from '../components/ui/useInView'
import { site } from '../config/site'
import { useScrollFrame } from '../hooks/useScrollFrame'
import '../styles/ie-comprar.css'

const shot = (name) => `/img/como-comprar/${name}.webp`

/* ---------- 4.1 Hero: portátil y celular con capturas reales ---------- */
function Hero() {
  return (
    <section className="cb-hero bg-lila-fade" aria-labelledby="h-how">
      <Bgfx b />
      <div className="wrap cb-hero__grid">
        <div className="cb-hero__text">
          <h1 id="h-how" className="cb-h1">
            <span className="cb-h1__thin">Compra o cotiza en</span>
            <span className="cb-h1__strong">tres pasos</span>
          </h1>
          <p className="rd-lead">Elige el camino que va con lo que necesitas: comprar en línea lo que tiene precio, o cotizar lo demás.</p>
          <div className="cb-hero__btns">
            <Pill as="a" href="#compra" tone="o">Compra en línea</Pill>
            <Pill as="a" href="#cotizacion" tone="d">Cotización</Pill>
          </div>
        </div>
        <div className="cb-hero__art" role="img" aria-label="La tienda de Imagen Eléctrica en un portátil y en un celular">
          <span className="cb-hero__arc" aria-hidden="true" />
          <span className="cb-hero__blob" aria-hidden="true" />
          <div className="laptop" aria-hidden="true">
            <div className="laptop__lid"><img src={shot('portatil')} alt="" width="1440" height="900" fetchPriority="high" loading="eager" /></div>
            <div className="laptop__base" />
          </div>
          <PhoneMock className="cb-hero__phone" size="md" screens={[{ src: shot('ficha') }]} label="Ficha de producto en el celular" />
        </div>
      </div>
    </section>
  )
}

/* ---------- 4.2 y 4.3 «Pantalla fija que cambia» ---------- */
// El celular queda fijo mientras pasan los cuatro pasos (cada uno de 70vh); el paso que cruza el centro de la ventana es el
// activo y cambia la pantalla del celular con un fundido de 300 ms. `mirror`: celular a la derecha.
function StickyFlow({ id, title, hl, steps, mirror = false, band = false, className = '' }) {
  const box = useRef(null)
  const [active, setActive] = useState(0)

  useScrollFrame(() => {
    const el = box.current
    if (!el) return
    const mid = window.innerHeight * 0.5
    let best = 0
    let dist = Infinity
    el.querySelectorAll('.cb-step').forEach((s, i) => {
      const r = s.getBoundingClientRect()
      const d = Math.abs(r.top + r.height / 2 - mid)
      if (d < dist) { dist = d; best = i }
    })
    setActive((prev) => (prev === best ? prev : best))
  })

  const go = (i) => box.current?.querySelectorAll('.cb-step')[i]?.scrollIntoView({ behavior: 'smooth', block: 'center' })

  return (
    <section id={id} className={`cb-flow ${mirror ? 'cb-flow--mirror' : ''} ${className}`} aria-labelledby={`h-${id}`}>
      {band ? <Bgfx e /> : <Bgfx d />}
      <div className="wrap">
        <Reveal><h2 id={`h-${id}`} className="rd-h2 cb-flow__title">{title} <Hl>{hl}</Hl></h2></Reveal>
        <div className="cb-flow__grid" ref={box}>
          <div className="cb-stage">
            <div className="cb-stage__in">
              <PhoneMock screens={steps.map((s) => (s.chat ? { chat: true } : { src: shot(s.screen) }))} active={active} label={steps[active].alt} />
              <ol className="cb-dots" aria-label="Paso">
                {steps.map((s, i) => (
                  <li key={s.title}>
                    <button type="button" className={i === active ? 'is-on' : ''} aria-label={`Ir al paso ${i + 1}: ${s.title}`} aria-current={i === active ? 'step' : undefined} onClick={() => go(i)} />
                  </li>
                ))}
              </ol>
            </div>
          </div>
          <ol className="cb-steps">
            {steps.map((s, i) => (
              <li key={s.title} className={`cb-step ${i === active ? 'is-on' : ''}`}>
                <span className="cb-step__n" aria-hidden="true">{i + 1}</span>
                <h3><span className="sr-only">Paso {i + 1}: </span>{s.title}</h3>
                <p>{s.text}</p>
                <span className="cb-pill">{s.tip}</span>
              </li>
            ))}
          </ol>
        </div>
      </div>
    </section>
  )
}

/* ---------- 4.4 Lo que recibes ---------- */
function Receive() {
  return (
    <section className="cb-recv bg-lila-fade" aria-labelledby="h-recibes">
      <Bgfx a="plan:r" />
      <div className="wrap">
        <Reveal><h2 id="h-recibes" className="rd-h2">Lo que <Hl>recibes</Hl></h2></Reveal>
        <ul className="cb-recv__grid">
          {RECEIVE.map((r, i) => (
            <Reveal as="li" key={r.title} i={i}>
              <article className="cb-card">
                <div className="cb-card__photo"><img src={r.photo} alt={r.alt} width="800" height="640" loading="lazy" decoding="async" /></div>
                <div className="cb-card__body">
                  <h3>{r.title}</h3>
                  <p>{r.text}</p>
                </div>
              </article>
            </Reveal>
          ))}
        </ul>
      </div>
    </section>
  )
}

/* ---------- 4.5 Envíos ---------- */
function Shipping() {
  const [ref, seen] = useInView({ threshold: 0.25 })
  return (
    <section className="cb-ship bg-lila-soft" aria-labelledby="h-envios">
      <div className="wrap cb-ship__grid">
        <div className={`cb-map ${seen ? 'is-in' : ''}`} ref={ref}>
          <ColombiaMap className="cb-map__svg" routes />
        </div>
        <div className="cb-ship__text">
          <Reveal><h2 id="h-envios" className="rd-h2">Desde Bucaramanga <Hl>a todo el país</Hl></h2></Reveal>
          <ul className="cb-ship__list">
            {SHIPPING.map((d, i) => (
              <Reveal as="li" key={d.title} i={i}>
                <strong className="cb-ship__big">{d.big}</strong>
                <div><h3>{d.title}</h3><p>{d.text}</p></div>
              </Reveal>
            ))}
          </ul>
        </div>
      </div>
    </section>
  )
}

/* ---------- 4.6 Preguntas frecuentes: pestañas y acordeón de dos columnas ---------- */
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

function Faq() {
  const [tab, setTab] = useState(0)
  const base = useId()
  // Flechas ← → entre pestañas (patrón de tablist)
  const onKey = (e) => {
    if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return
    e.preventDefault()
    const next = (tab + (e.key === 'ArrowRight' ? 1 : -1) + FAQ_TABS.length) % FAQ_TABS.length
    setTab(next)
    document.getElementById(`${base}-t${next}`)?.focus()
  }
  return (
    <section className="cb-faq bg-lila-fade" aria-labelledby="h-faq">
      <Bgfx a="motor:l" d />
      <div className="wrap">
        <Reveal><h2 id="h-faq" className="rd-h2">Resolvemos tus <Hl>dudas</Hl></h2></Reveal>
        <div className="cb-tabs" role="tablist" aria-label="Tema de las preguntas" onKeyDown={onKey}>
          {FAQ_TABS.map((t, i) => (
            <button key={t.id} id={`${base}-t${i}`} type="button" role="tab" aria-selected={tab === i} aria-controls={`${base}-p${i}`} tabIndex={tab === i ? 0 : -1} className={tab === i ? 'is-on' : ''} onClick={() => setTab(i)}>{t.label}</button>
          ))}
        </div>
        {FAQ_TABS.map((t, i) => (
          <div key={t.id} id={`${base}-p${i}`} role="tabpanel" aria-labelledby={`${base}-t${i}`} hidden={tab !== i} className="faq cb-faq__grid">
            {t.items.map((item) => <FaqItem key={item.q} {...item} />)}
          </div>
        ))}
      </div>
    </section>
  )
}

/* ---------- 4.7 Cierre claro ---------- */
function Closing() {
  return (
    <section className="cb-end bg-lila-arc" aria-labelledby="h-fin">
      <div className="wrap cb-end__in">
        <h2 id="h-fin" className="rd-h2">¿No encuentras <Hl>lo que buscas?</Hl></h2>
        <p className="rd-lead">Cuéntanos qué necesitas, con foto o la placa del equipo si la tienes, y un asesor lo busca y te responde con el precio.</p>
        <div className="cb-end__btns">
          <Pill as="link" to="/cotizar" tone="d" icon={ArrowRight}>Cotizar lo que necesito</Pill>
          <Pill as="a" href={site.whatsappUrl} target="_blank" rel="noreferrer" tone="l" icon={MessageCircle}>Escribir por WhatsApp</Pill>
        </div>
      </div>
    </section>
  )
}

export default function ComoComprar() {
  return (
    <>
      <Hero />
      <StickyFlow id="compra" title="Compra" hl="en línea" steps={ONLINE} className="bg-lila-fade" />
      <StickyFlow id="cotizacion" title="Pide una" hl="cotización" steps={QUOTE} mirror band className="cb-flow--quote bg-lila-soft" />
      <Receive />
      <Shipping />
      <Faq />
      <Closing />
    </>
  )
}
