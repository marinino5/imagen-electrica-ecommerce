import { useState } from 'react'
import { Link } from 'react-router-dom'
import { Check, Copy, Mail, MessageCircle, Phone } from 'lucide-react'
import { site } from '../../config/site'
import Reveal from '../ui/Reveal'
import Hl from '../ui/Hl'
import SectionLabel from '../ui/SectionLabel'
import Arc from '../ui/Arc'
import '../../styles/home.css'

// Único cierre del Inicio. Fondo: foto de bodega con capa casi negra del violeta y un brillo naranja en una esquina.
// A la izquierda el técnico recortado en arco que sobresale; a la derecha tres canales del mismo tamaño.
export default function Advisory() {
  const [copied, setCopied] = useState(false)
  const copy = async () => {
    try {
      await navigator.clipboard.writeText(site.email)
      setCopied(true)
      setTimeout(() => setCopied(false), 2200)
    } catch {
      /* sin portapapeles: el correo queda visible para copiarlo a mano */
    }
  }

  return (
    <section className="adv on-dark" aria-labelledby="h-asesoria">
      <img className="adv__bg" src="/img/fotos/bodega.webp" alt="" width="960" height="1280" loading="lazy" />
      <span className="adv__glow" aria-hidden="true" />
      <Arc pos="bl" />
      <div className="wrap adv__grid">
        <Reveal side="l" className="adv__person">
          <img src="/img/fotos/tecnico-casco.webp" alt="Técnico con casco y gafas frente a un tablero eléctrico" width="852" height="1280" loading="lazy" />
        </Reveal>
        <div className="adv__copy">
          <Reveal>
            <SectionLabel n="08">Asesoría</SectionLabel>
            <h2 id="h-asesoria" className="rd-h2 adv__title">¿Qué necesitas <Hl>mover hoy?</Hl></h2>
            <p className="rd-lead">Un asesor te responde con la referencia, el precio y el tiempo de entrega. Escoge el canal que prefieras.</p>
          </Reveal>
          <ul className="adv__channels">
            <Reveal as="li" i={1}>
              <a className="chan chan--wa" href={site.whatsappUrl} target="_blank" rel="noreferrer">
                <MessageCircle size={26} aria-hidden="true" />
                <strong>WhatsApp</strong>
                <span>Responde en minutos</span>
              </a>
            </Reveal>
            <Reveal as="li" i={2}>
              <div className="chan">
                <Mail size={26} aria-hidden="true" />
                <strong>Correo</strong>
                <a className="chan__mail" href={`mailto:${site.email}`}>{site.email}</a>
                <button type="button" className="chan__copy" onClick={copy}>
                  {copied ? <Check size={16} aria-hidden="true" /> : <Copy size={16} aria-hidden="true" />}
                  <span aria-live="polite">{copied ? 'Copiado' : 'Copiar correo'}</span>
                </button>
              </div>
            </Reveal>
            <Reveal as="li" i={3}>
              <div className="chan">
                <Phone size={26} aria-hidden="true" />
                <strong>Llamada</strong>
                <ul className="chan__phones">
                  {site.contacts.map((c) => <li key={c.label}><a href={c.href}>{c.label}</a></li>)}
                </ul>
              </div>
            </Reveal>
          </ul>
        </div>
      </div>
      <div className="adv__strip">
        <p>¿Compras seguido? <Link to="/crear-cuenta">Crea tu cuenta gratis →</Link></p>
      </div>
    </section>
  )
}
