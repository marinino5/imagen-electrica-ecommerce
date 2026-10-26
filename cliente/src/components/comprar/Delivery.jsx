import { MapPinned, Store, Truck } from 'lucide-react'
import { site } from '../../config/site'
import Reveal from '../ui/Reveal'
import SectionHeading from '../ui/SectionHeading'

const options = [
  { icon: Store, title: 'Recoger en tienda', text: `${site.address}, ${site.city}.`, note: 'Sin costo de envío.' },
  { icon: Truck, title: 'Envío local', text: 'Bucaramanga y su área metropolitana.', note: 'El costo lo confirma tu asesor.' },
  { icon: MapPinned, title: 'Envío nacional', text: 'A todo el país.', note: 'El costo lo confirma tu asesor.' },
]

// Ilustración: icono grande sobre un círculo lavanda con arco naranja y puntos
function IconArt({ icon: Icon }) {
  return (
    <span className="art" aria-hidden="true">
      <span className="art__circle" />
      <span className="art__arc" />
      <span className="art__dots" />
      <Icon size={76} strokeWidth={1.25} />
    </span>
  )
}

export default function Delivery() {
  return (
    <section className="section delivery-section" aria-labelledby="h-entregas">
      <div className="container">
        <SectionHeading id="h-entregas" eyebrow="Entregas" lead="Elige cómo recibirlo al enviar tu solicitud. El costo del envío se confirma con tu asesor.">
          Recíbelo <em>donde estés</em>
        </SectionHeading>
        <ul className="dcards">
          {options.map(({ icon, title, text, note }, i) => (
            <Reveal as="li" key={title} delay={i * 0.1} className="dcard card">
              <IconArt icon={icon} />
              <h3>{title}</h3>
              <p>{text}</p>
              <p className="dcard__note">{note}</p>
            </Reveal>
          ))}
        </ul>
      </div>
    </section>
  )
}
