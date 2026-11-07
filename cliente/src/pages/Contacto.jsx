import BusinessCard from '../components/contacto/BusinessCard'
import SmartForm from '../components/contacto/SmartForm'
import Bgfx from '../components/ui/Bgfx'
import Hl from '../components/ui/Hl'
import Mix from '../components/ui/Mix'
import Pop from '../components/ui/Pop'
import VisitFull from '../components/ui/VisitFull'
import '../styles/contacto.css'
import '../styles/ie-contacto.css'

// Contacto: «tarjeta de visita» con los tres canales, el formulario inteligente y el mapa. Entrada propia: fundido con escala 0,98 → 1.
export default function Contacto() {
  return (
    <>
      <section className="ct-hero bg-lila-fade" aria-labelledby="h-contacto">
        <Bgfx b a="motor:r" />
        <div className="wrap">
          <Pop className="ct-hero__head">
            <h1 id="h-contacto" className="ie-h1"><Mix light="Hablemos de">tu <Hl>proyecto</Hl></Mix></h1>
            <p className="rd-lead">Escríbenos o llámanos y un asesor te responde con la referencia y el precio.</p>
          </Pop>
          <Pop delay={0.12}><BusinessCard /></Pop>
        </div>
      </section>

      <section className="ct-formsec bg-lila-soft" aria-labelledby="h-ayuda">
        <Bgfx d c="l" arcTop="10%" />
        <div className="wrap ct-formsec__wrap">
          <Pop className="ct-panel" id="formulario">
            <SmartForm />
          </Pop>
        </div>
      </section>

      <VisitFull id="visitanos" fx={{ e: true, a: 'gear:r' }} />
    </>
  )
}
