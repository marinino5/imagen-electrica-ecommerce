import { Mail, MapPin, Navigation, Phone } from 'lucide-react'
import { site } from '../../config/site'
import MapEmbed from './MapEmbed'

// Mapa de la tienda a todo el ancho del contenedor, con la tarjeta ink de la dirección superpuesta a la izquierda
// (en celular va debajo del mapa). Lo usan Nosotros y Contacto.
export default function StoreMap() {
  return (
    <div className="store-map">
      <MapEmbed className="store-map__map" />
      <div className="store-map__card on-ink">
        <p className="eyebrow"><MapPin size={14} aria-hidden="true" /> Visítanos</p>
        <h3>{site.address}</h3>
        <p className="store-map__city">{site.city}</p>
        <ul className="store-map__list">
          {site.phones.map((p) => (
            <li key={p.href}><Phone size={16} aria-hidden="true" /><a href={p.href}>{p.label}</a></li>
          ))}
          <li><Mail size={16} aria-hidden="true" /><a href={`mailto:${site.email}`}>{site.email}</a></li>
        </ul>
        <a className="btn btn--accent" href={site.directionsUrl} target="_blank" rel="noreferrer">
          <Navigation size={18} aria-hidden="true" /> Cómo llegar
        </a>
      </div>
    </div>
  )
}
