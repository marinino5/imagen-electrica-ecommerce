import { Mail, MessageCircle, Phone } from 'lucide-react'
import { site } from '../../config/site'
import Bezel from '../ui/Bezel'
import Pill from '../ui/Pill'
import Reveal from '../ui/Reveal'

// Tres tarjetas de acción en bisel: WhatsApp (naranja), Llámanos (ink) y Correo (blanco). Sin nombres propios.
export default function ContactActions() {
  return (
    <section className="wrap ct-actions" aria-label="Formas de contactarnos">
      <Reveal>
        <Bezel className="ct-card ct-card--wa">
          <div className="ct-card__core">
            <span className="ct-card__ic"><MessageCircle size={24} aria-hidden="true" /></span>
            <h2>WhatsApp</h2>
            <p>La forma más rápida: cuéntanos qué necesitas y un asesor te responde.</p>
            <Pill as="a" href={site.whatsappUrl} target="_blank" rel="noreferrer" tone="d" icon={MessageCircle}>Escríbenos por WhatsApp</Pill>
          </div>
        </Bezel>
      </Reveal>

      <Reveal delay={0.06}>
        <Bezel className="ct-card ct-card--call">
          <div className="ct-card__core">
            <span className="ct-card__ic"><Phone size={24} aria-hidden="true" /></span>
            <h2>Llámanos</h2>
            <ul>
              {site.contacts.map((c) => (
                <li key={c.href}>
                  <a href={c.href}>{c.label}</a>
                  {c.main && <small>Línea principal</small>}
                </li>
              ))}
            </ul>
          </div>
        </Bezel>
      </Reveal>

      <Reveal delay={0.12}>
        <Bezel className="ct-card ct-card--mail">
          <div className="ct-card__core">
            <span className="ct-card__ic"><Mail size={24} aria-hidden="true" /></span>
            <h2>Correo</h2>
            <p>Para cotizaciones con listas largas o documentos.</p>
            <a className="ct-card__mail" href={`mailto:${site.email}`}>{site.email}</a>
          </div>
        </Bezel>
      </Reveal>
    </section>
  )
}
