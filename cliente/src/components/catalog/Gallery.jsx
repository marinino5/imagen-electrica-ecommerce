import { useState } from 'react'
import LineVisual from './LineVisual'
import '../../styles/product-detail.css'

// Galería de la ficha: imagen oficial grande con zoom al pasar el mouse, miniaturas y una foto de aplicación (el equipo en uso)
export default function Gallery({ line, appPhoto }) {
  const slides = [
    line.image_url && { src: line.image_url, alt: line.name, product: true },
    line.image_alt_url && { src: line.image_alt_url, alt: `${line.name}, otra vista`, product: true },
    appPhoto && { src: appPhoto.src, alt: appPhoto.alt, product: false },
  ].filter(Boolean)
  const [i, setI] = useState(0)
  const [zoom, setZoom] = useState(null)
  const cur = slides[i]

  const move = (e) => {
    const r = e.currentTarget.getBoundingClientRect()
    setZoom({ x: ((e.clientX - r.left) / r.width) * 100, y: ((e.clientY - r.top) / r.height) * 100 })
  }

  return (
    <div className="gal">
      <div className={`gal__main ${cur?.product ? '' : 'is-photo'}`} onMouseMove={cur ? move : undefined} onMouseLeave={() => setZoom(null)}>
        {cur ? (
          <img
            src={cur.src}
            alt={cur.alt}
            width="900"
            height="900"
            className={cur.product ? 'prod-img' : ''}
            style={zoom ? { transform: 'scale(1.9)', transformOrigin: `${zoom.x}% ${zoom.y}%` } : undefined}
            fetchPriority="high"
          />
        ) : (
          <LineVisual line={line} eager />
        )}
      </div>
      {slides.length > 1 && (
        <ul className="gal__thumbs" aria-label="Imágenes de la ficha">
          {slides.map((s, n) => (
            <li key={s.src}>
              <button type="button" className={n === i ? 'on' : ''} aria-label={`Ver imagen ${n + 1}: ${s.alt}`} aria-pressed={n === i} onClick={() => setI(n)}>
                <img className={s.product ? 'prod-img' : ''} src={s.src} alt="" width="84" height="84" loading="lazy" />
              </button>
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}
