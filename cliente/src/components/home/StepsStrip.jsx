import { Link } from 'react-router-dom'
import { ArrowRight, ClipboardList, MessageCircle, PackageCheck, Search } from 'lucide-react'
import '../../styles/home.css'

const steps = [
  { Icon: Search, title: 'Busca', text: 'Por referencia, marca o familia.' },
  { Icon: ClipboardList, title: 'Elige', text: 'Compara las referencias de la ficha.' },
  { Icon: MessageCircle, title: 'Envía tu pedido', text: 'Un asesor confirma pago y entrega.' },
  { Icon: PackageCheck, title: 'Recibe', text: 'En tu obra, taller o en la tienda.' },
]

// Resumen de «Cómo comprar» en cuatro iconos; la guía completa vive en su propia página
export default function StepsStrip() {
  return (
    <section className="wrap hm-steps" aria-labelledby="h-pasos">
      <h2 id="h-pasos" className="hm-steps__title">Comprar es así de fácil</h2>
      <ol className="hm-steps__list">
        {steps.map(({ Icon, title, text }) => (
          <li key={title}>
            <span className="hm-steps__ic" aria-hidden="true"><Icon size={26} strokeWidth={1.6} /></span>
            <div>
              <b>{title}</b>
              <span>{text}</span>
            </div>
          </li>
        ))}
      </ol>
      <Link to="/como-comprar" className="hm-steps__more">Ver la guía completa <ArrowRight size={16} aria-hidden="true" /></Link>
    </section>
  )
}
