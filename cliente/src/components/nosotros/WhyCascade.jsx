import { Headset, Package, ShieldCheck, Truck } from 'lucide-react'
import Eyebrow from '../ui/Eyebrow'
import Reveal from '../ui/Reveal'

const reasons = [
  { icon: Headset, tone: 'violet', title: 'Asesoría técnica', text: 'Un equipo con más de 25 años en el sector te ayuda a elegir la referencia correcta.' },
  { icon: Package, tone: 'white', title: 'Disponibilidad', text: 'Referencias con stock para comprar en línea y pedido bajo solicitud para lo demás.' },
  { icon: ShieldCheck, tone: 'orange', title: 'Marcas reconocidas', text: 'Distribuimos marcas que la industria ya conoce y en las que confía.' },
  { icon: Truck, tone: 'ink', title: 'Envíos a todo el país', text: 'Recoge en tienda en Bucaramanga o recíbelo donde estés.' },
]

// Tarjetas en cascada en Z: rotaciones leves en escritorio, apiladas sin rotar en celular
export default function WhyCascade() {
  return (
    <section className="wrap ab-why" aria-labelledby="h-por-que">
      <Reveal>
        <Eyebrow>Por qué elegirnos</Eyebrow>
        <h2 id="h-por-que" className="h2-sys">Cuatro razones <em>para confiar</em></h2>
      </Reveal>
      <ol className="ab-why__list">
        {reasons.map(({ icon: Icon, tone, title, text }, i) => (
          <Reveal as="li" key={title} delay={i * 0.06} className={`ab-why__card ab-why__card--${tone}`}>
            <span className="ab-why__ic"><Icon size={26} aria-hidden="true" /></span>
            <h3>{title}</h3>
            <p>{text}</p>
          </Reveal>
        ))}
      </ol>
    </section>
  )
}
