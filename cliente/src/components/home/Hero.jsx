import { useEffect, useRef, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { Pause, Play, Search } from 'lucide-react'
import { useReducedMotion } from 'framer-motion'
import { useMediaQuery } from '../../hooks/useMediaQuery'
import Pill from '../ui/Pill'
import '../../styles/hero.css'

const SLIDE_MS = 9000

// Orden fijo: video, imagen, video, imagen (Motores, Motorreductores, Automatización y control, Ventilación y extracción).
// Los videos son de Pixabay (rotor de un electromotor y técnico en un tablero) y las imágenes combinan una foto de planta
// con la foto oficial del producto recortado. Fuentes y licencias en docs/creditos-media.md.
const slides = [
  {
    id: 'motores',
    short: 'Motores',
    tag: 'Motores eléctricos',
    title: 'Motores',
    highlight: 'industriales WEG',
    text: 'Del monofásico de taller al trifásico de planta, con la referencia exacta que tu equipo necesita.',
    cta: { to: '/productos?familia=motores-electricos', label: 'Ver motores' },
    video: {
      poster: '/media/hero-v1-poster.webp',
      desktop: { mp4: '/media/hero-v1.mp4', webm: '/media/hero-v1.webm' },
      mobile: { mp4: '/media/hero-v1-m.mp4', webm: '/media/hero-v1-m.webm' },
      pos: '55% 50%',
      alt: 'Primer plano del eje de un electromotor girando',
    },
  },
  {
    id: 'motorreductores',
    short: 'Motorreductores',
    tag: 'Reductores y motorreductores',
    title: 'Motorreductores',
    highlight: 'WEG Cestari',
    text: 'Para bandas, mezcladoras y elevadores: sinfín y corona o helicoidales, listos para tu máquina.',
    cta: { to: '/productos?familia=reductores-y-motorreductores', label: 'Ver motorreductores' },
    bg: { src: '/img/fotos/hero-banda.webp', alt: 'Banda transportadora de rodillos con su motorreductor', pos: '40% 50%' },
    product: { src: '/img/catalogo/weg-cestari-wcg01.webp', alt: 'Motorreductor de sinfín y corona WEG Cestari WCG01', caption: 'WEG Cestari WCG01' },
  },
  {
    id: 'automatizacion',
    short: 'Automatización y control',
    tag: 'Control y PLC',
    title: 'Automatización',
    highlight: 'y control',
    text: 'Variadores, arrancadores, contactores y PLC para tableros y procesos industriales.',
    cta: { to: '/productos?familia=control-y-automatizacion', label: 'Ver automatización' },
    video: {
      poster: '/media/hero-v3-poster.webp',
      desktop: { mp4: '/media/hero-v3.mp4', webm: '/media/hero-v3.webm' },
      mobile: { mp4: '/media/hero-v3-m.mp4', webm: '/media/hero-v3-m.webm' },
      pos: '62% 50%',
      alt: 'Técnico revisando los breakers de un tablero eléctrico con un multímetro',
    },
  },
  {
    id: 'ventilacion',
    short: 'Ventilación y extracción',
    tag: 'Ventilación y extracción',
    title: 'Extractores',
    highlight: 'industriales',
    text: 'Aire limpio para naves, talleres y plantas: extractores axiales, centrífugos y de tejado.',
    cta: { to: '/productos?familia=ventilacion-y-climatizacion', label: 'Ver ventilación' },
    bg: { src: '/img/fotos/hero-extractores.webp', alt: 'Dos extractores axiales industriales en el muro de una nave', pos: '50% 50%' },
    product: { src: '/img/catalogo/soler-palau-extractor-axial.webp', alt: 'Extractor axial industrial Soler & Palau', caption: 'Soler & Palau' },
  },
]

const saveData = () => typeof navigator !== 'undefined' && navigator.connection?.saveData === true

function Slide({ slide, index, active, still, mobile }) {
  const videoRef = useRef(null)
  const { video, bg, product } = slide
  const clip = video && !still ? (mobile ? video.mobile : video.desktop) ?? video.desktop : null

  // Solo el video de la diapositiva activa se reproduce
  useEffect(() => {
    const el = videoRef.current
    if (!el) return
    if (active) {
      el.currentTime = 0
      el.play().catch(() => {})
    } else {
      el.pause()
    }
  }, [active, clip])

  return (
    <div className={`hero__slide ${active ? 'is-active' : ''}`} aria-hidden={!active}>
      {video &&
        (clip ? (
          <video ref={videoRef} key={clip.mp4} aria-hidden="true" tabIndex={-1} muted loop playsInline preload={index === 0 ? 'auto' : 'metadata'} poster={video.poster} style={{ objectPosition: video.pos }}>
            {clip.webm && <source src={clip.webm} type="video/webm" />}
            <source src={clip.mp4} type="video/mp4" />
          </video>
        ) : (
          <img src={video.poster} alt={video.alt} width="1920" height="1080" style={{ objectPosition: video.pos }} />
        ))}
      {bg && <img className="hero__bg" src={bg.src} alt={bg.alt} width="1600" height="900" style={{ objectPosition: bg.pos, '--zoom': bg.zoom, '--zoom-origin': bg.origin }} fetchPriority={index === 0 ? 'high' : undefined} loading={index === 0 ? 'eager' : 'lazy'} />}
      {product && (
        <figure className="hero__stage">
          <span className="hero__glow" aria-hidden="true" />
          <svg className="hero__arc" viewBox="0 0 1000 500" aria-hidden="true" focusable="false">
            <path pathLength="1" d="M0 500 A500 500 0 0 1 1000 500" />
          </svg>
          <span className="hero__floor" aria-hidden="true" />
          <img src={product.src} alt={product.alt} width="1000" height="1000" loading={index === 0 ? 'eager' : 'lazy'} />
          <figcaption>{product.caption}</figcaption>
        </figure>
      )}
    </div>
  )
}

export default function Hero() {
  const reduced = useReducedMotion()
  const navigate = useNavigate()
  const isMobile = useMediaQuery('(max-width: 767px)')
  const [index, setIndex] = useState(0)
  const [paused, setPaused] = useState(false)
  const [term, setTerm] = useState('')

  // Movimiento reducido o ahorro de datos: imagen fija y sin avance automático
  const still = reduced || saveData()
  const slide = slides[index]
  const nextSlide = slides[(index + 1) % slides.length]
  const next = () => setIndex((n) => (n + 1) % slides.length)

  const search = (e) => {
    e.preventDefault()
    const q = term.trim()
    navigate(q ? `/productos?q=${encodeURIComponent(q)}` : '/productos')
  }

  return (
    <section className="hero" aria-roledescription="carrusel" aria-label="Destacados de Imagen Eléctrica">
      <div className="hero__media">
        {slides.map((s, n) => (
          <Slide key={s.id} slide={s} index={n} active={n === index} still={still} mobile={isMobile} />
        ))}
        <div className="hero__tint" aria-hidden="true" />
        <div className="hero__overlay" />
      </div>

      <div className="wrap hero__inner">
        <div className="hero__content" key={slide.id}>
          <p className="hero__tag">{slide.tag}</p>
          <h1 className="hero__title" aria-live="polite">
            <span>{slide.title}</span>
            <em>{slide.highlight}</em>
          </h1>
          <p className="hero__text">{slide.text}</p>
          <div className="hero__ctas">
            <Pill as="link" to={slide.cta.to} tone="o">{slide.cta.label}</Pill>
          </div>
          <form className="hero__search" role="search" onSubmit={search}>
            <Search size={18} aria-hidden="true" />
            <input
              type="search"
              enterKeyHint="search"
              autoComplete="off"
              placeholder="Busca una referencia: motor 5 HP, CFW300…"
              aria-label="Buscar en el catálogo"
              value={term}
              onChange={(e) => setTerm(e.target.value)}
            />
            <button type="submit">Buscar</button>
          </form>
        </div>
      </div>

      <div className="wrap hero__dock">
        <div className="hero__progress" role="tablist" aria-label="Diapositivas">
          {slides.map((s, n) => (
            <button
              key={s.id}
              type="button"
              role="tab"
              aria-selected={n === index}
              aria-label={`Ir a la diapositiva ${n + 1}: ${s.short}`}
              className={`bar ${n < index ? 'is-done' : ''} ${n === index ? 'is-active' : ''} ${paused || still ? 'is-paused' : ''}`}
              onClick={() => setIndex(n)}
            >
              <span key={n === index ? `a-${index}` : n} style={{ animationDuration: `${SLIDE_MS}ms` }} onAnimationEnd={n === index ? next : undefined} />
            </button>
          ))}
        </div>
        <button type="button" className="hero__next" onClick={next} aria-label={`Ir a la siguiente diapositiva: ${nextSlide.short}`}>
          <span>Siguiente:</span> {nextSlide.short}
        </button>
        <button type="button" className="hero__pause" onClick={() => setPaused((p) => !p)} aria-label={paused ? 'Reanudar carrusel' : 'Pausar carrusel'}>
          {paused ? <Play size={16} /> : <Pause size={16} />}
        </button>
      </div>
    </section>
  )
}
