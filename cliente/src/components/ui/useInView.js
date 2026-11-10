import { useEffect, useRef, useState } from 'react'

export const reducedMotion = () => typeof window !== 'undefined' && window.matchMedia('(prefers-reduced-motion: reduce)').matches

/*
 * Gestor único de revelado. Antes cada elemento tenía su propio IntersectionObserver y, tras un salto de scroll
 * (scrollTo, ancla, botón atrás, recarga a mitad de página), dependía de que el observador reaccionara a tiempo:
 * a veces la sección quedaba en blanco hasta el respaldo de 1,5 s.
 *
 * Ahora una sola función `run()` revisa todos los elementos pendientes y se ejecuta en CADA señal que pueda mover la
 * pantalla: scroll, resize, scrollend, load, pageshow, popstate, hashchange, visibilitychange, y además cada 400 ms
 * mientras quede algo pendiente (cubre cambios de altura sin scroll: imágenes, fuentes, páginas lazy).
 *  - Lo que ya está en pantalla o por ENCIMA (quedó atrás tras un salto) se muestra al instante, sin animación.
 *  - Lo que entra por abajo con scroll normal se anima.
 *  - Respaldo a 1,5 s: todo lo que esté en pantalla o por encima se fuerza visible.
 */
const pending = new Set()
let lastY = 0
let raf = 0
let timer = 0
let bound = false

const vh = () => window.innerHeight || document.documentElement.clientHeight

// ¿Ya le toca mostrarse? `m` = fracción de la altura que se adelanta; `t` = fracción propia que debe verse
function isDue(e) {
  const r = e.el.getBoundingClientRect()
  const view = vh()
  return r.top < view * (1 - e.m) - Math.min(r.height, view * 0.6) * e.t
}

function run(force = false) {
  raf = 0
  const y = window.scrollY
  const jumped = Math.abs(y - lastY) > vh() * 0.6
  lastY = y
  pending.forEach((e) => {
    if (!isDue(e)) {
      // Tras un salto (o con el respaldo) todo lo que toque la pantalla se muestra, también la franja del borde inferior
      if ((force || jumped) && e.el.getBoundingClientRect().top < vh()) {
        pending.delete(e)
        e.fire(true)
      }
      return
    }
    pending.delete(e)
    const above = e.el.getBoundingClientRect().bottom < 0
    e.fire(jumped || above || force)
  })
  if (!pending.size) unbind()
}

// Red de seguridad tras cualquier desplazamiento: cuando el scroll se detiene 180 ms, todo lo que toque la pantalla
// (o haya quedado por encima) se muestra, aunque ningún otro cálculo lo haya alcanzado. Así un salto de scroll
// (scrollTo, ancla, botón atrás) nunca deja una sección en blanco.
let settleTimer = 0
const settle = () => {
  settleTimer = 0
  if (pending.size) run(true)
}
const schedule = () => {
  if (!raf) raf = requestAnimationFrame(() => run())
  clearTimeout(settleTimer)
  settleTimer = window.setTimeout(settle, 180)
}
const forceNow = () => run(true)

function bind() {
  if (bound) return
  bound = true
  lastY = window.scrollY
  window.addEventListener('scroll', schedule, { passive: true })
  window.addEventListener('scrollend', schedule)
  window.addEventListener('resize', schedule)
  window.addEventListener('load', schedule)
  window.addEventListener('pageshow', schedule)
  window.addEventListener('popstate', schedule)
  window.addEventListener('hashchange', schedule)
  document.addEventListener('visibilitychange', schedule)
  timer = window.setInterval(() => run(), 400)
}

function unbind() {
  if (!bound) return
  bound = false
  window.removeEventListener('scroll', schedule)
  window.removeEventListener('scrollend', schedule)
  window.removeEventListener('resize', schedule)
  window.removeEventListener('load', schedule)
  window.removeEventListener('pageshow', schedule)
  window.removeEventListener('popstate', schedule)
  window.removeEventListener('hashchange', schedule)
  document.removeEventListener('visibilitychange', schedule)
  clearInterval(timer)
  clearTimeout(settleTimer)
  settleTimer = 0
}

/** Parsea el margen inferior de rootMargin ('0px 0px -8% 0px' → 0.08) para conservar la firma anterior. */
const bottomFraction = (margin) => {
  const bottom = String(margin).trim().split(/\s+/)[2]
  const n = parseFloat(bottom)
  return bottom && bottom.endsWith('%') && n < 0 ? -n / 100 : 0
}

/**
 * Avisa una sola vez cuando el elemento debe mostrarse. Devuelve [ref, seen, instant].
 * `instant` = se muestra sin animación (ya estaba en pantalla, quedó atrás tras un salto o se forzó por respaldo).
 * `always`: la animación de entrada ES el contenido (portadas): se anima aunque ya esté en pantalla, pero solo si la
 * página está arriba del todo; tras un salto o recarga a mitad de página aparece ya.
 */
export function useInView({ threshold = 0.15, margin = '0px 0px -8% 0px', always = false } = {}) {
  const ref = useRef(null)
  const [seen, setSeen] = useState(false)
  const [instant, setInstant] = useState(false)

  useEffect(() => {
    const el = ref.current
    if (!el || seen) return
    if (reducedMotion() || typeof requestAnimationFrame === 'undefined') {
      setInstant(true)
      setSeen(true)
      return
    }
    const entry = {
      el,
      m: bottomFraction(margin),
      t: Math.min(threshold, 0.6),
      fire: (inst) => {
        if (inst) setInstant(true)
        setSeen(true)
      },
    }
    if (isDue(entry)) {
      // Portada con entrada animada y página arriba del todo: se anima; en cualquier otro caso aparece ya
      if (always && window.scrollY < 40 && !window.__ieRestoring) {
        const t = setTimeout(() => entry.fire(false), 80)
        return () => clearTimeout(t)
      }
      entry.fire(true)
      return
    }
    pending.add(entry)
    bind()
    const backup = setTimeout(forceNow, 1500)
    return () => {
      clearTimeout(backup)
      pending.delete(entry)
      if (!pending.size) unbind()
    }
  }, [seen, threshold, margin, always])

  return [ref, seen, instant]
}
