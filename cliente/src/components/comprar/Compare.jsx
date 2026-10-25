import { Link } from 'react-router-dom'
import { ArrowRight, Check, FileText, ShoppingCart } from 'lucide-react'
import Reveal from '../ui/Reveal'
import SectionHeading from '../ui/SectionHeading'

const cards = [
  {
    id: 'direct',
    tone: 'white',
    icon: ShoppingCart,
    title: 'Compra directa',
    when: 'Cuando el producto está disponible, tiene precio y hay stock para la cantidad que pides.',
    badges: [{ label: 'Disponible', cls: 'badge--success' }],
    includes: [
      'Precio en COP con IVA incluido',
      'Cantidad limitada al stock disponible',
      'Pedido con seguimiento en Mi cuenta',
      'Tu asesor confirma el pago y la entrega',
    ],
    cta: { label: 'Ver productos', to: '/productos', cls: 'btn--accent' },
  },
  {
    id: 'quote',
    tone: 'violet',
    icon: FileText,
    title: 'Cotización',
    when: 'Cuando el producto es solo por cotización, bajo pedido, no tiene stock o pides más de lo disponible.',
    badges: [
      { label: 'Solo cotización', cls: 'badge--muted' },
      { label: 'Bajo pedido', cls: 'badge--warning' },
      { label: 'Sin stock: cotiza', cls: 'badge--warning' },
    ],
    includes: [
      'Un asesor pone el precio de cada producto',
      'Recibes la cotización valorada en Mi cuenta',
      'Al aceptarla se crea un pedido nuevo',
      'La cotización se conserva como historial',
    ],
    cta: { label: 'Solicitar cotización', to: '/productos?sale_mode=quote', cls: 'btn--ghost-light' },
  },
]

// Dos tarjetas grandes lado a lado; la del modo elegido arriba se resalta
export default function Compare({ mode }) {
  return (
    <section className="section section--ink section--clip compare" aria-labelledby="h-comparar">
      <span className="compare__arc" aria-hidden="true" />
      <div className="container">
        <SectionHeading id="h-comparar" eyebrow="Elige tu camino" lead="Si tu carrito mezcla los dos, creamos una solicitud de cada tipo.">
          Compra directa frente a <em>cotización</em>
        </SectionHeading>
        <div className="compare__grid">
          {cards.map(({ id, tone, icon: Icon, title, when, badges, includes, cta }, i) => (
            <Reveal key={id} delay={i * 0.1} className={`ccompare ccompare--${tone} ${mode === id ? 'is-picked' : ''}`}>
              {mode === id && <span className="ccompare__pick">Tu camino</span>}
              <span className="ccompare__icon"><Icon size={28} aria-hidden="true" /></span>
              <h3>{title}</h3>
              <p className="ccompare__label">Cuándo aplica</p>
              <p>{when}</p>
              <p className="ccompare__badges">{badges.map((b) => <span key={b.label} className={`badge ${b.cls}`}>{b.label}</span>)}</p>
              <p className="ccompare__label">Qué incluye</p>
              <ul>
                {includes.map((item) => (
                  <li key={item}><span><Check size={14} strokeWidth={3} aria-hidden="true" /></span>{item}</li>
                ))}
              </ul>
              <Link to={cta.to} className={`btn btn--lg ${cta.cls}`}>
                {cta.label} <ArrowRight size={18} aria-hidden="true" />
              </Link>
            </Reveal>
          ))}
        </div>
      </div>
    </section>
  )
}
