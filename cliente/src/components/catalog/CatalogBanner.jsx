import { useRef, useState } from 'react'
import { Search } from 'lucide-react'
import Pedestal from '../ui/Pedestal'
import Hl from '../ui/Hl'
import Bgfx from '../ui/Bgfx'
import '../../styles/catalog.css'
import '../../styles/catalog2.css'
import '../../styles/ie-catalog.css'

// Vitrina: cinco productos recortados sobre pedestales, en escalera de tres alturas. El motor grande va al frente y al
// centro; detrás, dos medianos; a los lados, dos pequeños. `d` = cuántos px se mueve cada capa con el mouse (máx. 8).
const SHOWCASE = [
  { src: '/img/catalogo/weg-w22-nema-premium.webp', alt: 'Motor eléctrico WEG W22', cls: 'sc-motor', d: 8 },
  { src: '/img/catalogo/weg-cestari-wcg20.webp', alt: 'Motorreductor WEG Cestari', cls: 'sc-reductor', d: 5 },
  { src: '/img/catalogo/weg-cfw500.webp', alt: 'Variador de frecuencia WEG CFW500', cls: 'sc-variador', d: 5 },
  { src: '/img/catalogo/weg-contactores.webp', alt: 'Contactor WEG', cls: 'sc-contactor', d: 3 },
  { src: '/img/catalogo/fluke-multimetro-117.webp', alt: 'Multímetro Fluke 117', cls: 'sc-fluke', d: 3 },
]

export default function CatalogBanner({ finderRef, initial, onSearch, quick }) {
  const [term, setTerm] = useState(initial)
  const stage = useRef(null)

  // Parallax leve: solo con puntero fino y sin movimiento reducido
  const onMove = (e) => {
    const el = stage.current
    if (!el || e.pointerType !== 'mouse' || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    const r = el.getBoundingClientRect()
    el.style.setProperty('--mx', (((e.clientX - r.left) / r.width - 0.5) * 2).toFixed(3))
    el.style.setProperty('--my', (((e.clientY - r.top) / r.height - 0.5) * 2).toFixed(3))
  }

  return (
    <section className="cban bg-lila-fade" aria-labelledby="cat-banner">
      <Bgfx a="plan:r" b="flip" />
      <div className="wrap cban__grid">
        <div className="cban__copy">
          <p className="rd-label">Catálogo industrial</p>
          <h1 id="cat-banner" className="cban__h1">Todo para mover tu proyecto <Hl>en un solo lugar</Hl></h1>
          <p className="cban__lead">Motores, variadores, control y protección con la referencia exacta y el precio a la vista.</p>
          <form className="cban__search" role="search" onSubmit={(e) => { e.preventDefault(); onSearch(term.trim()) }}>
            <Search size={20} aria-hidden="true" />
            <input ref={finderRef} type="search" enterKeyHint="search" autoComplete="off" placeholder="Motor 5 HP 1800, CFW300, contactor 25 A…" aria-label="Buscar en el catálogo" value={term} onChange={(e) => setTerm(e.target.value)} />
            <button type="submit">Buscar</button>
          </form>
          <div className="cban__quick">
            <span>Búsquedas frecuentes</span>
            {quick.map(([label, text]) => <button key={label} type="button" onClick={() => { setTerm(text); onSearch(text) }}>{label}</button>)}
          </div>
        </div>

        <div className="cban__stage" ref={stage} onPointerMove={onMove} aria-hidden="true">
          {SHOWCASE.map((p, i) => (
            <div key={p.src} className={`sc ${p.cls}`} style={{ '--d': p.d, '--i': i }}>
              <div className="sc__in">
                <Pedestal lift src={p.src} alt="" loading={i === 0 ? 'eager' : 'lazy'} />
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
