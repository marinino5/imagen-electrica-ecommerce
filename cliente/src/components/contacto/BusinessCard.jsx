import { useState } from 'react'
import { Link } from 'react-router-dom'
import { Check, Copy, Mail, MessageCircle, Phone } from 'lucide-react'
import { site } from '../../config/site'
import Bgfx from '../ui/Bgfx'

// «Tarjeta de visita»: los tres canales (WhatsApp, correo y llamada) en una gran tarjeta horizontal con franja naranja
// a la izquierda, el logo y los datos en columnas. Los datos salen de config/site.js (fuente única).
export default function BusinessCard() {
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
    <div className="bc">
      <div className="bc__card">
        <div className="bc__brand">
          <img src="/img/logo-ajustado.png" alt="Imagen Eléctrica" width="120" height="145" />
          <p><strong>{site.name}</strong><span>{site.tagline}</span></p>
        </div>
        <ul className="bc__cols">
          <li>
            <span className="bc__ic"><MessageCircle size={20} aria-hidden="true" /></span>
            <h2>WhatsApp</h2>
            <p>Un asesor te responde en minutos.</p>
            <a className="bc__btn" href={site.whatsappUrl} target="_blank" rel="noreferrer">Escribir ahora</a>
          </li>
          <li>
            <span className="bc__ic"><Mail size={20} aria-hidden="true" /></span>
            <h2>Correo</h2>
            <a className="bc__val" href={`mailto:${site.email}`}>{site.email}</a>
            <button type="button" className="bc__copy" onClick={copy}>
              {copied ? <Check size={16} aria-hidden="true" /> : <Copy size={16} aria-hidden="true" />}
              <span aria-live="polite">{copied ? 'Copiado' : 'Copiar correo'}</span>
            </button>
          </li>
          <li>
            <span className="bc__ic"><Phone size={20} aria-hidden="true" /></span>
            <h2>Llamada</h2>
            <ul className="bc__phones">
              {site.contacts.map((c) => <li key={c.label}><a href={c.href}>{c.label}</a></li>)}
            </ul>
          </li>
        </ul>
      </div>
      <p className="bc__more">¿No está en el catálogo? <Link to="/cotizar">Cotiza aquí →</Link></p>
    </div>
  )
}
