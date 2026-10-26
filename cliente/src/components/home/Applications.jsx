import { Link } from 'react-router-dom'
import { ArrowUpRight, CircuitBoard, Droplets, Fan, Lightbulb, Settings2 } from 'lucide-react'
import Reveal from '../ui/Reveal'
import SectionHeading from '../ui/SectionHeading'
import '../../styles/home.css'

// Cada aplicación lleva a productos filtrados. Mientras `image` sea null (fotos pendientes),
// la tarjeta usa el degradado lavanda con un ícono violeta y un arco.
const applications = [
  { name: 'Bombeo', tag: 'Motores', icon: Droplets, image: null, to: '/productos?category=motores-electricos', size: 'a' },
  { name: 'Ventilación y extracción', tag: 'Ventilación', icon: Fan, image: null, to: '/productos?category=ventilacion', size: 'b' },
  { name: 'Bandas transportadoras', tag: 'Motorreductores', icon: Settings2, image: null, to: '/productos?category=motorreductores', size: 'c' },
  { name: 'Tableros y automatización', tag: 'Automatización', icon: CircuitBoard, image: null, to: '/productos?category=plc', size: 'd' },
  { name: 'Iluminación industrial', tag: 'Iluminación', icon: Lightbulb, image: null, to: '/productos?category=iluminacion', size: 'e' },
]

export default function Applications() {
  return (
    <section className="section" aria-labelledby="h-aplicaciones">
      <div className="container">
        <SectionHeading id="h-aplicaciones" eyebrow="Para tu planta" lead="Cuéntanos qué necesitas mover, ventilar, iluminar o automatizar.">
          Soluciones por <em>aplicación</em>
        </SectionHeading>

        <div className="bento">
          {applications.map(({ name, tag, icon: Icon, image, to, size }, i) => (
            <Reveal key={name} delay={i * 0.06} className={`bento__item bento__item--${size}`}>
              <Link to={to} className={`bento__link ${image ? 'has-photo' : ''}`}>
                {image ? (
                  <img src={image} alt="" loading="lazy" />
                ) : (
                  <>
                    <span className="bento__arc" aria-hidden="true" />
                    <Icon size={72} strokeWidth={1.25} className="bento__icon" aria-hidden="true" />
                  </>
                )}
                {image && <span className="bento__shade" aria-hidden="true" />}
                <span className="bento__tag">{tag}</span>
                <span className="bento__title">
                  {name}
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
