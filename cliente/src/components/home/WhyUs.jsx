import { Headset, MessageCircle, Phone } from 'lucide-react'
import { site } from '../../config/site'
import Eyebrow from '../ui/Eyebrow'
import Reveal from '../ui/Reveal'
import '../../styles/home.css'

const reasons = [
  ['Respuesta de quien conoce el producto', 'Un asesor revisa tu lista, confirma la referencia exacta y te responde con precio y tiempo de entrega.'],
  ['Existencias a la vista', 'Cada ficha muestra qué hay en bodega y qué se pide bajo pedido, antes de que armes tu solicitud.'],
  ['Cotiza sin crear cuenta', 'Envía tu lista de materiales y la respondemos; si te conviene, la convertimos en pedido.'],
]

// Asesoría y atención. La foto del asesor atendiendo está pendiente (docs/imagenes-pendientes.md):
// mientras tanto se muestra el diseño de marca, no una foto genérica.
export default function WhyUs() {
  return (
    <section className="hm-why" aria-labelledby="h-porque">
      <div className="wrap hm-why__grid">
        <Reveal className="hm-why__copy">
          <Eyebrow>Por qué elegirnos</Eyebrow>
          <h2 id="h-porque" className="h2-sys">Una persona real <em>detrás de cada pedido</em></h2>
          <ul className="hm-why__list">
            {reasons.map(([title, text]) => (
              <li key={title}>
                <b>{title}</b>
                <span>{text}</span>
              </li>
            ))}
          </ul>
          <div className="hm-why__cta">
            <a className="pill pill--o" href={site.whatsappUrl} target="_blank" rel="noreferrer">
              <span className="pill__label">Escribir por WhatsApp</span>
              <span className="pill__ic" aria-hidden="true"><MessageCircle size={18} /></span>
            </a>
            <a className="hm-why__tel" href={site.mainContact.href}><Phone size={16} aria-hidden="true" /> {site.mainContact.label}</a>
          </div>
        </Reveal>

        <Reveal className="hm-why__art" delay={0.08} aria-hidden="true">
          <span className="hm-why__ring hm-why__ring--a" />
          <span className="hm-why__ring hm-why__ring--b" />
          <span className="hm-why__badge"><Headset size={88} strokeWidth={1.1} /></span>
        </Reveal>
      </div>
    </section>
  )
}
