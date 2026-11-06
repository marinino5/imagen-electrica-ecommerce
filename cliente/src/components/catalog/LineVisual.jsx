import { useState } from 'react'
import { familyIcon } from '../../lib/families'
import '../../styles/line-visual.css'

// Imagen de una línea: su foto propia (image_url) o, si falta, un diseño de marca (arcos + icono de la familia).
// Nunca se reutiliza la foto de otra línea. Si la foto no carga, también cae en el diseño de marca.
export default function LineVisual({ line, className = '', width = 900, height = 675, eager = false }) {
  const [failed, setFailed] = useState(false)
  const src = !failed ? line?.image_url : null

  if (src) {
    return (
      <img
        className={`lv-photo ${className}`}
        src={src}
        alt={line.name}
        width={width}
        height={height}
        loading={eager ? 'eager' : 'lazy'}
        fetchPriority={eager ? 'high' : undefined}
        onError={() => setFailed(true)}
      />
    )
  }

  const Icon = familyIcon(line?.family?.slug)
  // Sin foto, cada ficha muestra su dato clave: así dos fichas de la misma familia no se ven iguales
  const label = line?.spec_chips?.[0] ?? line?.brand_label ?? null
  return (
    <span className={`lv-art ${className}`} aria-hidden="true">
      <span className="lv-art__arc lv-art__arc--a" />
      <span className="lv-art__arc lv-art__arc--b" />
      <span className="lv-art__icon"><Icon strokeWidth={1.4} /></span>
      {label && <span className="lv-art__label">{label}</span>}
    </span>
  )
}
