import { Mail, MessageCircle, Phone, Zap } from 'lucide-react'
import WordReveal from '../ui/WordReveal'
import { site } from '../../config/site'

// Portada: violeta con el título y las acciones rápidas (WhatsApp, llamada y correo); a la derecha, una foto real
export default function ContactCover() {
  return (
    <header className="cover contact-cover on-violet">
      <span className="contact-cover__ring contact-cover__ring--a" aria-hidden="true" />
      <span className="contact-cover__ring contact-cover__ring--b" aria-hidden="true" />
      <Zap className="contact-cover__bolt" size={420} strokeWidth={1} aria-hidden="true" />
      <div className="container contact-cover__grid">
        <div className="contact-cover__copy">
          <p className="tag-pill"><Zap size={14} aria-hidden="true" /> Contacto</p>
          <WordReveal
            className="display contact-cover__title"
            delay={0.1}
            lines={[{ text: 'HABLEMOS DE TU' }, { text: 'PROYECTO', accent: true }]}
          />
          <p className="contact-cover__text">Escríbenos o llámanos y un asesor te responde con la referencia y el precio.</p>
          <div className="contact-cover__actions">
            <a className="btn btn--accent btn--lg" href={site.whatsappUrl} target="_blank" rel="noreferrer">
              <MessageCircle size={20} aria-hidden="true" /> WhatsApp
            </a>
            <a className="btn btn--ghost-light btn--lg" href={site.mainContact.href}>
              <Phone size={20} aria-hidden="true" /> Llamar
            </a>
            <a className="btn btn--ghost-light btn--lg" href={`mailto:${site.email}`}>
              <Mail size={20} aria-hidden="true" /> Correo
            </a>
          </div>
          <p className="contact-cover__who">Línea principal: <strong>{site.mainContact.label}</strong></p>
        </div>
        <figure className="contact-cover__photo">
          <img
            src="/img/fotos/contacto-sm.webp"
            srcSet="/img/fotos/contacto-sm.webp 640w, /img/fotos/contacto.webp 1280w"
            sizes="(min-width: 960px) 40vw, 100vw"
            alt="Manos de un electricista conectando protecciones en un tablero"
            width="640"
            height="427"
            fetchPriority="high"
          />
        </figure>
      </div>
    </header>
  )
}
