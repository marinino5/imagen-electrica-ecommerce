import { MapPin, Navigation } from 'lucide-react'
import { site } from '../../config/site'
import Pill from './Pill'
import Reveal from './Reveal'
import SectionLabel from './SectionLabel'
import Hl from './Hl'

// «Visítanos»: mapa a ancho completo con una tarjeta flotante blanca (dirección, horario si existe y «Cómo llegar»).
// El horario no se inventa: solo aparece cuando la empresa lo confirme (site.hours).
export default function VisitFull({ id, n = '05' }) {
  return (
    <section className="visit2 bg-lila-fade" id={id} aria-labelledby="h-visit">
      <div className="visit2__map">
        <iframe title="Mapa: Bucaramanga, Santander, Colombia" src={site.mapUrl} loading="lazy" referrerPolicy="no-referrer-when-downgrade" allowFullScreen />
      </div>
      <div className="wrap visit2__wrap">
        <Reveal side="l" className="visit2__card">
          <SectionLabel n={n}>Visítanos</SectionLabel>
          <h2 id="h-visit" className="rd-h2">Pásate por <Hl>la tienda</Hl></h2>
          <p className="visit2__addr"><MapPin size={18} aria-hidden="true" /><span><strong>{site.address}</strong><br />{site.city}</span></p>
          {site.hours && <p className="visit2__hours">{site.hours}</p>}
          <Pill as="a" href={site.directionsUrl} target="_blank" rel="noreferrer" tone="o" icon={Navigation}>Cómo llegar</Pill>
        </Reveal>
      </div>
    </section>
  )
}
