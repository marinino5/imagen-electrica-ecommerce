import { useEffect, useRef, useState } from 'react'

const reducedMotion = () => typeof window !== 'undefined' && window.matchMedia('(prefers-reduced-motion: reduce)').matches

/**
 * Avisa una sola vez cuando el elemento debe mostrarse. El revelado NO depende de eventos de scroll:
 *  1. Al montar, todo lo que esté dentro de la pantalla, un poco por debajo del borde o por ENCIMA (ya quedó atrás tras
 *     un salto: botón atrás, ancla, recarga a mitad de página, scrollTo) se muestra de inmediato.
 *  2. IntersectionObserver con rootMargin generoso (revela 12 % antes de que el elemento llegue al borde).
 *  3. Respaldo: a los 1,5 s, y luego cada 700 ms, se fuerza a visible lo que esté en pantalla; también al volver a la
 *     página desde el historial (pageshow/popstate) y al cambiar el hash.
 */
export function useInView({ threshold = 0, margin = '0px 0px 12% 0px' } = {}) {
  const ref = useRef(null)
  const [seen, setSeen] = useState(false)

  useEffect(() => {
    const el = ref.current
    if (!el || seen) return
    if (reducedMotion() || !('IntersectionObserver' in window)) {
      setSeen(true)
      return
    }
    // En pantalla, justo debajo del borde o por encima: ya no hay nada que animar al entrar
    const due = () => el.getBoundingClientRect().top < window.innerHeight * 1.08
    if (due()) {
      setSeen(true)
      return
    }
    const check = () => due() && setSeen(true)
    const io = new IntersectionObserver((entries) => entries.some((e) => e.isIntersecting) && setSeen(true), { rootMargin: margin, threshold })
    io.observe(el)
    const first = setTimeout(check, 1500)
    const poll = setInterval(check, 700)
    window.addEventListener('pageshow', check)
    window.addEventListener('popstate', check)
    window.addEventListener('hashchange', check)
    return () => {
      io.disconnect()
      clearTimeout(first)
      clearInterval(poll)
      window.removeEventListener('pageshow', check)
      window.removeEventListener('popstate', check)
      window.removeEventListener('hashchange', check)
    }
  }, [seen, threshold, margin])

  return [ref, seen]
}

export { reducedMotion }
