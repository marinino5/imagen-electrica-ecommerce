import Cover from '../components/ui/Cover'
import Pill from '../components/ui/Pill'

export default function NotFound() {
  return (
    <Cover
      eyebrow="Error 404"
      lines={['No encontramos', { text: 'esa página', accent: true }]}
      lead="Puede que el enlace haya cambiado. Vuelve al inicio o busca en el catálogo."
      actions={
        <>
          <Pill as="link" to="/" tone="d">Ir al inicio</Pill>
          <Pill as="link" to="/productos" tone="l">Ver productos</Pill>
        </>
      }
    />
  )
}
