import { Link } from 'react-router-dom'
import { ArrowRight } from 'lucide-react'

// Botón píldora con el icono en un círculo interno que se desplaza al pasar el mouse.
// tone: 'o' naranja (texto ink) · 'd' ink (texto blanco) · 'l' blanco con contorno lavanda
// as: 'button' (por defecto), 'a' (enlace externo) o Link (ruta interna, con `to`)
export default function Pill({ tone = 'o', icon: Icon = ArrowRight, as = 'button', size, className = '', children, ...props }) {
  const classes = `pill pill--${tone} ${size ? `pill--${size}` : ''} ${className}`
  const content = (
    <>
      <span className="pill__label">{children}</span>
      <span className="pill__ic" aria-hidden="true"><Icon size={size === 'sm' ? 16 : 18} /></span>
    </>
  )
  if (as === 'link') return <Link className={classes} {...props}>{content}</Link>
  if (as === 'a') return <a className={classes} {...props}>{content}</a>
  return <button type="button" className={classes} {...props}>{content}</button>
}
