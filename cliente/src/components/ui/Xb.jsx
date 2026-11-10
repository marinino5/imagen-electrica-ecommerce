import { useInView } from './useInView'

/**
 * «Bloque extendido»: rectángulo violeta que nace en el borde de la pantalla y llega hasta el 38 % de la foto,
 * con la foto encima (sombra y esquinas de 28 px) y un arco naranja fino abrazando una esquina.
 * `side`: 'l' la foto va a la izquierda y el bloque nace del borde izquierdo; 'r' al revés.
 * `arch`: foto en arco alto (radio superior completo). Las animaciones viven en ie.css y se disparan con `.is-in`:
 * bloque (scaleX), foto (desde su lado), texto (desde el lado contrario) y arco (stroke-dashoffset).
 */
export default function Xb({ side = 'l', arch = false, ratio, col, photo, children, className = '', as: Tag = 'div', ...props }) {
  const [ref, seen, instant] = useInView({ always: true })
  const classes = ['xb', side === 'r' && 'xb--r', arch && 'xb--arch', seen && 'is-in', instant && 'xb--now', className].filter(Boolean).join(' ')
  return (
    <Tag ref={ref} className={classes} {...props}>
      <div className="wrap xb__grid" style={col ? { '--col': col } : undefined}>
        <div className="xb__media">
          <span className="xb__block" aria-hidden="true" />
          <div className="xb__photo" style={ratio ? { '--ratio': ratio } : undefined}>{photo}</div>
          <svg className="xb__arc" viewBox="0 0 180 180" aria-hidden="true" focusable="false">
            <path pathLength="1" d="M0 0 A180 180 0 0 1 180 180" />
          </svg>
        </div>
        <div className="xb__text">{children}</div>
      </div>
    </Tag>
  )
}
