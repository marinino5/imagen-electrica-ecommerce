import { useEffect, useRef } from 'react'
import { motion, useMotionValue, useReducedMotion, useScroll, useTransform } from 'framer-motion'
import { useMediaQuery } from '../../hooks/useMediaQuery'

// Sección fija con desplazamiento horizontal: al bajar con el scroll, el carril de tarjetas pasa de lado.
// En celular, o con prefers-reduced-motion, es un carril deslizable con el dedo (scroll-snap).
export default function HorizontalScroller({ head, children, className = '' }) {
  const desktop = useMediaQuery('(min-width: 900px)')
  const reduced = useReducedMotion()
  const pinned = desktop && !reduced

  const sectionRef = useRef(null)
  const viewportRef = useRef(null)
  const trackRef = useRef(null)
  const dist = useMotionValue(0)
  const { scrollYProgress } = useScroll({ target: sectionRef, offset: ['start start', 'end end'] })
  const x = useTransform([scrollYProgress, dist], ([p, d]) => -p * d)
  const bar = useTransform(scrollYProgress, [0, 1], [0, 1], { clamp: true })

  // Recorrido horizontal = ancho del carril menos lo que cabe en pantalla
  useEffect(() => {
    if (!pinned) return
    const measure = () => {
      const track = trackRef.current
      const viewport = viewportRef.current
      if (!track || !viewport) return
      const d = Math.max(0, track.scrollWidth - viewport.clientWidth)
      dist.set(d)
      if (sectionRef.current) sectionRef.current.style.height = `calc(100svh + ${d}px)`
    }
    measure()
    const observer = new ResizeObserver(measure)
    observer.observe(trackRef.current)
    observer.observe(viewportRef.current)
    return () => observer.disconnect()
  }, [pinned, dist, children])

  if (!pinned) {
    return (
      <div ref={sectionRef} className={`hscroll hscroll--rail ${className}`}>
        {head}
        <div className="hscroll__rail" tabIndex={0} role="region" aria-label="Líneas de producto, desliza para ver más">
          <div className="hscroll__track">{children}</div>
        </div>
      </div>
    )
  }

  return (
    <div ref={sectionRef} className={`hscroll hscroll--pinned ${className}`}>
      <div className="hscroll__sticky">
        {head}
        <div ref={viewportRef} className="hscroll__viewport">
          <motion.div ref={trackRef} className="hscroll__track" style={{ x }}>
            {children}
          </motion.div>
        </div>
        <div className="hscroll__progress" aria-hidden="true">
          <motion.span style={{ scaleX: bar }} />
        </div>
      </div>
    </div>
  )
}
