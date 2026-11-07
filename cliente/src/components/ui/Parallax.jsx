import { useEffect, useRef } from 'react'
import { reducedMotion } from './useInView'

/**
 * Foto grande con parallax suave: la imagen se mueve un 10 % más lento que el scroll.
 * Va dentro de un contenedor `.px` (overflow oculto). Con movimiento reducido se queda quieta.
 */
export default function Parallax({ src, alt = '', rate = 0.1, className = '', style, loading = 'lazy', children, ...rest }) {
  const box = useRef(null)
  const img = useRef(null)

  useEffect(() => {
    const el = box.current
    const media = img.current
    if (!el || !media || reducedMotion()) return
    let raf = 0
    let visible = false
    const update = () => {
      raf = 0
      const r = el.getBoundingClientRect()
      const center = r.top + r.height / 2 - window.innerHeight / 2
      media.style.transform = `translate3d(0, ${(-center * rate).toFixed(1)}px, 0)`
    }
    const onScroll = () => {
      if (visible && !raf) raf = requestAnimationFrame(update)
    }
    const io = new IntersectionObserver(([e]) => {
      visible = e.isIntersecting
      if (visible) update()
    })
    io.observe(el)
    window.addEventListener('scroll', onScroll, { passive: true })
    window.addEventListener('resize', onScroll)
    return () => {
      io.disconnect()
      window.removeEventListener('scroll', onScroll)
      window.removeEventListener('resize', onScroll)
      if (raf) cancelAnimationFrame(raf)
    }
  }, [rate])

  return (
    <div ref={box} className={`px ${className}`} style={style} {...rest}>
      <img ref={img} src={src} alt={alt} loading={loading} decoding="async" />
      {children}
    </div>
  )
}
