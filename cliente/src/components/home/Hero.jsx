import { useEffect, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { AnimatePresence, motion, useReducedMotion } from 'framer-motion'
import { Pause, Play, Zap } from 'lucide-react'
import { useMediaQuery } from '../../hooks/useMediaQuery'
import { useCategories } from '../../hooks/useCategories'
import { productImage } from '../../lib/catalog'
import SearchBox from '../ui/SearchBox'
import Current from '../ui/Current'
import { AddButton, ProductPrice } from '../ui/ProductCard'
import '../../styles/hero.css'

const SLIDE_MS = 8000

// Diapositivas del carrusel. Para agregar otra (por ejemplo el video de proyectos) basta una entrada nueva:
// { id: 'proyectos', media: { type: 'video', webm: '/media/hero-proyectos.webm', mp4: '/media/hero-proyectos.mp4',
//   poster: '/media/hero-proyectos-poster.jpg', pos: '50% 50%', posSm: '50% 50%' }, title, highlight, text }
// El contador, las barras de progreso y el avance automático se ajustan solos a la cantidad de entradas.
const slides = [
  {
    id: 'tablero',
    // pos / posSm: punto de enfoque en escritorio y en celular (donde el recorte es horizontal)
    media: { type: 'video', webm: '/media/hero-tablero.webm', mp4: '/media/hero-tablero.mp4', poster: '/media/hero-tablero-poster.jpg', pos: '72% 45%', posSm: '78% 50%' },
    title: 'Soluciones eléctricas',
    highlight: 'para tu industria',
    text: 'Motores, variadores, cables e iluminación con disponibilidad y asesoría técnica.',
  },
  {
    id: 'motores',
    media: { type: 'video', webm: '/media/hero-motores.webm', mp4: '/media/hero-motores.mp4', poster: '/media/hero-motores-poster.jpg', pos: '50% 58%', posSm: '46% 50%' },
    title: 'Motores y motorreductores',
    highlight: 'listos para trabajar',
    text: 'Potencia y confiabilidad de las marcas que tu planta ya conoce.',
  },
]

function Slide({ slide, active, still, mobile }) {
  const videoRef = useRef(null)
  const { media } = slide

  // Solo el video de la diapositiva activa se reproduce
  useEffect(() => {
    const video = videoRef.current
    if (!video) return
    if (active) {
      video.currentTime = 0
      video.play().catch(() => {})
    } else {
      video.pause()
    }
  }, [active])

  const poster = media.type === 'video' ? media.poster : media.src
  const fit = { objectPosition: mobile ? media.posSm : media.pos }

  return (
    <motion.div
      className="hero__slide"
      initial={false}
      animate={{ opacity: active ? 1 : 0 }}
      transition={{ duration: 1 }}
      aria-hidden={!active}
    >
      {/* Efecto Ken Burns: zoom lento mientras la diapositiva está activa */}
      <motion.div
        className="hero__kenburns"
        initial={false}
        animate={{ scale: active ? 1.12 : 1 }}
        transition={active ? { duration: SLIDE_MS / 1000 + 1, ease: 'linear' } : { duration: 0, delay: 1 }}
      >
        <div className="hero__sharp">
          {media.type === 'video' && !still ? (
            <video ref={videoRef} muted loop playsInline preload={slide.id === 'tablero' ? 'auto' : 'metadata'} poster={media.poster} style={fit}>
              <source src={media.webm} type="video/webm" />
              <source src={media.mp4} type="video/mp4" />
            </video>
          ) : (
            <img src={poster} alt="" style={fit} fetchPriority={slide.id === 'tablero' ? 'high' : undefined} />
          )}
        </div>
      </motion.div>
    </motion.div>
  )
}

function FeaturedCard({ products, reduced }) {
  const { data: categories } = useCategories()
  const [i, setI] = useState(0)
  const list = products.slice(0, 5)

  useEffect(() => {
    if (reduced || list.length < 2) return
    const timer = setInterval(() => setI((n) => (n + 1) % list.length), 5000)
    return () => clearInterval(timer)
  }, [reduced, list.length])

  if (list.length === 0) return null
  const product = list[i % list.length]
  const image = productImage(product, categories)

  return (
    <aside className="feat-card" aria-label="Producto destacado">
      <p className="feat-card__tag"><Zap size={14} aria-hidden="true" /> Destacado</p>
      <AnimatePresence mode="wait">
        <motion.div
          key={product.id}
          initial={{ opacity: 0, x: 24 }}
          animate={{ opacity: 1, x: 0 }}
          exit={{ opacity: 0, x: -24 }}
          transition={{ duration: 0.35 }}
        >
          {image && <img src={image} alt="" width="320" height="240" />}
          <p className="feat-card__brand">{product.brand?.name}</p>
          <h3>{product.name}</h3>
          <ProductPrice product={product} />
          <AddButton product={product} block size="sm" />
        </motion.div>
      </AnimatePresence>
      <div className="feat-card__dots">
        {list.map((p, n) => (
          <button key={p.id} type="button" aria-label={`Ver ${p.name}`} aria-current={n === i % list.length} className={n === i % list.length ? 'is-on' : ''} onClick={() => setI(n)} />
        ))}
      </div>
    </aside>
  )
}

// Arco naranja grande en trazo que asoma desde la esquina inferior derecha y se dibuja al cargar
function HeroArc() {
  return (
    <svg className="hero__arc" viewBox="0 0 600 600" aria-hidden="true" fill="none">
      <motion.path
        d="M590 40 A 550 550 0 0 1 40 590"
        stroke="var(--accent)"
        strokeWidth="6"
        strokeLinecap="round"
        initial={{ pathLength: 0 }}
        animate={{ pathLength: 1 }}
        transition={{ duration: 1.8, ease: [0.22, 1, 0.36, 1], delay: 0.3 }}
      />
    </svg>
  )
}

export default function Hero({ featured }) {
  const reduced = useReducedMotion()
  const isMobile = useMediaQuery('(max-width: 767px)')
  const isDesktop = useMediaQuery('(min-width: 1024px)')
  const [index, setIndex] = useState(0)
  const [paused, setPaused] = useState(false)

  // Celular o movimiento reducido: imagen fija en lugar del video y sin avance automático
  const still = isMobile || reduced
  const slide = slides[index]
  const next = () => setIndex((n) => (n + 1) % slides.length)
  const pad = (n) => String(n).padStart(2, '0')

  return (
    <section className="hero" aria-roledescription="carrusel" aria-label="Destacados de Imagen Eléctrica">
      <div className="hero__media">
        {slides.map((s, n) => (
          <Slide key={s.id} slide={s} active={n === index} still={still} mobile={isMobile} />
        ))}
        <div className="hero__overlay" />
      </div>

      <div className="hero__deco" aria-hidden="true">
        <HeroArc />
        <Current
          className="hero__current"
          viewBox="0 0 1000 120"
          d="M0 70 C 140 10, 260 120, 400 64 S 660 10, 800 64 S 940 100, 1000 56"
          offset={['start start', 'end start']}
        />
      </div>

      <div className="container hero__inner">
        <div className="hero__content">
          <AnimatePresence mode="wait">
            <motion.div
              key={slide.id}
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              transition={{ duration: 0.3, ease: 'easeInOut' }}
              aria-live="polite"
            >
              <h1 className="display hero__title">
                {slide.title} <em>{slide.highlight}</em>
              </h1>
              <p className="hero__text">{slide.text}</p>
            </motion.div>
          </AnimatePresence>

          <div className="hero__ctas">
            <Link to="/productos" className="btn btn--accent btn--lg">Ver productos</Link>
            <Link to="/productos?sale_mode=quote" className="btn btn--ghost-light btn--lg">Cotizar proyecto</Link>
          </div>

          <SearchBox className="hero__search" />
        </div>

        {isDesktop && featured.length > 0 && <FeaturedCard products={featured} reduced={reduced} />}
      </div>

      {/* Controles abajo a la izquierda: contador, barras de progreso cortas y pausa */}
      <div className="container hero__controls-wrap">
        <div className="hero__controls">
          <span className="hero__count" aria-hidden="true">{pad(index + 1)} / {pad(slides.length)}</span>
          <div className="hero__progress" role="tablist" aria-label="Diapositivas">
            {slides.map((s, n) => (
              <button
                key={s.id}
                type="button"
                role="tab"
                aria-selected={n === index}
                aria-label={`Ir a la diapositiva ${n + 1}: ${s.title} ${s.highlight}`}
                className={`bar ${n < index ? 'is-done' : ''} ${n === index ? 'is-active' : ''} ${paused ? 'is-paused' : ''}`}
                onClick={() => setIndex(n)}
              >
                <span
                  key={n === index ? `a-${index}` : n}
                  style={{ animationDuration: `${SLIDE_MS}ms` }}
                  onAnimationEnd={n === index ? next : undefined}
                />
              </button>
            ))}
          </div>
          {!reduced && (
            <button type="button" className="hero__pause" onClick={() => setPaused((p) => !p)} aria-label={paused ? 'Reanudar carrusel' : 'Pausar carrusel'}>
              {paused ? <Play size={16} /> : <Pause size={16} />}
            </button>
          )}
        </div>
      </div>
    </section>
  )
}
