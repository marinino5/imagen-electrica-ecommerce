import { Link } from 'react-router-dom'
import { motion, useReducedMotion } from 'framer-motion'
import { MapPin, Zap } from 'lucide-react'
import WordReveal from '../ui/WordReveal'
import { site } from '../../config/site'

// Insignia blanca que flota con un movimiento leve (quieta con prefers-reduced-motion)
function FloatBadge({ className, children, delay = 0 }) {
  const reduced = useReducedMotion()
  return (
    <motion.div
      className={`float-badge ${className}`}
      animate={reduced ? undefined : { y: [0, -9, 0] }}
      transition={{ duration: 4.5, repeat: Infinity, ease: 'easeInOut', delay }}
    >
      {children}
    </motion.div>
  )
}

// Portada: la fachada como banner a sangre, con una capa violeta suave y un degradado ink abajo para el texto
export default function AboutCover() {
  return (
    <header className="cover about-cover on-violet">
      <img className="about-cover__img" src="/img/fachada.webp" alt="Fachada de Imagen Eléctrica en Bucaramanga" width="1600" height="800" fetchPriority="high" />
      <span className="about-cover__tint" aria-hidden="true" />
      <span className="about-cover__shade" aria-hidden="true" />

      <div className="container about-cover__inner">
        <div className="about-cover__content">
          <p className="tag-pill"><Zap size={14} aria-hidden="true" /> Nosotros</p>
          <WordReveal
            className="display about-cover__title"
            delay={0.15}
            lines={[{ text: `MÁS DE ${site.years} AÑOS` }, { text: 'IMPULSANDO' }, { text: 'TUS PROYECTOS', accent: true }]}
          />
          <p className="about-cover__text">
            {site.tagline} en {site.city.split(',')[0]}: material eléctrico industrial con asesoría técnica.
          </p>
          <div className="about-cover__ctas">
            <Link to="/productos" className="btn btn--accent btn--lg">Ver productos</Link>
            <a href="#visitanos" className="btn btn--ghost-light btn--lg">Visítanos</a>
          </div>
        </div>

        <div className="about-cover__badges">
          <FloatBadge className="float-badge--years">
            <strong>+{site.years}</strong>
            <span>años</span>
          </FloatBadge>
          <FloatBadge className="float-badge--address" delay={1.2}>
            <MapPin size={18} aria-hidden="true" />
            <span>{site.address} · {site.city.split(',')[0]}</span>
          </FloatBadge>
        </div>
      </div>
    </header>
  )
}
