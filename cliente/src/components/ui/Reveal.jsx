import { useInView } from './useInView'

/**
 * Aparece al entrar en pantalla (450–600 ms). `i` escalona 70 ms por posición; `side` ('l' | 'r') hace que
 * entre deslizándose desde ese lado 60 px; `mask` abre la foto con un clip-path de abajo hacia arriba.
 * `delay` (segundos) suma un retraso fijo, por ejemplo 0.12 para el texto que acompaña a una imagen.
 * `always`: se anima aunque ya esté en pantalla al cargar (portadas cuya entrada es parte del diseño).
 */
export default function Reveal({ children, delay = 0, i = 0, side, mask = false, always = false, className = '', as: Tag = 'div', style, ...props }) {
  const [ref, seen, instant] = useInView({ always })
  const classes = [mask ? 'mask' : 'rv', side === 'l' && 'rv--l', side === 'r' && 'rv--r', seen && 'is-in', instant && 'rv--now', className].filter(Boolean).join(' ')
  return (
    <Tag ref={ref} className={classes} style={{ '--d': `${Math.round(delay * 1000 + i * 70)}ms`, ...style }} {...props}>
      {children}
    </Tag>
  )
}
