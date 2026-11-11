import { useEffect, useRef, useState } from 'react'
import { useReducedMotion } from 'framer-motion'

/**
 * Video propio de la tienda (sin audio, en bucle): solo se reproduce mientras está en pantalla y con movimiento reducido
 * se queda en el póster. `name` es el archivo de public/media sin extensión (versión móvil «-m», póster «-poster.jpg»).
 */
export default function OwnVideo({ name, label, className = '', wide = false }) {
  // Recuadro apaisado: la versión de escritorio (16:9); en los demás, la vertical «-m»
  const v = wide ? '' : '-m'
  const ref = useRef(null)
  const reduced = useReducedMotion()
  const [near, setNear] = useState(false)

  useEffect(() => {
    const el = ref.current
    if (!el || reduced || typeof IntersectionObserver === 'undefined') return undefined
    const io = new IntersectionObserver(([entry]) => {
      setNear((was) => was || entry.isIntersecting)
      if (entry.isIntersecting) el.play().catch(() => {})
      else el.pause()
    }, { rootMargin: '200px 0px', threshold: 0.25 })
    io.observe(el)
    return () => io.disconnect()
  }, [reduced])

  if (reduced) return <img className={className} src={`/media/${name}-poster.jpg`} alt={label} loading="lazy" />

  return (
    <video ref={ref} className={className} muted loop playsInline preload="none" poster={`/media/${name}-poster.jpg`} aria-label={label}>
      {near && <source src={`/media/${name}${v}.webm`} type="video/webm" />}
      {near && <source src={`/media/${name}${v}.mp4`} type="video/mp4" />}
    </video>
  )
}
