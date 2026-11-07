import { useState } from 'react'
import LineVisual from './LineVisual'
import '../../styles/product-detail.css'

// Galería de la ficha: el producto recortado sobre un pedestal con un arco naranja detrás, lupa de 2× al pasar el mouse
// y miniaturas en píldoras de 64 px. La imagen principal nunca es perezosa (loading="eager", fetchpriority="high") y,
// si falla, cae en el diseño de marca en vez de mostrar solo el texto alternativo.
// Las fotos de ambiente no van aquí: están en la pestaña «Dónde se usa».
export default function Gallery({ line }) {
  const slides = [
    line.image_url && { src: line.image_url, alt: line.name },
    line.image_alt_url && { src: line.image_alt_url, alt: `${line.name}, otra vista` },
  ].filter(Boolean)
  const [i, setI] = useState(0)
  const [zoom, setZoom] = useState(null)
  const [failed, setFailed] = useState({})
  const cur = slides[i] && !failed[slides[i].src] ? slides[i] : null

  const move = (e) => {
    const r = e.currentTarget.getBoundingClientRect()
    setZoom({ x: ((e.clientX - r.left) / r.width) * 100, y: ((e.clientY - r.top) / r.height) * 100 })
  }

  return (
    <div className="gal">
      <div className="gal__main" onMouseMove={cur ? move : undefined} onMouseLeave={() => setZoom(null)}>
        {cur ? (
          <>
            <svg className="gal__arc" viewBox="0 0 400 200" aria-hidden="true" focusable="false">
              <path d="M10 200 A190 190 0 0 1 390 200" />
            </svg>
            <span className="gal__ped" aria-hidden="true" />
            <img
              src={cur.src}
              alt={cur.alt}
              width="900"
              height="900"
              loading="eager"
              decoding="async"
              fetchPriority="high"
              onError={() => setFailed((f) => ({ ...f, [cur.src]: true }))}
              style={zoom ? { transform: 'scale(2)', transformOrigin: `${zoom.x}% ${zoom.y}%` } : undefined}
            />
          </>
        ) : (
          <LineVisual line={line} eager />
        )}
        {cur && <span className="gal__hint" aria-hidden="true">Pasa el mouse para ampliar</span>}
      </div>
      {slides.length > 1 && (
        <ul className="gal__thumbs" aria-label="Imágenes de la ficha">
          {slides.map((s, n) => (
            <li key={s.src}>
              <button type="button" className={n === i ? 'on' : ''} aria-label={`Ver imagen ${n + 1}: ${s.alt}`} aria-pressed={n === i} onClick={() => setI(n)}>
                <img src={s.src} alt="" width="64" height="64" loading="lazy" />
              </button>
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}
