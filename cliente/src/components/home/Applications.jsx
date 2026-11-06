import { Link } from 'react-router-dom'
import { ArrowUpRight, CircuitBoard, Droplets, Fan, Lightbulb, Settings2, Sun } from 'lucide-react'
import Reveal from '../ui/Reveal'
import SectionHeading from '../ui/SectionHeading'
import '../../styles/home.css'

// Cada aplicación lleva a productos filtrados. Fotos de Pixabay (ver docs/recursos.md); sin foto
// (iluminación, pendiente) la tarjeta usa el degradado lavanda con un ícono violeta y un arco.
const applications = [
  { name: 'Bombeo', tag: 'Motores', icon: Droplets, image: 'bombeo', alt: 'Tuberías y válvulas de un sistema de bombeo', to: '/productos?category=motores-electricos', size: 'a' },
  { name: 'Ventilación y extracción', tag: 'Ventilación', icon: Fan, image: 'ventilacion', alt: 'Ventiladores industriales de gran tamaño', to: '/productos?category=ventilacion', size: 'b' },
  { name: 'Bandas transportadoras', tag: 'Motorreductores', icon: Settings2, image: 'bandas', alt: 'Banda transportadora en una planta', to: '/productos?category=motorreductores', size: 'c' },
  { name: 'Tableros y automatización', tag: 'Automatización', icon: CircuitBoard, image: 'tableros', alt: 'Electricista revisando un tablero eléctrico', to: '/productos?category=plc', size: 'd' },
  { name: 'Iluminación industrial', tag: 'Iluminación', icon: Lightbulb, image: null, to: '/productos?category=iluminacion', size: 'e' },
  { name: 'Energía solar', tag: 'Solar', icon: Sun, image: 'solar', alt: 'Campo de paneles solares', to: '/productos?category=energia-solar', size: 'f' },
]

export default function Applications() {
  return (
    <section className="section" aria-labelledby="h-aplicaciones">
      <div className="container">
        <SectionHeading id="h-aplicaciones" eyebrow="Para tu planta" lead="Cuéntanos qué necesitas mover, ventilar, iluminar o automatizar.">
          Soluciones por <em>aplicación</em>
        </SectionHeading>

        <div className="bento">
          {applications.map(({ name, tag, icon: Icon, image, alt, to, size }, i) => (
            <Reveal key={name} delay={i * 0.06} className={`bento__item bento__item--${size}`}>
              <Link to={to} className={`bento__link ${image ? 'has-photo' : ''}`}>
                {image ? (
                  <img
                    src={`/img/aplicaciones/${image}-sm.webp`}
                    srcSet={`/img/aplicaciones/${image}-sm.webp 640w, /img/aplicaciones/${image}.webp 1280w`}
                    sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw"
                    alt={alt}
                    width="640"
                    height="427"
                    loading="lazy"
                  />
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
