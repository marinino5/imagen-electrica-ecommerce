import { Link } from 'react-router-dom'
import { ArrowRight, Banknote, FileText, MapPin, ShoppingCart, Store, Truck, Zap } from 'lucide-react'
import PageHeader from '../components/ui/PageHeader'
import Reveal from '../components/ui/Reveal'
import SectionHeading from '../components/ui/SectionHeading'
import JoinCta from '../components/home/JoinCta'
import { site } from '../config/site'
import '../styles/info-pages.css'

const paths = [
  {
    icon: ShoppingCart,
    title: 'Compra directa',
    text: 'Para productos disponibles, con precio en COP e IVA incluido.',
    steps: [
      'Agrega al carrito lo que está disponible.',
      'Envía tu pedido con tu cuenta.',
      'Un asesor confirma el pago contigo.',
      'Recibe en tienda o con envío.',
    ],
    cta: { label: 'Ver productos', to: '/productos', style: 'accent' },
  },
  {
    icon: FileText,
    title: 'Cotización',
    text: 'Para productos industriales, bajo pedido o sin stock.',
    steps: [
      'Agrega los productos a tu cotización.',
      'Envía la solicitud con tu cuenta.',
      'Un asesor pone el precio y te responde.',
      'Acepta la cotización y se convierte en pedido.',
    ],
    cta: { label: 'Solicitar cotización', to: '/productos?sale_mode=quote', style: 'primary' },
  },
]

const delivery = [
  { icon: Store, title: 'Recoger en tienda', text: `${site.address}, ${site.city}.` },
  { icon: MapPin, title: 'Envío local', text: 'Bucaramanga y su área metropolitana.' },
  { icon: Truck, title: 'Envío nacional', text: 'A todo el país.' },
]

const faqs = [
  { q: '¿Necesito una cuenta para comprar o cotizar?', a: 'Sí. Para enviar tu solicitud crea una cuenta como persona natural o como empresa. Es gratis y tus datos quedan guardados para la próxima vez.' },
  { q: '¿Qué pasa si mi carrito mezcla productos de compra directa y de cotización?', a: 'Creamos dos solicitudes separadas: un pedido con lo que está disponible y una cotización con el resto.' },
  { q: '¿Los precios incluyen IVA?', a: 'Sí. Los precios están en COP con IVA incluido.' },
  { q: '¿Qué pasa si un producto está agotado?', a: 'Va a tu cotización: un asesor te responde con la disponibilidad y el precio.' },
  { q: '¿Cómo sigo el estado de mi solicitud?', a: 'En Mi cuenta ves todos tus pedidos y cotizaciones con su estado.' },
]

export default function ComoComprar() {
  return (
    <>
      <PageHeader title="Cómo" accent="comprar" crumbs={[{ label: 'Cómo comprar' }]}>
        Dos caminos para llevar lo que tu planta necesita: compra en línea o pide tu cotización.
      </PageHeader>

      <section className="section" aria-labelledby="h-caminos">
        <div className="container">
          <SectionHeading id="h-caminos" eyebrow="Elige tu camino">
            Compra directa o <em>cotización</em>
          </SectionHeading>
          <div className="paths">
            {paths.map(({ icon: Icon, title, text, steps, cta }, i) => (
              <Reveal key={title} delay={i * 0.08} className="card path">
                <span className="path__icon"><Icon size={26} aria-hidden="true" /></span>
                <h3 className="path__title">{title}</h3>
                <p className="path__text">{text}</p>
                <ol className="path__steps">
                  {steps.map((s) => <li key={s}>{s}</li>)}
                </ol>
                <Link to={cta.to} className={`btn btn--lg ${cta.style === 'accent' ? 'btn--accent' : 'btn--primary'}`}>
                  {cta.label} <ArrowRight size={18} aria-hidden="true" />
                </Link>
              </Reveal>
            ))}
          </div>
        </div>
      </section>

      <section className="section section--violet section--clip" aria-labelledby="h-envios">
        <div className="container info-split">
          <div>
            <SectionHeading id="h-envios" eyebrow="Envíos y entrega" lead="El costo del envío se calcula al confirmar la solicitud.">
              Recíbelo <em>donde estés</em>
            </SectionHeading>
            <ul className="delivery">
              {delivery.map(({ icon: Icon, title, text }) => (
                <li key={title}>
                  <Icon size={22} aria-hidden="true" />
                  <div>
                    <h3>{title}</h3>
                    <p>{text}</p>
                  </div>
                </li>
              ))}
            </ul>
          </div>
          <div>
            <SectionHeading eyebrow="Pagos">
              Pagos <em>con tu asesor</em>
            </SectionHeading>
            <p className="info-split__text">
              <Banknote size={22} aria-hidden="true" />
              Por ahora el pago se coordina con tu asesor cuando confirmamos tu pedido. Más adelante habilitaremos el pago en línea.
            </p>
          </div>
        </div>
      </section>

      <section className="section" aria-labelledby="h-faq">
        <div className="container faq-wrap">
          <SectionHeading id="h-faq" eyebrow="Preguntas frecuentes">
            Resolvemos tus <em>dudas</em>
          </SectionHeading>
          <div className="faq">
            {faqs.map(({ q, a }) => (
              <details key={q} className="card">
                <summary><Zap size={16} aria-hidden="true" /> {q}</summary>
                <p>{a}</p>
              </details>
            ))}
          </div>
        </div>
      </section>

      <JoinCta />
    </>
  )
}
