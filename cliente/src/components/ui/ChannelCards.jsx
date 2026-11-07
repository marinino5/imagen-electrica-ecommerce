import { useState } from 'react'
import { Link } from 'react-router-dom'
import { Check, Copy, Mail, MessageCircle, Phone } from 'lucide-react'
import { site } from '../../config/site'
import Reveal from './Reveal'

// Tres canales de contacto (mismo componente en el cierre del Inicio y en Contacto) y el acceso a la cotización libre.
export default function ChannelCards({ more = true, row = false }) {
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
    <>
      <ul className={`chans ${row ? 'chans--row' : ''}`}>
        <Reveal as="li" i={1}>
          <a className="chan chan--wa" href={site.whatsappUrl} target="_blank" rel="noreferrer">
            <span className="chan__ic"><MessageCircle size={22} aria-hidden="true" /></span>
            <strong>WhatsApp</strong>
            <span className="chan__sub">Responde en minutos</span>
          </a>
        </Reveal>
        <Reveal as="li" i={2}>
          <div className="chan">
            <span className="chan__ic"><Mail size={22} aria-hidden="true" /></span>
            <strong>Correo</strong>
            <a className="chan__sub" href={`mailto:${site.email}`}>{site.email}</a>
            <button type="button" className="chan__copy" onClick={copy}>
              {copied ? <Check size={16} aria-hidden="true" /> : <Copy size={16} aria-hidden="true" />}
              <span aria-live="polite">{copied ? 'Copiado' : 'Copiar correo'}</span>
            </button>
          </div>
        </Reveal>
        <Reveal as="li" i={3}>
          <div className="chan">
            <span className="chan__ic"><Phone size={22} aria-hidden="true" /></span>
            <strong>Llamada</strong>
            <ul className="chan__phones">
              {site.contacts.map((c) => <li key={c.label}><a href={c.href}>{c.label}</a></li>)}
            </ul>
          </div>
        </Reveal>
      </ul>
      {more && <p className="chan-more">¿No está en el catálogo? <Link to="/cotizar">Cotiza aquí →</Link></p>}
    </>
  )
}
