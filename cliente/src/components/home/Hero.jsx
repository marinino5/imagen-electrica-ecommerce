import { useEffect, useRef, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { Pause, Play, Search } from 'lucide-react'
import { useReducedMotion } from 'framer-motion'
import { useMediaQuery } from '../../hooks/useMediaQuery'
import Eyebrow from '../ui/Eyebrow'
import Pill from '../ui/Pill'
import '../../styles/hero.css'

const SLIDE_MS = 9000

// Diapositivas del carrusel (hasta 3). Cada una puede llevar un clip propio:
//   media: { poster: '/media/hero-d2-poster.webp',
//            desktop: { mp4, webm },   // 16:9, 1920 px
//            mobile:  { mp4, webm },   // 4:5, 720 px
//            pos: '60% 50%', posSm: '60% 50%' }
// Sin `media`, la diapositiva usa el fondo de marca (degradado ink y arco) hasta tener material con licencia.
// Los clips se registran en docs/recursos.md. El contador, las barras y el avance se ajustan solos.
const allSlides = [
  {
    id: 'motores',
    // D1 pendiente: imagen IA provisional (docs/recursos.md) o material oficial de WEG. Ver scripts/kenburns-hero.sh
    media: null,
    tag: 'Línea WEG',
    title: 'Motores WEG y',
    highlight: 'motorreductores WEG Cestari',
    text: 'Primero el motor industrial de 100 HP; después el motorreductor Cestari. Dos equipos distintos de la línea WEG que distribuimos.',
    cta: { to: '/productos?familia=motores-electricos', label: 'Ver motores' },
  },
  {
    id: 'automatizacion',
    // Pixabay: técnico en tablero (id 159674) y cableado de control (id 86252)
    media: {
      poster: '/media/hero-d2-poster.webp',
      desktop: { mp4: '/media/hero-d2.mp4', webm: '/media/hero-d2.webm' },
      mobile: { mp4: '/media/hero-d2-m.mp4', webm: '/media/hero-d2-m.webm' },
      pos: '60% 50%',
      posSm: '60% 50%',
    },
    tag: 'Control y PLC',
    title: 'Automatización',
    highlight: 'y control',
    text: 'Variadores, arrancadores, contactores y PLC para tableros y procesos industriales.',
    cta: { to: '/productos?familia=control-y-automatizacion', label: 'Ver automatización' },
  },
  {
    id: 'extractores',
    // Pixabay: unidades de ventilación en una cubierta industrial (id 235032)
    media: {
      poster: '/media/hero-d3-poster.webp',
      desktop: { mp4: '/media/hero-d3.mp4', webm: '/media/hero-d3.webm' },
      mobile: { mp4: '/media/hero-d3-m.mp4', webm: '/media/hero-d3-m.webm' },
      pos: '50% 50%',
      posSm: '50% 50%',
    },
    tag: 'Aire y luz',
    title: 'Extractores',
    highlight: 'e iluminación',
    text: 'Ventilación e iluminación industrial para bodegas, talleres y obras.',
    cta: { to: '/productos?familia=ventilacion-y-climatizacion', label: 'Ver ventilación' },
  },
]

// El carrusel solo cuenta las diapositivas que ya tienen clip aprobado: el contador nunca promete más de lo que hay.
// Cuando 'motores' reciba su video (media), aparece sola y el contador pasa a 01/03.
const slides = allSlides.filter((s) => s.media)

const saveData = () => typeof navigator !== 'undefined' && navigator.connection?.saveData === true

function Slide({ slide, index, active, still, mobile }) {
  const videoRef = useRef(null)
  const { media } = slide
  const clip = media && !still ? (mobile ? media.mobile : media.desktop) ?? media.desktop : null

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
  }, [active, clip])

  const fit = media ? { objectPosition: mobile ? media.posSm : media.pos } : undefined

  return (
    <div className={`hero__slide ${media ? '' : 'hero__slide--brand'} ${active ? 'is-active' : ''}`} aria-hidden={!active}>
      {media &&
        (clip ? (
          <video ref={videoRef} key={clip.mp4} aria-hidden="true" tabIndex={-1} muted loop playsInline preload="metadata" poster={media.poster} style={fit}>
            {clip.webm && <source src={clip.webm} type="video/webm" />}
            <source src={clip.mp4} type="video/mp4" />
          </video>
        ) : (
          <img src={media.poster} alt="" width="1920" height="1080" style={fit} fetchPriority={index === 0 ? 'high' : undefined} />
        ))}
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

  // Movimiento reducido o ahorro de datos: solo el poster (imagen fija), sin avance automático
  const still = reduced || saveData()
  const slide = slides[index]
  const next = () => setIndex((n) => (n + 1) % slides.length)
  const pad = (n) => String(n).padStart(2, '0')

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
        {/* Degradado ink solo del lado del texto: el video se ve a color a la derecha */}
        <div className="hero__overlay" />
        <span className="hero__arc" aria-hidden="true" />
      </div>

      <div className="wrap hero__inner">
        <div className="hero__content" key={slide.id}>
          <Eyebrow className="eyebrow--light">{slide.tag}</Eyebrow>
          <h1 className="h-hero hero__title" aria-live="polite">
            <span>{slide.title}</span>
            <em>{slide.highlight}</em>
          </h1>
          <p className="hero__text">{slide.text}</p>

          <div className="hero__ctas">
            <Pill as="link" to={slide.cta.to} tone="o">{slide.cta.label}</Pill>
            <Pill as="link" to="/carrito#cotizacion" tone="l">Cotizar sin cuenta</Pill>
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

      {/* Controles: contador, barras de progreso y pausa */}
      <div className="wrap hero__controls-wrap">
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
          <button type="button" className="hero__pause" onClick={() => setPaused((p) => !p)} aria-label={paused ? 'Reanudar carrusel' : 'Pausar carrusel'}>
            {paused ? <Play size={16} /> : <Pause size={16} />}
          </button>
        </div>
      </div>
    </section>
  )
}
