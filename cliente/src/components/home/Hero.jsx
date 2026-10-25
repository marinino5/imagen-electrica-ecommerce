import { useEffect, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { AnimatePresence, motion, useReducedMotion } from 'framer-motion'
import { Pause, Play } from 'lucide-react'
import { useMediaQuery } from '../../hooks/useMediaQuery'
import { useCategories } from '../../hooks/useCategories'
import { productImage } from '../../lib/catalog'
import SearchBox from '../ui/SearchBox'
import { AddButton, ProductPrice } from '../ui/ProductCard'
import '../../styles/hero.css'

const SLIDE_MS = 8000

const slides = [
  {
    id: 'tablero',
    media: { type: 'video', webm: '/media/hero-tablero.webm', mp4: '/media/hero-tablero.mp4', poster: '/media/hero-tablero-poster.jpg' },
    title: 'Soluciones eléctricas',
    highlight: 'para tu industria',
    text: 'Motores, variadores, cables, iluminación y automatización con disponibilidad inmediata y asesoría técnica.',
    cta: { label: 'Ver productos', to: '/productos', style: 'accent' },
  },
  {
    id: 'motores',
    media: { type: 'video', webm: '/media/hero-motores.webm', mp4: '/media/hero-motores.mp4', poster: '/media/hero-motores-poster.jpg' },
    title: 'Motores y motorreductores',
    highlight: 'listos para trabajar',
    text: 'Potencia y confiabilidad de las marcas que tu planta ya conoce.',
    cta: { label: 'Ver motores', to: '/productos?category=motores-electricos', style: 'accent' },
  },
  {
    id: 'proyectos',
    media: { type: 'image', src: '/img/fachada.webp' },
    title: 'Cotiza tu proyecto',
    highlight: 'con asesoría técnica',
    text: 'Cuéntanos qué necesitas y un asesor te responde con la referencia y el precio.',
    cta: { label: 'Solicitar cotización', to: '/productos?sale_mode=quote', style: 'primary' },
  },
]

function Slide({ slide, active, still }) {
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
        {media.type === 'video' && !still ? (
          <video ref={videoRef} muted loop playsInline preload={slide.id === 'tablero' ? 'auto' : 'metadata'} poster={media.poster}>
            <source src={media.webm} type="video/webm" />
            <source src={media.mp4} type="video/mp4" />
          </video>
        ) : (
          <img src={poster} alt="" fetchPriority={slide.id === 'tablero' ? 'high' : undefined} />
        )}
      </motion.div>
    </motion.div>
  )
}

function FeaturedGlass({ products, reduced }) {
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
    <aside className="glass-card" aria-label="Producto destacado">
      <p className="glass-card__tag">Destacado</p>
      <AnimatePresence mode="wait">
        <motion.div
          key={product.id}
          initial={{ opacity: 0, x: 24 }}
          animate={{ opacity: 1, x: 0 }}
          exit={{ opacity: 0, x: -24 }}
          transition={{ duration: 0.35 }}
        >
          {image && <img src={image} alt="" width="320" height="180" />}
          <p className="glass-card__brand">{product.brand?.name}</p>
          <h3>{product.name}</h3>
          <ProductPrice product={product} />
          <AddButton product={product} block size="sm" />
        </motion.div>
      </AnimatePresence>
      <div className="glass-card__dots">
        {list.map((p, n) => (
          <button key={p.id} type="button" aria-label={`Ver ${p.name}`} aria-current={n === i % list.length} className={n === i % list.length ? 'is-on' : ''} onClick={() => setI(n)} />
        ))}
      </div>
    </aside>
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

  return (
    <section className="hero" aria-roledescription="carrusel" aria-label="Destacados de Imagen Eléctrica">
      <div className="hero__media">
        {slides.map((s, n) => (
          <Slide key={s.id} slide={s} active={n === index} still={still} />
        ))}
        <div className="hero__overlay" />
      </div>

      {/* Barras de progreso estilo historias: la animación CSS marca el avance y, al terminar, pasa a la siguiente */}
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
        {!reduced && (
          <button type="button" className="hero__pause" onClick={() => setPaused((p) => !p)} aria-label={paused ? 'Reanudar carrusel' : 'Pausar carrusel'}>
            {paused ? <Play size={16} /> : <Pause size={16} />}
          </button>
        )}
      </div>

      <div className="container hero__inner">
        <div className="hero__content">
          <AnimatePresence mode="wait">
            <motion.div
              key={slide.id}
              initial={{ opacity: 0, y: 28 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -16 }}
              transition={{ duration: 0.5 }}
              aria-live="polite"
            >
              <h1 className="display hero__title">
                {slide.title} <em>{slide.highlight}</em>
              </h1>
              <p className="hero__text">{slide.text}</p>
              <Link to={slide.cta.to} className={`btn btn--lg ${slide.cta.style === 'accent' ? 'btn--accent' : 'btn--primary hero__btn-primary'}`}>
                {slide.cta.label}
              </Link>
            </motion.div>
          </AnimatePresence>

          <SearchBox className="hero__search" />
        </div>

        {isDesktop && featured.length > 0 && <FeaturedGlass products={featured} reduced={reduced} />}
      </div>
    </section>
  )
}
