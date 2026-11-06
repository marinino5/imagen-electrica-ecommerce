import { useEffect, useRef } from 'react'

// Aparece al hacer scroll: translateY 24 px y opacidad, con IntersectionObserver. Solo anima lo que
// empieza fuera de la pantalla y se queda quieto con prefers-reduced-motion.
export default function Reveal({ children, delay = 0, className = '', as: Tag = 'div', ...props }) {
  const ref = useRef(null)

  useEffect(() => {
    const el = ref.current
    if (!el || !('IntersectionObserver' in window)) return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    if (el.getBoundingClientRect().top <= window.innerHeight) return
    el.classList.add('pre')
    const io = new IntersectionObserver(
      (entries) => {
        if (entries.some((e) => e.isIntersecting)) {
          el.classList.remove('pre')
          io.disconnect()
        }
      },
      { rootMargin: '0px 0px -40px 0px' },
    )
    io.observe(el)
    return () => io.disconnect()
  }, [])

  return (
    <Tag ref={ref} className={`reveal ${className}`} style={delay ? { transitionDelay: `${delay}s` } : undefined} {...props}>
      {children}
    </Tag>
  )
}
