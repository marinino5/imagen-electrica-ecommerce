import { useRef, useState } from 'react'
import { Search } from 'lucide-react'
import '../../styles/catalog.css'

// Productos que flotan en el banner (fotos oficiales) con movimiento lento y continuo + parallax con el mouse
const FLOATERS = [
  { src: '/img/productos/motores/weg-w22-nema-premium.webp', alt: 'Motor WEG W22', size: 220, x: '56%', y: '10%', depth: 28, delay: 0 },
  { src: '/img/productos/control/weg-cfw500.webp', alt: 'Variador WEG CFW500', size: 170, x: '72%', y: '52%', depth: 44, delay: -2 },
  { src: '/img/productos/control/weg-contactores.webp', alt: 'Contactor WEG', size: 140, x: '49%', y: '58%', depth: 36, delay: -4 },
  { src: '/img/productos/reductores/weg-cestari-wcg20.webp', alt: 'Motorreductor WEG Cestari', size: 190, x: '77%', y: '8%', depth: 52, delay: -1 },
  { src: '/img/productos/medicion/fluke-multimetro-117.webp', alt: 'Multímetro Fluke 117', size: 130, x: '90%', y: '34%', depth: 24, delay: -3 },
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
          <span key={f.src} className="cban__fl" style={{ left: f.x, top: f.y, width: f.size, height: f.size, '--depth': f.depth, '--delay': `${f.delay}s` }}>
            <span className="cban__bob"><img src={f.src} alt="" width={f.size} height={f.size} loading="lazy" /></span>
          </span>
        ))}
      </div>
      <div className="wrap cban__inner">
        <p className="cban__tag">Catálogo industrial</p>
        <h1 id="cat-banner">Todo para mover tu proyecto <strong>en un solo lugar</strong></h1>
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
