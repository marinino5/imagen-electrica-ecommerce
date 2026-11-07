import { useInView } from './useInView'

// Arco naranja de la marca como trazo fino que se dibuja al entrar en pantalla. pos: tl | tr | bl | br
export default function Arc({ pos = 'tl', size, className = '' }) {
  const [ref, seen] = useInView({ threshold: 0, margin: '0px 0px -5% 0px' })
  return (
    <svg
      ref={ref}
      className={`arc arc--${pos} ${seen ? 'is-in' : ''} ${className}`}
      style={size ? { width: size } : undefined}
      viewBox="0 0 200 200"
      aria-hidden="true"
      focusable="false"
    >
      <path pathLength="1" d="M0 175 A175 175 0 0 1 175 0" />
      <path pathLength="1" d="M0 140 A140 140 0 0 1 140 0" />
    </svg>
  )
}
