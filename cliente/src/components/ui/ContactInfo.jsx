import { Mail, MapPin, MessageCircle, Phone } from 'lucide-react'
import { site } from '../../config/site'
import { Facebook, Instagram } from './SocialIcons'

// Datos de contacto reales como tarjetas. El horario no se muestra hasta tener el dato.
export default function ContactInfo({ whatsapp = true, social = false }) {
  return (
    <ul className="info-cards">
      {whatsapp && (
        <li>
          <span className="info-cards__icon"><MessageCircle size={20} aria-hidden="true" /></span>
          <div>
            <h3>WhatsApp</h3>
            <a href={site.whatsappUrl} target="_blank" rel="noreferrer">Escríbenos por WhatsApp</a>
          </div>
        </li>
      )}
      <li>
        <span className="info-cards__icon"><Phone size={20} aria-hidden="true" /></span>
        <div>
          <h3>Teléfonos</h3>
          {site.phones.map((p) => <a key={p.href} href={p.href}>{p.label}</a>)}
        </div>
      </li>
      <li>
        <span className="info-cards__icon"><Mail size={20} aria-hidden="true" /></span>
        <div>
          <h3>Correo</h3>
          <a href={`mailto:${site.email}`}>{site.email}</a>
        </div>
      </li>
      <li>
        <span className="info-cards__icon"><MapPin size={20} aria-hidden="true" /></span>
        <div>
          <h3>Dirección</h3>
          <p>{site.address}<br />{site.city}</p>
        </div>
      </li>
      {social && (
        <li>
          <span className="info-cards__icon"><Facebook size={20} /></span>
          <div>
            <h3>Redes</h3>
            <a href={site.social.facebook} target="_blank" rel="noreferrer">Facebook</a>
            <a href={site.social.instagram} target="_blank" rel="noreferrer">Instagram</a>
          </div>
        </li>
      )}
    </ul>
  )
}
