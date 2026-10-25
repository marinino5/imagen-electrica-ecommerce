import AboutCover from '../components/nosotros/AboutCover'
import KineticStrip from '../components/nosotros/KineticStrip'
import AboutBento from '../components/nosotros/AboutBento'
import LinesScroller from '../components/nosotros/LinesScroller'
import WhyStack from '../components/nosotros/WhyStack'
import BrandChips from '../components/nosotros/BrandChips'
import SectionHeading from '../components/ui/SectionHeading'
import VisitCard from '../components/ui/VisitCard'
import '../styles/kit.css'
import '../styles/nosotros.css'

// Ritmo de climas: marca (portada) → profundo (franja) → claro (mosaico) → marca (líneas)
// → claro (razones) → marca (marcas) → claro (visítanos)
export default function Nosotros() {
  return (
    <>
      <AboutCover />
      <KineticStrip />
      <AboutBento />
      <LinesScroller />
      <WhyStack />
      <BrandChips />

      {/* Trayectoria: se agrega cuando la empresa entregue los hitos; no se inventan fechas. */}

      <section id="visitanos" className="section visit-section" aria-labelledby="h-visitanos">
        <div className="container">
          <SectionHeading id="h-visitanos" eyebrow="Visítanos">
            Estamos en <em>Bucaramanga</em>
          </SectionHeading>
          <VisitCard />
        </div>
      </section>
    </>
  )
}
