import { useInView } from './useInView'

/**
 * Entrada propia de Contacto: fundido con escala 0,98 → 1 (600 ms). Si ya está en pantalla al cargar se anima igual
 * (`always`); con movimiento reducido aparece sin animar. Las reglas viven en ie-contacto.css.
 */
export default function Pop({ children, delay = 0, className = '', as: Tag = 'div', ...props }) {
  const [ref, seen, instant] = useInView({ always: true })
  return (
    <Tag ref={ref} className={`pop ${seen ? 'is-in' : ''} ${instant ? 'pop--now' : ''} ${className}`} style={{ '--d': `${Math.round(delay * 1000)}ms` }} {...props}>
      {children}
    </Tag>
  )
}
