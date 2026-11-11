import { useRef } from 'react'
import { useScrollFrame } from '../../hooks/useScrollFrame'
import { reducedMotion } from './useInView'

/**
 * Decoración de sección: un círculo y un semicírculo de línea fina (1,5 px, sin relleno; ya no son cuadros de color) detrás de
 * fotos y tarjetas, con parallax leve. El segundo elemento es el semicírculo. Máximo 2 por sección. Va como hijo directo de una sección `.bgt` (que le da position y aislamiento): el cuadro queda
 * detrás del contenido y no recibe clics. Cada item: { w, h, tone: 'lila' | 'orange' | 'violet', rate, ...posición }
 * donde la posición es cualquier mezcla de top/bottom/left/right en CSS ({ top: '12%', right: '6%' }).
 * `rate` es cuánto se desplazan por cada px de scroll respecto del centro de la pantalla (negativo = sube más lento).
 */
export default function Squares({ items }) {
  const box = useRef(null)
  const still = reducedMotion()

  useScrollFrame(() => {
    const el = box.current
    if (!el) return
    const host = el.parentElement.getBoundingClientRect()
    if (host.bottom < -200 || host.top > window.innerHeight + 200) return
    const center = host.top + host.height / 2 - window.innerHeight / 2
    el.style.setProperty('--sq-c', center.toFixed(1))
  }, !still)

  return (
    <div ref={box} className="sqs" aria-hidden="true">
      {items.slice(0, 2).map(({ w, h = w, tone = 'lila', rate = 0.1, ...pos }, i) => (
        <span key={i} className={`sq sq--${tone}`} style={{ width: w, height: h, '--rate': rate, ...pos }} />
      ))}
    </div>
  )
}
