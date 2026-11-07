import { useEffect, useRef, useState } from 'react'

const reducedMotion = () => typeof window !== 'undefined' && window.matchMedia('(prefers-reduced-motion: reduce)').matches

/**
 * Avisa una sola vez cuando el elemento entra en pantalla. Se dispara antes de que llegue al borde
 * (rootMargin 0 0 -10 % 0, umbral 0,1) y, como red de seguridad, un chequeo cada 700 ms por si el
 * navegador se salta el aviso: así nunca queda un bloque invisible a la vista.
 */
export function useInView({ threshold = 0.1, margin = '0px 0px -10% 0px' } = {}) {
  const ref = useRef(null)
  const [seen, setSeen] = useState(false)

  useEffect(() => {
    const el = ref.current
    if (!el || seen) return
    if (reducedMotion() || !('IntersectionObserver' in window)) {
      setSeen(true)
      return
    }
    const io = new IntersectionObserver(
      (entries) => {
        if (entries.some((e) => e.isIntersecting)) setSeen(true)
      },
      { rootMargin: margin, threshold },
    )
    io.observe(el)
    const timer = setInterval(() => {
      const r = el.getBoundingClientRect()
      if (r.top < window.innerHeight * 0.92 && r.bottom > 0) setSeen(true)
    }, 700)
    return () => {
      io.disconnect()
      clearInterval(timer)
    }
  }, [seen, threshold, margin])

  return [ref, seen]
}

export { reducedMotion }
