import ContactCover from '../components/contacto/ContactCover'
import ContactActions from '../components/contacto/ContactActions'
import SmartForm from '../components/contacto/SmartForm'
import VisitMap from '../components/ui/VisitMap'
import Bezel from '../components/ui/Bezel'
import Eyebrow from '../components/ui/Eyebrow'
import Reveal from '../components/ui/Reveal'
import '../styles/contacto.css'

export default function Contacto() {
  return (
    <>
      <ContactCover />
      <ContactActions />

      <section className="wrap ct-form" aria-labelledby="h-ayuda">
        <Reveal>
          <Bezel className="smart-bz"><SmartForm /></Bezel>
        </Reveal>
      </section>

      <section className="wrap ct-map" aria-labelledby="h-visitanos">
        <Reveal>
          <Eyebrow>Visítanos</Eyebrow>
          <h2 id="h-visitanos" className="h2-sys">Pásate por <em>la tienda</em></h2>
        </Reveal>
        <Reveal delay={0.06}><VisitMap /></Reveal>
      </section>
    </>
  )
}
