import { Link } from 'react-router-dom'
import { ArrowUpRight } from 'lucide-react'
import Eyebrow from '../ui/Eyebrow'
import Reveal from '../ui/Reveal'
import '../../styles/home.css'

// Mosaico de fotos reales en marcos de doble bisel (Pixabay; ver docs/recursos.md).
// Iluminación no tiene foto real todavía: no se muestra en el mosaico.
const applications = [
  { id: 'a', name: 'Bombeo', tag: 'Motores', image: 'bombeo', alt: 'Tuberías y válvulas de un sistema de bombeo', to: '/productos?familia=motores-electricos' },
  { id: 'b', name: 'Ventilación y extracción', tag: 'Ventilación', image: 'ventilacion', alt: 'Ventiladores industriales de gran tamaño', to: '/productos?familia=ventilacion-y-climatizacion' },
  { id: 'c', name: 'Bandas transportadoras', tag: 'Motorreductores', image: 'bandas', alt: 'Banda transportadora en una planta', to: '/productos?familia=reductores-y-motorreductores' },
  { id: 'd', name: 'Tableros y automatización', tag: 'Control', image: 'tableros', alt: 'Electricista revisando un tablero eléctrico', to: '/productos?familia=control-y-automatizacion' },
  { id: 'e', name: 'Energía solar', tag: 'Solar', image: 'solar', alt: 'Campo de paneles solares', to: '/productos?q=solar' },
]

export default function Applications() {
  return (
    <section className="wrap hm-section" aria-labelledby="h-aplicaciones">
      <div className="hm-head">
        <div>
          <Eyebrow>Para tu planta</Eyebrow>
          <h2 id="h-aplicaciones" className="h2-sys">Soluciones por <em>aplicación</em></h2>
          <p className="hm-head__lead">Cuéntanos qué necesitas mover, ventilar o automatizar.</p>
        </div>
      </div>

      <div className="hm-apps">
        {applications.map((a, i) => (
          <Reveal key={a.id} delay={i * 0.05} className={`hm-apps__${a.id}`}>
            <Link to={a.to} className="bz hm-app">
              <span className="core">
                <img
                  src={`/img/aplicaciones/${a.image}-sm.webp`}
                  srcSet={`/img/aplicaciones/${a.image}-sm.webp 640w, /img/aplicaciones/${a.image}.webp 1280w`}
                  sizes="(min-width: 1024px) 40vw, 100vw"
                  alt={a.alt}
                  width="640"
                  height="427"
                  loading="lazy"
                />
                <span className="hm-app__shade" aria-hidden="true" />
                <span className="hm-app__tag">{a.tag}</span>
                <span className="hm-app__title">{a.name}<ArrowUpRight size={20} aria-hidden="true" /></span>
              </span>
            </Link>
          </Reveal>
        ))}
      </div>
    </section>
  )
}
