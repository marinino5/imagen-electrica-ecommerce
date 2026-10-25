import { Zap } from 'lucide-react'
import Reveal from './Reveal'

// Patrón común de todas las secciones: etiqueta con rayo, título con una palabra de acento y subtítulo corto.
// El acento va dentro de children con <em>. `actions` queda a la derecha del título.
export default function SectionHeading({ id, eyebrow, children, lead, actions, className = '' }) {
  return (
    <div className={`section-head ${className}`}>
      <Reveal>
        {eyebrow && (
          <p className="eyebrow">
            <Zap size={14} aria-hidden="true" />
            {eyebrow}
          </p>
        )}
        <h2 id={id} className="section-title">{children}</h2>
        {lead && <p className="section-lead">{lead}</p>}
      </Reveal>
      {actions}
    </div>
  )
}
