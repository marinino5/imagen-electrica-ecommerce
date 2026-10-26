import { Camera } from 'lucide-react'
import '../../styles/kit.css'

// Marcador de una foto que aún no existe. Se reemplaza por la imagen real sin tocar el diseño de alrededor.
// ratio: proporción CSS ("4 / 3"). fill: ocupa todo el alto de su contenedor (ej. una pieza del mosaico).
export default function ImageSlot({ label, ratio = '4 / 3', fill = false, className = '' }) {
  return (
    <div
      className={`image-slot ${fill ? 'image-slot--fill' : ''} ${className}`}
      style={fill ? undefined : { aspectRatio: ratio }}
      role="img"
      aria-label={`Espacio para: ${label}`}
    >
      <span className="image-slot__icon"><Camera size={26} aria-hidden="true" /></span>
      <span className="image-slot__text">Espacio para: {label}</span>
    </div>
  )
}
