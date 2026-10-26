import { useEffect, useRef, useState } from 'react'
import { animate, useInView, useReducedMotion } from 'framer-motion'

// Número que sube de 0 al valor al entrar en pantalla (con prefers-reduced-motion aparece completo)
export default function Counter({ to, prefix = '', suffix = '', className = '' }) {
  const ref = useRef(null)
  const inView = useInView(ref, { once: true, margin: '0px 0px -60px 0px' })
  const reduced = useReducedMotion()
  const [value, setValue] = useState(reduced ? to : 0)

  useEffect(() => {
    if (!inView || reduced) return
    const controls = animate(0, to, { duration: 1.6, ease: 'easeOut', onUpdate: (v) => setValue(Math.round(v)) })
    return () => controls.stop()
  }, [inView, reduced, to])

  return (
    <span ref={ref} className={className}>
      {prefix}{new Intl.NumberFormat('es-CO').format(value)}{suffix}
    </span>
  )
}
