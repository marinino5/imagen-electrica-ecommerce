import { site } from '../../config/site'

// Mapa de Google embebido con la dirección de la tienda
export default function MapEmbed({ className = '' }) {
  return (
    <div className={`map ${className}`}>
      <iframe
        title={`Mapa: ${site.address}, ${site.city}`}
        src={site.mapUrl}
        loading="lazy"
        referrerPolicy="no-referrer-when-downgrade"
        allowFullScreen
      />
    </div>
  )
}
