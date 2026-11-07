import { MapPin, Navigation } from 'lucide-react'
import { site } from '../../config/site'
import Pill from '../ui/Pill'

// Dirección compacta: texto, una miniatura de mapa ESTÁTICA (imagen, no iframe) y «Cómo llegar», que abre Google Maps.
// La miniatura es un recorte de OpenStreetMap con la ubicación de la tienda (créditos en docs/creditos-media.md).
export default function StoreCard() {
  return (
    <div className="sc">
      <a className="sc__map" href={site.directionsUrl} target="_blank" rel="noreferrer" aria-label="Abrir la ubicación de la tienda en Google Maps">
        <img src="/img/mapa-tienda.webp" alt="Mapa del centro de Bucaramanga con la ubicación de la tienda" width="900" height="520" loading="lazy" />
        <span className="sc__pin" aria-hidden="true"><MapPin size={34} strokeWidth={2.2} fill="currentColor" /></span>
        <small>© OpenStreetMap</small>
      </a>
      <div className="sc__body">
        <h2>Visítanos en la tienda</h2>
        <p className="sc__addr"><MapPin size={18} aria-hidden="true" /><span><strong>{site.address}</strong><br />{site.city}</span></p>
        {site.hours && <p className="sc__hours">{site.hours}</p>}
        <Pill as="a" href={site.directionsUrl} target="_blank" rel="noreferrer" tone="o" icon={Navigation}>Cómo llegar</Pill>
      </div>
    </div>
  )
}
