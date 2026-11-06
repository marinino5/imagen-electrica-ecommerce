import { useEffect, useRef, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { Cog, Fan, Pause, Play, Search } from 'lucide-react'
import { useReducedMotion } from 'framer-motion'
import { useMediaQuery } from '../../hooks/useMediaQuery'
import { useFamilies } from '../../hooks/useFamilies'
import { site } from '../../config/site'
import Counter from '../ui/Counter'
import Eyebrow from '../ui/Eyebrow'
import Pill from '../ui/Pill'
import '../../styles/hero.css'

const SLIDE_MS = 9000

// Diapositivas. Con `media` llevan video a pantalla completa (16:9 en escritorio, 4:5 en celular).
// Sin `media` se dibuja el diseño de marca (arcos + icono grande): no se rellena con material genérico.
// Los clips y su licencia están en docs/creditos-media.md; lo que falta, en docs/imagenes-pendientes.md.
const slides = [
  {
    id: 'automatizacion',
    media: {
      poster: '/media/hero-d2-poster.webp',
      desktop: { mp4: '/media/hero-d2.mp4', webm: '/media/hero-d2.webm' },
      mobile: { mp4: '/media/hero-d2-m.mp4', webm: '/media/hero-d2-m.webm' },
      pos: '60% 50%',
      posSm: '65% 50%',
      alt: 'Manos de un técnico conectando el cableado de control de un tablero eléctrico',
    },
    tag: 'Control y PLC',
    title: 'Automatización',
    highlight: 'y control',
    text: 'Variadores, arrancadores, contactores y PLC para tableros y procesos industriales.',
    cta: { to: '/productos?familia=control-y-automatizacion', label: 'Ver automatización' },
  },
  {
    id: 'motores',
    art: Cog,
    tag: 'Línea WEG',
    title: 'Motores WEG y',
    highlight: 'motorreductores WEG Cestari',
    text: 'Del motor monofásico al industrial de 100 HP, y el motorreductor para tu banda o tu mezcladora.',
    cta: { to: '/productos?familia=motores-electricos', label: 'Ver motores' },
  },
  {
    id: 'extractores',
    art: Fan,
    spin: true,
    tag: 'Aire y luz',
    title: 'Extractores',
    highlight: 'e iluminación',
    text: 'Ventilación e iluminación industrial para bodegas, talleres y obras.',
    cta: { to: '/productos?familia=ventilacion-y-climatizacion', label: 'Ver ventilación' },
  },
]

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
  const Art = slide.art

  return (
    <div className={`hero__slide ${media ? '' : 'hero__slide--brand'} ${active ? 'is-active' : ''}`} aria-hidden={!active}>
      {media &&
        (clip ? (
          <video ref={videoRef} key={clip.mp4} aria-hidden="true" tabIndex={-1} muted loop playsInline preload={index === 0 ? 'auto' : 'metadata'} poster={media.poster} style={fit}>
            {clip.webm && <source src={clip.webm} type="video/webm" />}
            <source src={clip.mp4} type="video/mp4" />
          </video>
        ) : (
          <img src={media.poster} alt={media.alt} width="1920" height="1080" style={fit} fetchPriority={index === 0 ? 'high' : undefined} />
        ))}
      {Art && (
        <div className="hero__art" aria-hidden="true">
          <span className="hero__ring hero__ring--a" />
          <span className="hero__ring hero__ring--b" />
          <span className="hero__ring hero__ring--c" />
          <Art className={slide.spin ? 'is-spin' : ''} strokeWidth={0.8} />
        </div>
      )}
    </div>
  )
}

export default function Hero() {
  const reduced = useReducedMotion()
  const navigate = useNavigate()
  const isMobile = useMediaQuery('(max-width: 767px)')
  const { meta } = useFamilies()
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

  // Las cifras salen de la API: mientras no lleguen no se muestra ninguna (nada de números de relleno)
  const stats = [
    { value: site.years, prefix: '+', label: 'años en el sector' },
    { value: meta.references, label: 'referencias en inventario' },
    { value: meta.lines, label: 'fichas de producto' },
    { value: meta.families, label: 'familias del portafolio' },
  ].filter((s) => s.value > 0)

  return (
    <section className="hero" aria-roledescription="carrusel" aria-label="Destacados de Imagen Eléctrica">
      <div className="hero__media">
        {slides.map((s, n) => (
          <Slide key={s.id} slide={s} index={n} active={n === index} still={still} mobile={isMobile} />
        ))}
        {/* Degradado ink: abajo y a la izquierda, donde va el texto; el video se ve a color arriba a la derecha */}
        <div className="hero__overlay" />
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

      <div className="wrap hero__dock">
        {/* Vidrio oscuro translúcido sobre el video: el mensaje de la empresa y las cifras con contador */}
        <div className="hero__glass">
          <p className="hero__glass-lead">
            Una distribuidora de Bucaramanga que conoce <strong>cada referencia que vende</strong>.
          </p>
          {stats.length > 0 && (
            <ul className="hero__stats" aria-label="Imagen Eléctrica en cifras">
              {stats.map((s) => (
                <li key={s.label}>
                  <Counter to={s.value} prefix={s.prefix} className="hero__stat-value" />
                  <span>{s.label}</span>
                </li>
              ))}
            </ul>
          )}
        </div>

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
