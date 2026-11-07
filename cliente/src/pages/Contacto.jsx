import { useState } from 'react'
import { Check, Copy, Mail, MapPin, MessageCircle, Navigation, Phone } from 'lucide-react'
import { site } from '../config/site'
import SmartForm from '../components/contacto/SmartForm'
import Hl from '../components/ui/Hl'
import Pill from '../components/ui/Pill'
import Reveal from '../components/ui/Reveal'
import SectionLabel from '../components/ui/SectionLabel'
import '../styles/contacto.css'
import '../styles/rd-pages.css'

export default function Contacto() {
  const [copied, setCopied] = useState(false)
  const copy = async () => {
    try {
      await navigator.clipboard.writeText(site.email)
      setCopied(true)
      setTimeout(() => setCopied(false), 2200)
    } catch {
      /* sin portapapeles: el correo queda visible */
    }
  }

  return (
    <>
      <section className="pg-hero on-dark" aria-labelledby="h-contacto">
        <img className="pg-hero__bg" src="/img/fotos/contacto.webp" alt="Manos de un técnico conectando módulos en un tablero eléctrico" width="1280" height="854" fetchPriority="high" />
        <div className="wrap pg-hero__txt">
          <p className="pg-hero__tag">Contacto</p>
          <h1 id="h-contacto">Hablemos de <strong>tu proyecto</strong></h1>
          <p>Escríbenos o llámanos y un asesor te responde con la referencia y el precio.</p>
        </div>
      </section>

      <section className="rd-sec bg-white tex-dots ct2" aria-label="Formas de contactarnos">
        <div className="wrap">
          <ul className="ct2__chan">
            <Reveal as="li">
              <a className="ct2__card ct2__card--wa lift" href={site.whatsappUrl} target="_blank" rel="noreferrer">
                <MessageCircle size={30} aria-hidden="true" />
                <h2>WhatsApp</h2>
                <p>La forma más rápida: cuéntanos qué necesitas.</p>
                <span className="ct2__go">Escribir ahora →</span>
              </a>
            </Reveal>
            <Reveal as="li" i={1}>
              <div className="ct2__card lift">
                <Phone size={30} aria-hidden="true" />
                <h2>Llamada</h2>
                <ul>{site.contacts.map((c) => <li key={c.href}><a href={c.href}>{c.label}</a></li>)}</ul>
              </div>
            </Reveal>
            <Reveal as="li" i={2}>
              <div className="ct2__card lift">
                <Mail size={30} aria-hidden="true" />
                <h2>Correo</h2>
                <a className="ct2__mail" href={`mailto:${site.email}`}>{site.email}</a>
                <button type="button" className="ct2__copy" onClick={copy}>
                  {copied ? <Check size={16} aria-hidden="true" /> : <Copy size={16} aria-hidden="true" />}
                  <span aria-live="polite">{copied ? 'Copiado' : 'Copiar correo'}</span>
                </button>
              </div>
            </Reveal>
          </ul>

          <div className="ct2__grid">
            <Reveal side="l" id="formulario">
              <SectionLabel n="01">Escríbenos</SectionLabel>
              <h2 className="rd-h2" id="h-ayuda">Cuéntanos <Hl>qué necesitas</Hl></h2>
              <div className="ct2__form"><SmartForm /></div>
            </Reveal>
            <Reveal side="r" delay={0.12} className="ct2__side">
              <SectionLabel n="02">Visítanos</SectionLabel>
              <h2 className="rd-h2">Pásate por <Hl>la tienda</Hl></h2>
              <div className="ct2__map">
                <iframe title="Mapa: Bucaramanga, Santander, Colombia" src={site.mapUrl} loading="lazy" referrerPolicy="no-referrer-when-downgrade" allowFullScreen />
              </div>
              <div className="ct2__addr">
                <MapPin size={20} aria-hidden="true" />
                <p><strong>{site.address}</strong><br />{site.city}</p>
              </div>
              <Pill as="a" href={site.directionsUrl} target="_blank" rel="noreferrer" tone="d" icon={Navigation}>Cómo llegar</Pill>
            </Reveal>
          </div>
        </div>
      </section>
    </>
  )
}
