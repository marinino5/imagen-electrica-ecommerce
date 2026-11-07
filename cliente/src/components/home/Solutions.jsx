import { useState } from 'react'
import { Link } from 'react-router-dom'
import { ArrowRight } from 'lucide-react'
import SectionLabel from '../ui/SectionLabel'
import Hl from '../ui/Hl'
import Reveal from '../ui/Reveal'
import '../../styles/home.css'

// Soluciones por aplicación: paneles verticales de foto que se expanden al pasar el mouse o enfocar (estilo WEG).
// El panel abierto muestra título, frase y 3 líneas recomendadas con enlace. En celular es un acordeón con fotos.
const solutions = [
  {
    id: 'bandas',
    title: 'Bandas transportadoras',
    text: 'Mueve carga todo el día: motorreductor, variador y la protección justa.',
    photo: '/img/aplicaciones/bandas.webp',
    alt: 'Banda transportadora en una planta de producción',
    lines: [
      ['motorreductor-sinfin-wcg01', 'Motorreductor sinfín WCG01'],
      ['reductor-helicoidal-wcg20', 'Reductor helicoidal WCG20'],
      ['variador-cfw300', 'Variador CFW300'],
    ],
  },
  {
    id: 'bombeo',
    title: 'Bombeo y agua',
    text: 'Arranques suaves y velocidad variable para bombas de cualquier tamaño.',
    photo: '/img/aplicaciones/bombeo.webp',
    alt: 'Tubería y bombas de una planta de agua',
    lines: [
      ['w22-iec-carcasa-hierro', 'Motor W22 IEC · carcasa hierro'],
      ['variador-cfw500', 'Variador CFW500'],
      ['arrancador-suave-ssw07', 'Arrancador suave SSW07'],
    ],
  },
  {
    id: 'tableros',
    title: 'Tableros y control',
    text: 'Contactores, relés y guardamotores para armar tableros que no fallan.',
    photo: '/img/fotos/tablero-manos.webp',
    alt: 'Manos de un técnico trabajando en un tablero eléctrico',
    lines: [
      ['contactores', 'Contactores'],
      ['reles-termicos', 'Relés térmicos'],
      ['guardamotores', 'Guardamotores'],
    ],
  },
  {
    id: 'solar',
    title: 'Protección y conexión',
    text: 'Interruptores, borneras y accesorios para instalaciones nuevas y ampliaciones.',
    photo: '/img/aplicaciones/solar.webp',
    alt: 'Paneles solares al atardecer',
    lines: [
      ['minibreakers', 'Minibreakers'],
      ['totalizadores', 'Totalizadores'],
      ['borneras-y-accesorios', 'Borneras y accesorios'],
    ],
  },
  {
    id: 'bodegas',
    title: 'Bodegas e iluminación',
    text: 'Cable, luz y aire para naves, bodegas y talleres.',
    photo: '/img/fotos/bodega-cajas.webp',
    alt: 'Pasillo de una bodega con cajas en estanterías',
    lines: [
      ['start-highbay-led', 'START Highbay LED'],
      ['thhn-thwn-2-centelsa', 'Cable THHN / THWN-2'],
      ['extractores-airmax-gl-y-ttt500', 'Extractores AirMax'],
    ],
  },
]

export default function Solutions() {
  const [open, setOpen] = useState(0)

  return (
    <section className="rd-sec bg-white hm-sol" aria-labelledby="h-soluciones">
      <div className="wrap">
        <div className="hm-head">
          <div>
            <SectionLabel n="06">Aplicaciones</SectionLabel>
            <h2 id="h-soluciones" className="rd-h2">Soluciones <Hl>por aplicación</Hl></h2>
          </div>
        </div>
        <Reveal>
          <ul className="sol">
            {solutions.map((s, i) => {
              const active = open === i
              return (
                <li key={s.id} className={`sol__panel ${active ? 'is-open' : ''}`} onMouseEnter={() => setOpen(i)}>
                  <img src={s.photo} alt={s.alt} width="900" height="1200" loading="lazy" />
                  <button type="button" className="sol__head" aria-expanded={active} aria-controls={`sol-${s.id}`} onClick={() => setOpen(i)} onFocus={() => setOpen(i)}>
                    <span className="sol__n" aria-hidden="true">{String(i + 1).padStart(2, '0')}</span>
                    <span className="sol__title">{s.title}</span>
                  </button>
                  <div className="sol__body" id={`sol-${s.id}`} hidden={!active}>
                    <p>{s.text}</p>
                    <ul>
                      {s.lines.map(([slug, name]) => (
                        <li key={slug}><Link to={`/productos/${slug}`}>{name}<ArrowRight size={16} aria-hidden="true" /></Link></li>
                      ))}
                    </ul>
                  </div>
                </li>
              )
            })}
          </ul>
        </Reveal>
      </div>
    </section>
  )
}
