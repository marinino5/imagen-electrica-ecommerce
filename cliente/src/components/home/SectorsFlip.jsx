import { useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { ArrowRight, RotateCw, Sun, X } from 'lucide-react'
import { sectors } from '../../config/sectors'
import { useLines } from '../../hooks/useLines'
import { lineThumb } from '../../lib/lines'
import SectionLabel from '../ui/SectionLabel'
import Hl from '../ui/Hl'
import Reveal from '../ui/Reveal'
import Bgfx from '../ui/Bgfx'
import '../../styles/home2.css'

// Tarjeta de sector: es un botón (aria-pressed) que gira 180° en 3D al activarlo con clic, toque, Enter o Espacio.
// El reverso (fondo oscuro de marca) dice para qué usa el sector el equipo, recomienda 3 líneas con miniatura y enlaza
// al catálogo. Con prefers-reduced-motion no gira: cambia por fundido (ver home2.css). Escape o «Volver» la regresan.
function SectorCard({ sector, lines }) {
  const [flipped, setFlipped] = useState(false)
  const front = useRef(null)
  const back = useRef(null)

  const flip = (next) => {
    setFlipped(next)
    // El foco acompaña a la cara visible: el lector de pantalla y el teclado no se quedan en la cara oculta
    requestAnimationFrame(() => (next ? back : front).current?.focus({ preventScroll: true }))
  }
  const picks = (sector.lines ?? []).map((slug) => lines.find((l) => l.slug === slug)).filter(Boolean)

  return (
    <li className={`sx ${flipped ? 'is-flipped' : ''}`} onKeyDown={(e) => e.key === 'Escape' && flipped && flip(false)}>
      <div className="sx__inner">
        <button ref={front} type="button" className="sx__face sx__front" aria-pressed={flipped} inert={flipped} onClick={() => flip(true)}>
          <img src={sector.photo} alt="" width="900" height="1125" loading="lazy" />
          <span className="sx__shade" aria-hidden="true" />
          <span className="sx__name">{sector.name}</span>
          <span className="sx__hint" aria-hidden="true"><RotateCw size={15} /> Para qué se usa</span>
          <span className="sr-only">: {sector.alt}. Activa para ver qué equipos recomendamos.</span>
        </button>

        <div className="sx__face sx__back" inert={!flipped} role="group" aria-label={`Equipos para ${sector.name}`}>
          <button ref={back} type="button" className="sx__close" onClick={() => flip(false)} aria-label={`Volver a la foto de ${sector.name}`}>
            <X size={18} aria-hidden="true" />
          </button>
          <h3>{sector.name}</h3>
          <p className="sx__use">{sector.use}</p>
          <ul className="sx__picks">
            {sector.quotes?.map((qt) => (
              <li key={qt.q}>
                <Link to={`/cotizar?q=${encodeURIComponent(qt.q)}`}>
                  <span className="sx__thumb"><Sun size={22} aria-hidden="true" /></span>
                  <span className="sx__pick">{qt.label}</span>
                  <ArrowRight size={16} aria-hidden="true" />
                </Link>
              </li>
            ))}
            {picks.map((l) => (
              <li key={l.slug}>
                <Link to={`/productos/${l.slug}`}>
                  <span className="sx__thumb">{lineThumb(l) && <img src={lineThumb(l)} alt="" width="48" height="48" loading="lazy" />}</span>
                  <span className="sx__pick">{l.name}</span>
                  <ArrowRight size={16} aria-hidden="true" />
                </Link>
              </li>
            ))}
          </ul>
          <Link className="sx__cta" to={sector.to}>{sector.cta ?? 'Ver productos para este sector'} <ArrowRight size={16} aria-hidden="true" /></Link>
        </div>
      </div>
    </li>
  )
}

// Sectores y aplicaciones: una sola sección con 7 tarjetas 4:5 (4 + 3 centradas en escritorio, carrusel con imán en el celular).
export default function SectorsFlip() {
  const { data: lines } = useLines()

  return (
    <section className="rd-sec bg-lav hm-sectors" id="sectores" aria-labelledby="h-sectores">
      <Bgfx a="contactor:l" d />
      <div className="wrap">
        <div className="hm-head">
          <div>
            <SectionLabel n="05">Sectores</SectionLabel>
            <h2 id="h-sectores" className="rd-h2">Sectores y <Hl>aplicaciones</Hl></h2>
            <p className="rd-lead">Toca un sector para ver para qué usa el equipo eléctrico y qué le recomendamos.</p>
          </div>
        </div>
        <Reveal>
          <ul className="sx-grid" aria-label="Sectores que atendemos">
            {sectors.map((s) => <SectorCard key={s.id} sector={s} lines={lines} />)}
          </ul>
        </Reveal>
      </div>
    </section>
  )
}
