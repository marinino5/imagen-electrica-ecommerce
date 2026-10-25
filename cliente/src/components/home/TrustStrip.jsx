import { useEffect, useRef, useState } from 'react'
import { animate, useInView, useReducedMotion } from 'framer-motion'
import { Headset, Truck } from 'lucide-react'
import { site } from '../../config/site'
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
    <span ref={ref} className="stats__value">
      {prefix}{new Intl.NumberFormat('es-CO').format(value)}{suffix}
    </span>
  )
}

// Tarjeta blanca que se monta sobre el borde inferior del hero
export default function TrustStrip() {
  return (
    <section className="stats" aria-label="Por qué elegirnos">
      <div className="container">
        <ul className="stats__card">
          <li>
            <Counter to={site.years} prefix="+" />
            <span>años de experiencia</span>
          </li>
          <li>
            <Counter to={1000} prefix="+" />
            <span>referencias en catálogo</span>
          </li>
          <li>
            <Truck size={38} aria-hidden="true" />
            <span>Envíos a todo el país</span>
          </li>
          <li>
            <Headset size={38} aria-hidden="true" />
            <span>Asesoría técnica</span>
          </li>
        </ul>
      </div>
    </section>
  )
}
