import { motion, useReducedMotion, useScroll, useTransform } from 'framer-motion'
import '../../styles/backdrop.css'

// Anillos concéntricos: grueso + finos + punteado. Se dibujan en un SVG centrado en la esquina.
function Rings({ color, thin, className }) {
  return (
    <svg className={`backdrop__rings ${className}`} viewBox="-400 -400 800 800" fill="none" aria-hidden="true">
      <circle r="330" stroke={color} strokeWidth="44" />
      <circle r="260" stroke={thin} strokeWidth="2" />
      <circle r="225" stroke="var(--lavender-soft)" strokeWidth="2" />
      <circle r="190" stroke={color} strokeWidth="2" strokeDasharray="1 14" strokeLinecap="round" />
      <circle r="140" stroke={thin} strokeWidth="1.5" />
    </svg>
  )
}

// Fondo fijo de toda la tienda: degradado --page-bg con arcos de marca en las esquinas.
// Solo decorativo: aria-hidden, no captura clics y el parallax se desactiva con prefers-reduced-motion.
export default function BrandBackdrop() {
  const reduced = useReducedMotion()
  const { scrollY } = useScroll()
  const up = useTransform(scrollY, [0, 4000], [0, -90])
  const down = useTransform(scrollY, [0, 4000], [0, 90])

  return (
    <div className="backdrop" aria-hidden="true">
      <motion.div className="backdrop__corner backdrop__corner--tr" style={{ y: reduced ? 0 : up }}>
        <Rings color="var(--accent)" thin="var(--orange-soft)" className="backdrop__rings--orange" />
        <span className="backdrop__dots backdrop__dots--orange" />
      </motion.div>
      <motion.div className="backdrop__corner backdrop__corner--bl" style={{ y: reduced ? 0 : down }}>
        <Rings color="var(--primary)" thin="var(--orange-soft)" className="backdrop__rings--violet" />
        <span className="backdrop__dots backdrop__dots--lavender" />
      </motion.div>
    </div>
  )
}
