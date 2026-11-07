import { useEffect, useRef } from 'react'

/**
 * Ejecuta `update` en cada fotograma en que cambie el scroll o el tamaño de la ventana (una vez por rAF) y una vez al montar.
 * Sirve para las animaciones atadas al scroll: `update` lee el progreso y lo escribe en una variable CSS (`--p`), y el CSS
 * hace el resto. Es la técnica de respaldo común a todos los navegadores: donde existen las animaciones CSS ligadas al scroll
 * podrían sustituirla, pero así el resultado es idéntico en Safari, Firefox y Chrome. `enabled=false` no escucha nada
 * (movimiento reducido o pantalla pequeña).
 */
export function useScrollFrame(update, enabled = true, deps = []) {
  const fn = useRef(update)
  fn.current = update

  useEffect(() => {
    if (!enabled) return undefined
    let raf = 0
    const run = () => {
      raf = 0
      fn.current()
    }
    const onChange = () => {
      if (!raf) raf = requestAnimationFrame(run)
    }
    run()
    window.addEventListener('scroll', onChange, { passive: true })
    window.addEventListener('resize', onChange)
    window.addEventListener('load', onChange)
    window.addEventListener('pageshow', onChange)
    return () => {
      if (raf) cancelAnimationFrame(raf)
      window.removeEventListener('scroll', onChange)
      window.removeEventListener('resize', onChange)
      window.removeEventListener('load', onChange)
      window.removeEventListener('pageshow', onChange)
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [enabled, ...deps])
}

export const clamp01 = (n) => Math.min(1, Math.max(0, n))
