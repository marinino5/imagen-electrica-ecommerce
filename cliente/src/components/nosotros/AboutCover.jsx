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

// Portada dividida: violeta con el mensaje a la izquierda, la fachada en un arco con anillo naranja a la derecha
export default function AboutCover() {
  return (
    <header className="cover about-cover">
      <div className="about-cover__left on-violet">
        <span className="about-cover__ring about-cover__ring--a" aria-hidden="true" />
        <span className="about-cover__ring about-cover__ring--b" aria-hidden="true" />
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
      </div>

      <div className="about-cover__right">
        <span className="about-cover__dots deco-dots" aria-hidden="true" />
        <div className="arch-photo">
          {/* Anillo naranja grueso que se dibuja al cargar */}
          <svg className="arch-photo__ring" viewBox="0 0 400 420" preserveAspectRatio="none" fill="none" aria-hidden="true">
            <motion.path
              d="M6 388 Q6 414 32 414 H368 Q394 414 394 388 V200 A194 194 0 0 0 6 200 Z"
              stroke="var(--accent)"
              strokeWidth="11"
              strokeLinejoin="round"
              vectorEffect="non-scaling-stroke"
              initial={{ pathLength: 0 }}
              animate={{ pathLength: 1 }}
              transition={{ duration: 1.8, ease: [0.22, 1, 0.36, 1], delay: 0.3 }}
            />
          </svg>
          <img className="arch-photo__img" src="/img/fachada.webp" alt="Fachada de Imagen Eléctrica en Bucaramanga" width="800" height="1000" fetchPriority="high" />

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
