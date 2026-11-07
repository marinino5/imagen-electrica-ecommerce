import { useInView } from './useInView'

// Etiqueta de sección: línea corta naranja encima y número ("01 — Portafolio")
export default function SectionLabel({ n, children, className = '' }) {
  const [ref, seen] = useInView()
  return (
    <p ref={ref} className={`rd-label rv-label ${seen ? 'is-in' : ''} ${className}`}>
      <span>
        {n && <b>{n}</b>}
        {n && '— '}
        {children}
      </span>
    </p>
  )
}
