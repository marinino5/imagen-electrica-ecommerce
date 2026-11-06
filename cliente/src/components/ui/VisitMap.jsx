import { Mail, MapPin, Navigation, Phone } from 'lucide-react'
import { site } from '../../config/site'
import Bezel from './Bezel'
import Eyebrow from './Eyebrow'
import Pill from './Pill'
import '../../styles/visit.css'

// Mapa de Google en marco de doble bisel con la tarjeta ink de la dirección. Lo usan Nosotros y Contacto.
export default function VisitMap({ id }) {
  return (
    <div className="visit" id={id}>
      <Bezel className="visit__map" coreClassName="visit__map-core">
        <iframe
          title="Mapa: Bucaramanga, Santander, Colombia"
          src={site.mapUrl}
          loading="lazy"
          referrerPolicy="no-referrer-when-downgrade"
          allowFullScreen
        />
      </Bezel>
      <Bezel className="visit__card" coreClassName="visit__card-core">
        <Eyebrow className="eyebrow--light"><MapPin size={12} aria-hidden="true" /> Visítanos</Eyebrow>
        <h3>{site.address}</h3>
        <p className="visit__city">{site.city}</p>
        <ul>
          {site.contacts.map((c) => (
            <li key={c.href}>
              <Phone size={16} aria-hidden="true" />
              <a href={c.href}>{c.label}</a>
              {c.main && <small>Línea principal</small>}
            </li>
          ))}
          <li><Mail size={16} aria-hidden="true" /><a href={`mailto:${site.email}`}>{site.email}</a></li>
        </ul>
        <Pill as="a" href={site.directionsUrl} target="_blank" rel="noreferrer" tone="o" icon={Navigation}>Cómo llegar</Pill>
      </Bezel>
    </div>
  )
}
