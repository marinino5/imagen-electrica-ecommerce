import Eyebrow from './Eyebrow'

// Portada editorial dividida en dos: texto grande a la izquierda y una composición visual a la derecha
// (placas apiladas, foto en bisel o un elemento interactivo). En celular se apila.
// lines: ['PRIMERA LÍNEA', { text: 'SEGUNDA', accent: true }]
export default function Cover({ eyebrow, lines, lead, actions, children, className = '', ring = true }) {
  return (
    <section className={`cover wrap ${className}`}>
      {ring && <span className="cover__ring" aria-hidden="true" />}
      <div className="cover__copy">
        {eyebrow && <Eyebrow>{eyebrow}</Eyebrow>}
        <h1 className="h-hero">
          {lines.map((line, i) => {
            const item = typeof line === 'string' ? { text: line } : line
            return item.accent ? <em key={i}>{item.text}</em> : <span key={i}>{item.text}</span>
          })}
        </h1>
        {lead && <p className="cover__lead">{lead}</p>}
        {actions && <div className="cover__actions">{actions}</div>}
      </div>
      {children && <div className="cover__visual">{children}</div>}
    </section>
  )
}
