import { useRef, useState } from 'react'
import { Search } from 'lucide-react'
import '../../styles/catalog.css'
import '../../styles/catalog2.css'

// Productos recortados que flotan a la derecha con profundidad: distinto tamaño, desenfoque y sombra según la distancia,
// movimiento lento y parallax leve con el mouse (`depth` = cuántos px se mueve). Son las imágenes oficiales del catálogo.
const FLOATERS = [
  { src: '/img/catalogo/weg-w22-nema-premium.webp', size: 300, x: '60%', y: '4%', depth: 30, dur: 11, delay: 0, z: 3 },
  { src: '/img/catalogo/weg-cfw500.webp', size: 230, x: '79%', y: '44%', depth: 46, dur: 9, delay: -2, z: 4 },
  { src: '/img/catalogo/weg-contactores.webp', size: 170, x: '55%', y: '52%', depth: 38, dur: 10, delay: -4, z: 2 },
  { src: '/img/catalogo/weg-cestari-wcg20.webp', size: 210, x: '81%', y: '2%', depth: 54, dur: 12, delay: -1, z: 3 },
  { src: '/img/catalogo/fluke-multimetro-117.webp', size: 150, x: '93%', y: '30%', depth: 20, dur: 13, delay: -3, z: 1, far: true },
  { src: '/img/catalogo/weg-ssw07.webp', size: 130, x: '48%', y: '14%', depth: 16, dur: 14, delay: -5, z: 1, far: true },
]

export default function CatalogBanner({ finderRef, initial, onSearch, quick }) {
  const [term, setTerm] = useState(initial)
  const box = useRef(null)

  const onMove = (e) => {
    const el = box.current
    if (!el || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    const r = el.getBoundingClientRect()
    el.style.setProperty('--mx', ((e.clientX - r.left) / r.width - 0.5).toFixed(3))
    el.style.setProperty('--my', ((e.clientY - r.top) / r.height - 0.5).toFixed(3))
  }

  return (
    <section className="cban on-dark" aria-labelledby="cat-banner" ref={box} onMouseMove={onMove}>
      <div className="cban__floaters" aria-hidden="true">
        {FLOATERS.map((f) => (
          <span key={f.src} className={`cban__fl ${f.far ? 'is-far' : ''}`} style={{ left: f.x, top: f.y, width: f.size, height: f.size, zIndex: f.z, '--depth': f.depth, '--delay': `${f.delay}s`, '--dur': `${f.dur}s` }}>
            <span className="cban__bob"><img src={f.src} alt="" width={f.size} height={f.size} loading="lazy" /></span>
          </span>
        ))}
      </div>
      <div className="wrap cban__inner">
        <p className="cban__tag">Catálogo industrial</p>
        <h1 id="cat-banner">Todo para mover tu proyecto <strong>en un solo lugar</strong></h1>
        <p className="cban__lead">Motores, variadores, control y protección de las marcas que distribuimos, con la referencia exacta y el precio a la vista.</p>
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
    </section>
  )
}
