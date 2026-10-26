import { useRef } from 'react'
import { motion, useReducedMotion, useScroll, useTransform } from 'framer-motion'

// "La corriente": línea naranja que se dibuja con el scroll. Con prefers-reduced-motion aparece completa.
export default function Current({ d, viewBox, className = '', offset = ['start 85%', 'end 35%'], width = 3 }) {
  const ref = useRef(null)
  const reduced = useReducedMotion()
  const { scrollYProgress } = useScroll({ target: ref, offset })
  const drawn = useTransform(scrollYProgress, [0, 1], [0, 1], { clamp: true })

  return (
    <svg ref={ref} className={`current ${className}`} viewBox={viewBox} preserveAspectRatio="none" aria-hidden="true" fill="none">
      <motion.path
        d={d}
        stroke="var(--accent)"
        strokeWidth={width}
        strokeLinecap="round"
        vectorEffect="non-scaling-stroke"
        style={{ pathLength: reduced ? 1 : drawn }}
      />
    </svg>
  )
}
