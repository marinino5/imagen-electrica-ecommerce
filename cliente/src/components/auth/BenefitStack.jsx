import { useEffect, useState } from 'react'
import { motion, useReducedMotion } from 'framer-motion'

// Beneficios que rotan en una pila vertical: el activo va al frente y los demás asoman detrás.
// Con prefers-reduced-motion se muestran todos en una lista estática.
export default function BenefitStack({ items }) {
  const reduced = useReducedMotion()
  const [active, setActive] = useState(0)

  useEffect(() => {
    if (reduced) return
    const timer = setInterval(() => setActive((n) => (n + 1) % items.length), 3200)
    return () => clearInterval(timer)
  }, [reduced, items.length])

  if (reduced) {
    return (
      <ul className="benefits benefits--list">
        {items.map(({ icon: Icon, text }) => (
          <li key={text} className="benefit">
            <span className="benefit__icon"><Icon size={20} aria-hidden="true" /></span>
            <span>{text}</span>
          </li>
        ))}
      </ul>
    )
  }

  return (
    <div className="benefits" aria-label="Beneficios de tener cuenta" role="list">
      {items.map(({ icon: Icon, text }, i) => {
        const rel = (i - active + items.length) % items.length // 0 = al frente
        return (
          <motion.div
            key={text}
            role="listitem"
            className="benefit"
            aria-hidden={rel !== 0 ? 'true' : undefined}
            initial={false}
            animate={{
              y: rel * 14,
              scale: 1 - rel * 0.05,
              opacity: rel > 2 ? 0 : 1 - rel * 0.28,
            }}
            style={{ zIndex: items.length - rel }}
            transition={{ duration: 0.6, ease: [0.22, 1, 0.36, 1] }}
          >
            <span className="benefit__icon"><Icon size={20} aria-hidden="true" /></span>
            <span>{text}</span>
          </motion.div>
        )
      })}
    </div>
  )
}
