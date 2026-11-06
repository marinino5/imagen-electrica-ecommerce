import { useRef, useState } from 'react'
import { Plus } from 'lucide-react'
import { site } from '../../config/site'
import Bezel from '../ui/Bezel'
import Eyebrow from '../ui/Eyebrow'
import Reveal from '../ui/Reveal'

// Línea de tiempo interactiva. Solo hay datos confirmados al inicio y al final: los hitos intermedios
// quedan como espacios para que la empresa entregue el año y el texto. No se inventan fechas.
const milestones = [
  { id: 'inicio', label: `Hace más de ${site.years} años`, title: 'Empieza Imagen Eléctrica', text: 'Empezamos a distribuir material eléctrico industrial en Bucaramanga.' },
  { id: 'h2', pending: true },
  { id: 'h3', pending: true },
  { id: 'h4', pending: true },
  { id: 'hoy', label: 'Hoy', title: 'Tu tienda en línea', text: 'Catálogo por líneas con compra en línea, cotización sin cuenta y asesoría técnica por WhatsApp.' },
]

export default function Timeline() {
  const [active, setActive] = useState(0)
  const refs = useRef([])
  const current = milestones[active]

  // Flechas del teclado entre los hitos
  const onKey = (e) => {
    const step = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0
    if (!step) return
    e.preventDefault()
    const next = (active + step + milestones.length) % milestones.length
    setActive(next)
    refs.current[next]?.focus()
  }

  return (
    <section className="wrap ab-time" aria-labelledby="h-trayectoria">
      <Reveal>
        <Eyebrow>Trayectoria</Eyebrow>
        <h2 id="h-trayectoria" className="h2-sys">Más de {site.years} años <em>de camino</em></h2>
      </Reveal>

      <Reveal delay={0.06}>
        <div className="ab-time__rail" role="tablist" aria-label="Hitos de la trayectoria" onKeyDown={onKey}>
          <span className="ab-time__line" aria-hidden="true" />
          <span className="ab-time__fill" aria-hidden="true" style={{ transform: `scaleX(${active / (milestones.length - 1)})` }} />
          {milestones.map((m, i) => (
            <button
              key={m.id}
              ref={(el) => (refs.current[i] = el)}
              type="button"
              role="tab"
              id={`hito-${m.id}`}
              aria-selected={i === active}
              aria-controls="hito-panel"
              tabIndex={i === active ? 0 : -1}
              className={`ab-time__node ${i === active ? 'is-on' : ''} ${i < active ? 'is-past' : ''} ${m.pending ? 'is-pending' : ''}`}
              onClick={() => setActive(i)}
            >
              <span className="ab-time__dot">{m.pending ? <Plus size={14} aria-hidden="true" /> : null}</span>
              <span className="ab-time__label">{m.pending ? 'Por confirmar' : m.label}</span>
            </button>
          ))}
        </div>

        <Bezel className="ab-time__panel">
          <div className="ab-time__core" role="tabpanel" id="hito-panel" aria-labelledby={`hito-${current.id}`} key={current.id}>
            {current.pending ? (
              <>
                <small>Espacio para un hito</small>
                <h3>Aquí va un momento de la historia</h3>
                <p>La empresa entrega el año, qué pasó y, si hay, una foto. Mientras tanto no se publican fechas que no estén confirmadas.</p>
              </>
            ) : (
              <>
                <small>{current.label}</small>
                <h3>{current.title}</h3>
                <p>{current.text}</p>
              </>
            )}
          </div>
        </Bezel>
      </Reveal>
    </section>
  )
}
