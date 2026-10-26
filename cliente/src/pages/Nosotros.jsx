import AboutCover from '../components/nosotros/AboutCover'
import AboutBento from '../components/nosotros/AboutBento'
import WhyStack from '../components/nosotros/WhyStack'
import BrandMarquee from '../components/nosotros/BrandMarquee'
import SectionHeading from '../components/ui/SectionHeading'
import StoreMap from '../components/ui/StoreMap'
import '../styles/kit.css'
import '../styles/nosotros.css'

// Ritmo de climas: marca (portada) → claro (mosaico) → claro con tarjetas de color (razones)
// → marca (marcas) → claro (visítanos)
export default function Nosotros() {
  return (
    <>
      <AboutCover />
      <AboutBento />
      <WhyStack />
      <BrandMarquee />

      {/* Trayectoria: se agrega cuando la empresa entregue los hitos; no se inventan fechas. */}

      <section id="visitanos" className="section visit-section" aria-labelledby="h-visitanos">
        <div className="container">
          <SectionHeading id="h-visitanos" eyebrow="Visítanos">
            Estamos en <em>Bucaramanga</em>
          </SectionHeading>
          <StoreMap />
        </div>
      </section>
    </>
  )
}
