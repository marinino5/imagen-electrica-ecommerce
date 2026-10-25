import { Link } from 'react-router-dom'
import { ArrowUpRight } from 'lucide-react'
import Reveal from '../ui/Reveal'
import '../../styles/home.css'

// Cada aplicación lleva a productos filtrados. Las imágenes están pendientes (marcador de posición).
const applications = [
  { name: 'Bombeo', resource: 'aplicacion-bombeo.webp', to: '/productos?category=motores-electricos', size: 'a' },
  { name: 'Ventilación y extracción', resource: 'aplicacion-ventilacion.webp', to: '/productos?category=ventilacion', size: 'b' },
  { name: 'Bandas transportadoras', resource: 'aplicacion-bandas.webp', to: '/productos?category=motorreductores', size: 'c' },
  { name: 'Tableros y automatización', resource: 'aplicacion-tableros.webp', to: '/productos?category=plc', size: 'd' },
  { name: 'Iluminación industrial', resource: 'aplicacion-iluminacion.webp', to: '/productos?category=iluminacion', size: 'e' },
]

export default function Applications() {
  return (
    <section className="section" aria-labelledby="h-aplicaciones">
      <div className="container">
        <div className="section-head">
          <Reveal>
            <h2 id="h-aplicaciones" className="section-title">Soluciones por <em>aplicación</em></h2>
            <p className="section-lead">Cuéntanos qué necesitas mover, ventilar, iluminar o automatizar.</p>
          </Reveal>
        </div>

        <div className="bento">
          {applications.map((app, i) => (
            <Reveal key={app.name} delay={i * 0.06} className={`bento__item bento__item--${app.size}`}>
              <Link to={app.to} className="bento__link">
                <span className="bento__placeholder" aria-hidden="true">Imagen pendiente · {app.resource}</span>
                <span className="bento__shade" aria-hidden="true" />
                <span className="bento__title">
                  {app.name}
                  <ArrowUpRight size={22} aria-hidden="true" />
                </span>
              </Link>
            </Reveal>
          ))}
        </div>
      </div>
    </section>
  )
}
