import { useRef } from 'react'
import { motion, useReducedMotion, useScroll, useTransform } from 'framer-motion'
import { Headset, Package, ShieldCheck, Truck } from 'lucide-react'
import SectionHeading from '../ui/SectionHeading'

const reasons = [
  { icon: Headset, tone: 'violet', title: 'Asesoría técnica', text: 'Un equipo con más de 25 años en el sector te ayuda a elegir la referencia correcta.' },
  { icon: Package, tone: 'white', title: 'Disponibilidad', text: 'Referencias con stock para comprar en línea y pedido bajo solicitud para lo demás.' },
  { icon: ShieldCheck, tone: 'orange', title: 'Marcas reconocidas', text: 'Distribuimos marcas que las plantas industriales ya conocen y en las que confían.' },
  { icon: Truck, tone: 'ink', title: 'Envíos a todo el país', text: 'Recoge en tienda en Bucaramanga o recíbelo donde estés.' },
]

// Cada tarjeta se encoge un poco cuando la siguiente se le monta encima
function StackCard({ reason, index, progress, reduced }) {
  const { icon: Icon, tone, title, text } = reason
  const last = reasons.length - 1
  const scale = useTransform(progress, [index / reasons.length, 1], [1, reduced ? 1 : 1 - (last - index) * 0.045])
  return (
    <motion.li className={`why-card why-card--${tone}`} style={{ scale, '--i': index }}>
      <span className="why-card__num" aria-hidden="true">{String(index + 1).padStart(2, '0')}</span>
      <span className="why-card__icon"><Icon size={30} aria-hidden="true" /></span>
      <h3>{title}</h3>
      <p>{text}</p>
    </motion.li>
  )
}

// Título fijo a la izquierda; a la derecha, 4 tarjetas que se apilan al hacer scroll (sin apilar en celular)
export default function WhyStack() {
  const listRef = useRef(null)
  const reduced = useReducedMotion()
  const { scrollYProgress } = useScroll({ target: listRef, offset: ['start 30%', 'end end'] })

  return (
    <section className="section why" aria-labelledby="h-por-que">
      <span className="why__arc" aria-hidden="true" />
      <div className="container why__grid">
        <div className="why__title">
          <SectionHeading id="h-por-que" eyebrow="Por qué elegirnos" lead="Lo que nos ha mantenido más de 25 años junto a la industria de Bucaramanga.">
            Cuatro razones para <em>confiar</em>
          </SectionHeading>
        </div>
        <ol className="why__list" ref={listRef}>
          {reasons.map((reason, i) => (
            <StackCard key={reason.title} reason={reason} index={i} progress={scrollYProgress} reduced={reduced} />
          ))}
        </ol>
      </div>
    </section>
  )
}
