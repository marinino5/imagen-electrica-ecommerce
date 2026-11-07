import { useEffect, useState } from 'react'
import { animate } from 'framer-motion'
import { useInView, reducedMotion } from './useInView'

// Número que sube de 0 al valor al entrar en pantalla. Si por cualquier motivo no llega a animar,
// al desmontarse o con movimiento reducido muestra siempre el valor final: nunca se queda en 0.
export default function Counter({ to, prefix = '', suffix = '', className = '' }) {
  const [ref, seen] = useInView({ threshold: 0.3, margin: '0px 0px -5% 0px' })
  const [value, setValue] = useState(reducedMotion() ? to : 0)

  useEffect(() => {
    if (!seen) return
    if (reducedMotion()) {
      setValue(to)
      return
    }
    const controls = animate(0, to, { duration: 1.4, ease: 'easeOut', onUpdate: (v) => setValue(Math.round(v)), onComplete: () => setValue(to) })
    return () => {
      controls.stop()
      setValue(to)
    }
  }, [seen, to])

  return (
    <span ref={ref} className={className}>
      {prefix}
      {new Intl.NumberFormat('es-CO').format(value)}
      {suffix}
    </span>
  )
}
