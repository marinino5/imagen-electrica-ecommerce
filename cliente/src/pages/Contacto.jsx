import SmartForm from '../components/contacto/SmartForm'
import ChannelCards from '../components/ui/ChannelCards'
import VisitFull from '../components/ui/VisitFull'
import Xb from '../components/ui/Xb'
import Hl from '../components/ui/Hl'
import Mix from '../components/ui/Mix'
import SectionLabel from '../components/ui/SectionLabel'
import '../styles/contacto.css'
import '../styles/rd-pages.css'
import '../styles/ie-pages.css'

export default function Contacto() {
  return (
    <>
      <section className="ct-hero bg-lila-fade" aria-labelledby="h-contacto">
        <Xb
          side="l"
          photo={<img src="/img/fotos/contacto.webp" alt="Manos de un técnico conectando módulos en un tablero eléctrico" width="1280" height="854" fetchPriority="high" loading="eager" />}
        >
          <p className="rd-label">Contacto</p>
          <h1 id="h-contacto" className="ie-h1"><Mix light="Hablemos de">tu <Hl>proyecto</Hl></Mix></h1>
          <p className="rd-lead">Escríbenos o llámanos y un asesor te responde con la referencia y el precio.</p>
        </Xb>
      </section>

      <section className="ct-chans bg-lila-soft" aria-label="Formas de contactarnos">
        <div className="wrap">
          <ChannelCards row />
        </div>
      </section>

      <section className="ct-formsec bg-lila-arc" aria-labelledby="h-ayuda">
        <div className="wrap ct-formsec__wrap">
          <div className="ct-panel" id="formulario">
            <svg className="ct-panel__arc" viewBox="0 0 180 180" aria-hidden="true" focusable="false"><path d="M0 0 A180 180 0 0 1 180 180" /></svg>
            <SectionLabel n="01">Escríbenos</SectionLabel>
            <h2 className="rd-h2" id="h-ayuda">Cuéntanos <Hl>qué necesitas</Hl></h2>
            <div className="ct-panel__form"><SmartForm /></div>
          </div>
        </div>
      </section>

      <VisitFull id="visitanos" n="02" />
    </>
  )
}
