import AboutCover from '../components/nosotros/AboutCover'
import AboutStory from '../components/nosotros/AboutStory'
import Services from '../components/nosotros/Services'
import WhyCascade from '../components/nosotros/WhyCascade'
import BrandBento from '../components/nosotros/BrandBento'
import VisitMap from '../components/ui/VisitMap'
import Eyebrow from '../components/ui/Eyebrow'
import Reveal from '../components/ui/Reveal'
import '../styles/nosotros.css'

export default function Nosotros() {
  return (
    <>
      <AboutCover />
      <AboutStory />
      <Services />
      <WhyCascade />
      <BrandBento />

      <section className="wrap ab-visit" aria-labelledby="h-visitanos">
        <Reveal>
          <Eyebrow>Visítanos</Eyebrow>
          <h2 id="h-visitanos" className="h2-sys">Estamos en <em>Bucaramanga</em></h2>
        </Reveal>
        <Reveal delay={0.06}><VisitMap id="visitanos" /></Reveal>
      </section>
    </>
  )
}
