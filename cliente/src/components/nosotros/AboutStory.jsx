import { MapPin, Store } from 'lucide-react'
import { site } from '../../config/site'
import Bezel from '../ui/Bezel'
import Eyebrow from '../ui/Eyebrow'
import Pill from '../ui/Pill'
import Reveal from '../ui/Reveal'

// Historia y propósito, y la atención en tienda. Las cifras del catálogo viven solo en el Inicio.
export default function AboutStory() {
  return (
    <section className="wrap ab-story" aria-labelledby="h-historia">
      <Reveal className="ab-story__text">
        <Eyebrow>Nuestra historia</Eyebrow>
        <h2 id="h-historia" className="h2-sys">Soluciones <em>industriales</em></h2>
        <p>
          Imagen Eléctrica es una distribuidora de material eléctrico industrial en Bucaramanga, Santander. Con más de {site.years} años de
          experiencia acompañamos a empresas y personas con motores, variadores, cables, iluminación y automatización.
        </p>
        <p>
          Hoy llevamos esa experiencia a la tienda en línea: compra lo que está disponible o pide tu cotización sin cuenta y un asesor te responde.
        </p>
        <Pill as="link" to="/productos" tone="d">Ver el catálogo</Pill>
      </Reveal>

      <Reveal delay={0.08}>
        <Bezel className="ab-store">
          <div className="ab-store__core">
            <span className="ab-store__ic" aria-hidden="true"><Store size={30} strokeWidth={1.5} /></span>
            <h3>Atención en tienda</h3>
            <p>Pasa por el mostrador: un asesor revisa tu lista, te muestra la referencia y te dice qué hay en bodega.</p>
            <p className="ab-store__addr"><MapPin size={16} aria-hidden="true" /> {site.address} · {site.city.split(',')[0]}</p>
            <a className="ab-store__link" href={site.directionsUrl} target="_blank" rel="noreferrer">Cómo llegar</a>
          </div>
        </Bezel>
      </Reveal>
    </section>
  )
}
