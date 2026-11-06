import { MapPin } from 'lucide-react'
import { site } from '../../config/site'
import Bezel from '../ui/Bezel'
import Cover from '../ui/Cover'
import Pill from '../ui/Pill'

// Portada: título a la izquierda y la fachada real en bisel con dos insignias flotantes
export default function AboutCover() {
  return (
    <Cover
      eyebrow="Nosotros"
      lines={['Material eléctrico', 'con criterio', { text: 'técnico', accent: true }]}
      lead={`${site.tagline} en ${site.city.split(',')[0]}: material eléctrico industrial con asesoría técnica.`}
      actions={
        <>
          <Pill as="link" to="/productos" tone="o">Ver productos</Pill>
          <Pill as="a" href="#visitanos" tone="l">Visítanos</Pill>
        </>
      }
    >
      <div className="ab-photo">
        <Bezel className="ab-photo__frame">
          <img src="/img/fachada.webp" alt="Fachada de Imagen Eléctrica en Bucaramanga" width="1600" height="800" fetchPriority="high" />
        </Bezel>
        <div className="ab-badge ab-badge--address"><MapPin size={16} aria-hidden="true" /><span>{site.address} · {site.city.split(',')[0]}</span></div>
      </div>
    </Cover>
  )
}
