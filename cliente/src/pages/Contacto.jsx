import ContactCover from '../components/contacto/ContactCover'
import SmartForm from '../components/contacto/SmartForm'
import ContactCard from '../components/contacto/ContactCard'
import SectionHeading from '../components/ui/SectionHeading'
import VisitCard from '../components/ui/VisitCard'
import Reveal from '../components/ui/Reveal'
import '../styles/pages.css'
import '../styles/kit.css'
import '../styles/contacto.css'

// Climas: marca (portada) → claro (formulario) → profundo (visítanos)
export default function Contacto() {
  return (
    <>
      <ContactCover />

      <section className="section contact-main" aria-label="Escríbenos">
        <div className="container contact-main__grid">
          <Reveal><SmartForm /></Reveal>
          <Reveal delay={0.1}><ContactCard /></Reveal>
        </div>
      </section>

      <section className="section section--ink section--clip visit-ink" aria-labelledby="h-visitanos">
        <div className="container">
          <SectionHeading id="h-visitanos" eyebrow="Visítanos">
            Pásate por <em>la tienda</em>
          </SectionHeading>
          <VisitCard photoLabel="Fachada o interior para Contacto" />
        </div>
      </section>
    </>
  )
}
