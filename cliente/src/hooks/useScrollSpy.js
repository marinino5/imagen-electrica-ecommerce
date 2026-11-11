import { useEffect, useState } from 'react'

/**
 * Scrollspy del catálogo: devuelve el slug del grupo (familia) que se está viendo, es decir, el último cuyo borde superior ya
 * pasó la línea de las cabeceras fijas. Devuelve '' antes del primer grupo. Los grupos son elementos con id `fgroup-<slug>`.
 * `top` es la distancia (px) desde el borde de la ventana hasta esa línea; puede ser un número o una función que se vuelve a
 * medir en cada paso (la línea depende del alto real de las barras fijas).
 */
export function useScrollSpy(slugs, top) {
  const [active, setActive] = useState('')
  const key = slugs.join('|')

  useEffect(() => {
    if (!slugs.length) {
      setActive('')
      return undefined
    }
    let frame = 0
    const update = () => {
      frame = 0
      let current = ''
      const line = typeof top === 'function' ? top() : top
      for (const slug of slugs) {
        const el = document.getElementById(`fgroup-${slug}`)
        if (el && el.getBoundingClientRect().top <= line) current = slug
      }
      setActive((prev) => (prev === current ? prev : current))
    }
    const onScroll = () => {
      if (!frame) frame = requestAnimationFrame(update)
    }
    update()
    window.addEventListener('scroll', onScroll, { passive: true })
    window.addEventListener('resize', onScroll)
    return () => {
      cancelAnimationFrame(frame)
      window.removeEventListener('scroll', onScroll)
      window.removeEventListener('resize', onScroll)
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [key]) // `top` puede ser una función nueva en cada render: solo cambian los grupos

  return active
}
