import { useRef } from 'react'
import { motion, useAnimationFrame, useMotionValue, useReducedMotion, useScroll, useSpring, useTransform, useVelocity } from 'framer-motion'
import { Zap } from 'lucide-react'

const WORDS = ['Motores', 'Variadores', 'Cables', 'Iluminación', 'Ventilación', 'Automatización']

// Marquesina gigante en contorno: avanza sola y se acelera con la velocidad del scroll.
// Con prefers-reduced-motion queda quieta y se lee como una lista envuelta.
export default function KineticStrip() {
  const reduced = useReducedMotion()
  const baseX = useMotionValue(0)
  const { scrollY } = useScroll()
  const smooth = useSpring(useVelocity(scrollY), { damping: 50, stiffness: 400 })
  const boost = useTransform(smooth, [-2000, 0, 2000], [-6, 0, 6], { clamp: false })
  const direction = useRef(-1)

  useAnimationFrame((_, delta) => {
    if (reduced) return
    // La pista tiene dos copias: -50 % es una vuelta completa
    let move = direction.current * 3.2 * (delta / 1000)
    const b = boost.get()
    if (b !== 0) {
      direction.current = b < 0 ? 1 : -1
      move += direction.current * Math.abs(b) * (delta / 1000) * 2.4
    }
    let next = baseX.get() + move
    if (next <= -50) next += 50
    if (next > 0) next -= 50
    baseX.set(next)
  })
  const x = useTransform(baseX, (v) => `${v}%`)

  const group = (copy) => (
    <ul className="kinetic__group" aria-hidden={copy === 1 ? 'true' : undefined}>
      {WORDS.map((word) => (
        <li key={word}>
          <span className="kinetic__word">{word}</span>
          <Zap className="kinetic__bolt" size={56} strokeWidth={1.5} aria-hidden="true" />
        </li>
      ))}
    </ul>
  )

  return (
    <section className={`kinetic ${reduced ? 'is-still' : ''}`} aria-label="Líneas de producto: motores, variadores, cables, iluminación, ventilación y automatización">
      <motion.div className="kinetic__track" style={{ x: reduced ? 0 : x }}>
        {group(0)}
        {!reduced && group(1)}
      </motion.div>
    </section>
  )
}
