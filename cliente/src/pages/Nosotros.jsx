import { Link } from 'react-router-dom'
import { motion, useReducedMotion } from 'framer-motion'
import { ChevronRight, Headset, Package, ShieldCheck, Truck, Zap } from 'lucide-react'
import { useCategories } from '../hooks/useCategories'
import PageHeader from '../components/ui/PageHeader'
import Reveal from '../components/ui/Reveal'
import SectionHeading from '../components/ui/SectionHeading'
import Counter from '../components/ui/Counter'
import MapEmbed from '../components/ui/MapEmbed'
import ContactInfo from '../components/ui/ContactInfo'
import BrandStrip from '../components/home/BrandStrip'
import { site } from '../config/site'
import '../styles/info-pages.css'

const reasons = [
  { icon: Headset, title: 'Asesoría técnica', text: 'Un equipo con más de 25 años en el sector te ayuda a elegir la referencia correcta.' },
  { icon: Package, title: 'Disponibilidad', text: 'Referencias con stock para comprar en línea y pedido bajo solicitud para lo demás.' },
  { icon: ShieldCheck, title: 'Marcas reconocidas', text: 'Distribuimos marcas que las plantas industriales ya conocen y en las que confían.' },
  { icon: Truck, title: 'Envíos a todo el país', text: 'Recoge en tienda en Bucaramanga o recíbelo donde estés.' },
]

// Tarjeta de "25+ años": número con contador animado y un arco naranja grueso que se dibuja al entrar en pantalla
function YearsCard() {
  const reduced = useReducedMotion()
  return (
    <div className="years">
      <svg className="years__arc" viewBox="0 0 400 300" preserveAspectRatio="none" fill="none" aria-hidden="true">
        <motion.path
          d="M110 6 H336 Q394 6 394 64 V250"
          stroke="var(--accent)"
          strokeWidth="9"
          strokeLinecap="round"
          vectorEffect="non-scaling-stroke"
          initial={{ pathLength: reduced ? 1 : 0 }}
          whileInView={{ pathLength: 1 }}
          viewport={{ once: true, margin: '0px 0px -80px 0px' }}
          transition={{ duration: 1.6, ease: [0.22, 1, 0.36, 1] }}
        />
      </svg>
      <div className="years__card">
        <span className="years__ring years__ring--a" aria-hidden="true" />
        <span className="years__ring years__ring--b" aria-hidden="true" />
        <p className="years__number" aria-label={`Más de ${site.years} años`}>
          <Counter to={site.years} className="years__value" />
          <span className="years__plus" aria-hidden="true">+</span>
        </p>
        <p className="years__label" aria-hidden="true">Años</p>
        <p className="years__note">{site.tagline}</p>
      </div>
    </div>
  )
}

export default function Nosotros() {
  const { data: categories } = useCategories()

  return (
    <>
      <PageHeader title="Nosotros" crumbs={[{ label: 'Nosotros' }]}>
        {site.tagline} · más de {site.years} años en {site.city.split(',')[0]}.
      </PageHeader>

      <section className="section" aria-labelledby="h-quienes">
        <div className="container about">
          <Reveal className="about__text">
            <SectionHeading id="h-quienes" eyebrow="Quiénes somos">
              Soluciones <em>industriales</em>
            </SectionHeading>
            <p>
              Imagen Eléctrica es una distribuidora de material eléctrico industrial en {site.city}. Con más de {site.years} años
              de experiencia acompañamos a empresas y personas con motores, variadores, cables, iluminación y automatización.
            </p>
            <p>
              Hoy llevamos esa experiencia a la tienda en línea: compra lo que está disponible o pide tu cotización y un asesor
              te responde.
            </p>
            <Link to="/productos" className="btn btn--accent btn--lg">Ver productos</Link>
          </Reveal>
          <Reveal className="about__years" delay={0.1}>
            <YearsCard />
          </Reveal>
        </div>
      </section>

      {categories.roots.length > 0 && (
        <section className="section section--violet section--clip" aria-labelledby="h-hacemos">
          <div className="container">
            <SectionHeading id="h-hacemos" eyebrow="Qué hacemos" lead="Nuestras líneas de producto.">
              Todo para tu <em>proyecto</em>
            </SectionHeading>
            <ul className="lines-list">
              {categories.roots.map((root) => (
                <li key={root.id}>
                  <Link to={`/productos?category=${root.slug}`} className="card lines-list__item">
                    <span className="lines-list__icon"><Zap size={20} aria-hidden="true" /></span>
                    <span>{root.name}</span>
                    <ChevronRight size={18} aria-hidden="true" />
                  </Link>
                </li>
              ))}
            </ul>
          </div>
        </section>
      )}

      <section className="section section--ink section--clip" aria-labelledby="h-por-que">
        <div className="container">
          <SectionHeading id="h-por-que" eyebrow="Por qué elegirnos">
            Cuatro razones para <em>confiar</em>
          </SectionHeading>
          <ul className="reasons">
            {reasons.map(({ icon: Icon, title, text }, i) => (
              <Reveal as="li" key={title} delay={i * 0.07}>
                <span className="reasons__icon"><Icon size={24} aria-hidden="true" /></span>
                <h3>{title}</h3>
                <p>{text}</p>
              </Reveal>
            ))}
          </ul>
        </div>
      </section>

      {/* Trayectoria: se agrega cuando la empresa entregue los hitos; no se inventan fechas. */}

      <BrandStrip />

      <section className="section section--violet section--clip" aria-labelledby="h-visitanos">
        <div className="container">
          <SectionHeading id="h-visitanos" eyebrow="Visítanos">
            Estamos en <em>Bucaramanga</em>
          </SectionHeading>
          <div className="visit">
            <ContactInfo whatsapp={false} />
            <MapEmbed />
          </div>
        </div>
      </section>
    </>
  )
}
