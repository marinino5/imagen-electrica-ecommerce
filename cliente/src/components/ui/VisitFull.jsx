import { MapPin, Navigation } from 'lucide-react'
import { site } from '../../config/site'
import Bgfx from './Bgfx'
import Pill from './Pill'
import Reveal from './Reveal'
import Hl from './Hl'
import '../../styles/ie-pages.css'

// «Visítanos»: el mapa va dentro de un contenedor de radio 28 px (no a sangre) con la tarjeta flotante blanca (dirección,
// horario si existe y «Cómo llegar»). Deja 120 px de margen inferior antes del pie. El horario no se inventa: solo aparece
// cuando la empresa lo confirme (site.hours). `fx` son los fondos con vida de la sección (ver Bgfx).
export default function VisitFull({ id, fx = {} }) {
  return (
    <section className="visit2 bg-lila-fade" id={id} aria-labelledby="h-visit">
      <Bgfx {...fx} />
      <div className="wrap">
        <div className="visit2__frame">
          <div className="visit2__map">
            <iframe title="Mapa: Bucaramanga, Santander, Colombia" src={site.mapUrl} loading="lazy" referrerPolicy="no-referrer-when-downgrade" allowFullScreen />
          </div>
          <Reveal side="l" className="visit2__card">
            <h2 id="h-visit" className="rd-h2">Pásate por <Hl>la tienda</Hl></h2>
            <p className="visit2__addr"><MapPin size={18} aria-hidden="true" /><span><strong>{site.address}</strong><br />{site.city}</span></p>
            {site.hours && <p className="visit2__hours">{site.hours}</p>}
            <Pill as="a" href={site.directionsUrl} target="_blank" rel="noreferrer" tone="o" icon={Navigation}>Cómo llegar</Pill>
          </Reveal>
        </div>
      </div>
    </section>
  )
}
