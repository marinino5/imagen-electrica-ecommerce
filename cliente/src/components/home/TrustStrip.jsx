import { useEffect, useRef, useState } from 'react'
import { animate, useInView, useReducedMotion } from 'framer-motion'
import { Headset, Truck } from 'lucide-react'
import '../../styles/home.css'

function Counter({ to, prefix = '', suffix = '' }) {
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
    <span ref={ref} className="trust__value">
      {prefix}{new Intl.NumberFormat('es-CO').format(value)}{suffix}
    </span>
  )
}

export default function TrustStrip() {
  return (
    <section className="section--surface trust" aria-label="Por qué elegirnos">
      <ul className="container trust__list">
        <li>
          <Counter to={25} suffix="+" />
          <span>años de experiencia</span>
        </li>
        <li>
          <Counter to={1000} prefix="+" />
          <span>referencias en catálogo</span>
        </li>
        <li>
          <Truck size={34} aria-hidden="true" />
          <span>Envíos a todo el país</span>
        </li>
        <li>
          <Headset size={34} aria-hidden="true" />
          <span>Asesoría técnica incluida</span>
        </li>
      </ul>
    </section>
  )
}
