import { Mail, MapPin, Navigation, Phone } from 'lucide-react'
import { site } from '../../config/site'
import MapEmbed from './MapEmbed'
import ImageSlot from './ImageSlot'
import Reveal from './Reveal'

// Mapa embebido dentro de un marco de arco naranja, con la tarjeta ink de la dirección encima.
// photoLabel: si se pasa, agrega un marcador de foto de la fachada o el interior.
export default function VisitCard({ photoLabel }) {
  return (
    <Reveal className={`visit ${photoLabel ? 'visit--photo' : ''}`}>
      <div className="visit__frame">
        <MapEmbed className="visit__map" />
      </div>

      <div className="visit__card on-ink">
        <p className="eyebrow"><MapPin size={14} aria-hidden="true" /> Visítanos</p>
        <h3>{site.address}</h3>
        <p className="visit__city">{site.city}</p>
        <ul className="visit__list">
          {site.phones.map((p) => (
            <li key={p.href}><Phone size={16} aria-hidden="true" /><a href={p.href}>{p.label}</a></li>
          ))}
          <li><Mail size={16} aria-hidden="true" /><a href={`mailto:${site.email}`}>{site.email}</a></li>
        </ul>
        <a className="btn btn--accent" href={site.directionsUrl} target="_blank" rel="noreferrer">
          <Navigation size={18} aria-hidden="true" /> Cómo llegar
        </a>
      </div>

      {photoLabel && (
        <div className="visit__photo">
          <ImageSlot label={photoLabel} ratio="4 / 5" />
        </div>
      )}
    </Reveal>
  )
}
