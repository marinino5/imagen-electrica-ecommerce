import { Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import { ArrowRight, Zap } from 'lucide-react'
import Counter from '../ui/Counter'
import ColombiaMap from '../ui/ColombiaMap'
import ImageSlot from '../ui/ImageSlot'
import { site } from '../../config/site'

// Cada pieza entra con un pequeño rebote al aparecer en pantalla
function Tile({ className, delay = 0, children }) {
  return (
    <motion.div
      className={`bento-about__tile ${className}`}
      initial={{ opacity: 0, y: 36, scale: 0.97 }}
      whileInView={{ opacity: 1, y: 0, scale: 1 }}
      viewport={{ once: true, margin: '0px 0px -60px 0px' }}
      transition={{ duration: 0.65, delay, ease: [0.22, 1, 0.36, 1] }}
    >
      {children}
    </motion.div>
  )
}

// Quiénes somos en mosaico: 6 piezas de tamaños distintos sobre el fondo global
export default function AboutBento() {
  return (
    <section className="section about-bento" aria-labelledby="h-quienes">
      <div className="container">
        <div className="bento-about">
          <Tile className="bento-about__text card">
            <p className="eyebrow"><Zap size={14} aria-hidden="true" /> Quiénes somos</p>
            <h2 id="h-quienes" className="section-title">Soluciones <em>industriales</em></h2>
            <p>
              Imagen Eléctrica es una distribuidora de material eléctrico industrial en {site.city}. Con más de {site.years} años
              de experiencia acompañamos a empresas y personas con motores, variadores, cables, iluminación y automatización.
            </p>
            <p>
              Hoy llevamos esa experiencia a la tienda en línea: compra lo que está disponible o pide tu cotización y un asesor
              te responde.
            </p>
            <Link to="/productos" className="btn btn--accent">Ver productos <ArrowRight size={18} aria-hidden="true" /></Link>
          </Tile>

          <Tile className="bento-about__years" delay={0.08}>
            <span className="bento-about__arc" aria-hidden="true" />
            <p className="bento-about__big" aria-label={`Más de ${site.years} años`}>
              <Counter to={site.years} />
              <span aria-hidden="true">+</span>
            </p>
            <p className="bento-about__cap">años de experiencia</p>
          </Tile>

          <Tile className="bento-about__refs" delay={0.14}>
            <p className="bento-about__big bento-about__big--sm">
              <span aria-hidden="true">+</span>
              <Counter to={1000} />
            </p>
            <p className="bento-about__cap">referencias disponibles</p>
          </Tile>

          <Tile className="bento-about__team" delay={0.1}>
            <ImageSlot label="Foto del equipo" fill />
          </Tile>

          <Tile className="bento-about__ship" delay={0.05}>
            <div className="bento-about__ship-copy">
              <p className="eyebrow"><Zap size={14} aria-hidden="true" /> Cobertura</p>
              <h3>Envíos a todo el país</h3>
              <p>Recoge en Bucaramanga o recíbelo donde estés.</p>
            </div>
            <ColombiaMap className="bento-about__map" />
          </Tile>

          <Tile className="bento-about__store" delay={0.12}>
            <ImageSlot label="Interior de la tienda" fill />
          </Tile>
        </div>
      </div>
    </section>
  )
}
