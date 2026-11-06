import { Check, Headset, MessageCircle, PackageCheck } from 'lucide-react'

// Ilustraciones de los pasos que ocurren fuera de la tienda (la respuesta del asesor y la aprobación).
// Son composiciones de la interfaz con el diseño de la marca, no fotos: el texto es de ejemplo.
export default function StepArt({ kind }) {
  if (kind === 'chat') {
    return (
      <div className="how-art how-art--chat" role="img" aria-label="Ejemplo de conversación: el cliente envía su lista y el asesor responde con referencia, precio y tiempo de entrega">
        <div className="how-art__top"><span className="how-art__avatar"><Headset size={18} aria-hidden="true" /></span><b>Asesor de Imagen Eléctrica</b><small>En línea</small></div>
        <p className="how-art__msg how-art__msg--in">Hola, necesito 2 contactores de 25 A y un variador CFW300.</p>
        <p className="how-art__msg how-art__msg--out">Listo. Te confirmo la referencia exacta, el precio con IVA y el tiempo de entrega.</p>
        <p className="how-art__msg how-art__msg--out how-art__quote"><MessageCircle size={14} aria-hidden="true" /> Cotización enviada</p>
        <span className="how-art__tag">Ejemplo</span>
      </div>
    )
  }
  return (
    <div className="how-art how-art--approve" role="img" aria-label="Ejemplo de seguimiento: la cotización aprobada pasa a pago confirmado y entrega coordinada">
      <div className="how-art__top"><b>Tu cotización</b><small>Enviada</small></div>
      <ol className="how-art__flow">
        <li className="is-done"><span><Check size={14} aria-hidden="true" /></span>Aprobada por ti</li>
        <li className="is-done"><span><Check size={14} aria-hidden="true" /></span>Pago confirmado por el asesor</li>
        <li className="is-now"><span><PackageCheck size={14} aria-hidden="true" /></span>Entrega coordinada</li>
      </ol>
      <span className="how-art__tag">Ejemplo</span>
    </div>
  )
}
