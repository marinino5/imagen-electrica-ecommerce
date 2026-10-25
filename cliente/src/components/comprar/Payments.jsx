import { Banknote, Clock, Headset, Send } from 'lucide-react'
import Reveal from '../ui/Reveal'
import SectionHeading from '../ui/SectionHeading'

const flow = [
  { icon: Send, text: 'Envías tu solicitud. No genera ningún cobro automático.' },
  { icon: Headset, text: 'Tu asesor te contacta e indica los medios de pago disponibles.' },
  { icon: Banknote, text: 'Confirma tu pago y coordina la entrega contigo.' },
]

// Dice la verdad: hoy el pago lo confirma el asesor; el pago en línea llega más adelante
export default function Payments({ mode }) {
  return (
    <section className="section section--violet section--clip payments" aria-labelledby="h-pagos">
      <span className="payments__arc" aria-hidden="true" />
      <div className="container payments__grid">
        <div>
          <SectionHeading
            id="h-pagos"
            eyebrow="Pagos"
            lead={mode === 'direct'
              ? 'Hoy el pago de tu pedido lo confirma tu asesor, que te indica los medios disponibles.'
              : 'Cuando aceptas la cotización se crea tu pedido y tu asesor te indica los medios de pago disponibles.'}
          >
            Pagas <em>con tu asesor</em>
          </SectionHeading>
          <Reveal>
            <span className="soon"><Clock size={16} aria-hidden="true" /> Pago en línea · próximamente</span>
          </Reveal>
        </div>
        <ol className="payments__flow">
          {flow.map(({ icon: Icon, text }, i) => (
            <Reveal as="li" key={text} delay={i * 0.1}>
              <span className="payments__icon"><Icon size={22} aria-hidden="true" /></span>
              <p>{text}</p>
            </Reveal>
          ))}
        </ol>
      </div>
    </section>
  )
}
