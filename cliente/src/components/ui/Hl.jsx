import { useInView } from './useInView'

// Palabra clave en naranja con el subrayado que se dibuja de izquierda a derecha al entrar en pantalla
export default function Hl({ children }) {
  const [ref, seen] = useInView({ threshold: 0.6, margin: '0px 0px -5% 0px' })
  return (
    <span ref={ref} className={`hl ${seen ? 'is-in' : ''}`}>
      {children}
    </span>
  )
}
