import { Mail, MapPin, MessageCircle, Phone, Zap } from 'lucide-react'
import { Facebook, Instagram } from '../ui/SocialIcons'
import { site } from '../../config/site'

// Tarjeta ink con todos los datos de contacto y las redes. El horario no se muestra hasta tener el dato.
export default function ContactCard() {
  return (
    <aside className="ccard on-ink" aria-label="Datos de contacto">
      <span className="ccard__arc" aria-hidden="true" />
      <p className="eyebrow"><Zap size={14} aria-hidden="true" /> Datos de contacto</p>
      <h2>Estamos para ayudarte</h2>
      <ul className="ccard__list">
        <li>
          <span><MessageCircle size={20} aria-hidden="true" /></span>
          <div><h3>WhatsApp</h3><a href={site.whatsappUrl} target="_blank" rel="noreferrer">Escríbenos por WhatsApp</a></div>
        </li>
        <li>
          <span><Phone size={20} aria-hidden="true" /></span>
          <div><h3>Teléfonos</h3>{site.phones.map((p) => <a key={p.href} href={p.href}>{p.label}</a>)}</div>
        </li>
        <li>
          <span><Mail size={20} aria-hidden="true" /></span>
          <div><h3>Correo</h3><a href={`mailto:${site.email}`}>{site.email}</a></div>
        </li>
        <li>
          <span><MapPin size={20} aria-hidden="true" /></span>
          <div><h3>Dirección</h3><p>{site.address}<br />{site.city}</p></div>
        </li>
      </ul>
      <div className="ccard__social">
        <a href={site.social.facebook} target="_blank" rel="noreferrer" aria-label="Facebook de Imagen Eléctrica"><Facebook size={20} /></a>
        <a href={site.social.instagram} target="_blank" rel="noreferrer" aria-label="Instagram de Imagen Eléctrica"><Instagram size={20} /></a>
      </div>
    </aside>
  )
}
