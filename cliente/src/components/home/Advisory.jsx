import { Link } from 'react-router-dom'
import Xb from '../ui/Xb'
import Hl from '../ui/Hl'
import Reveal from '../ui/Reveal'
import SectionLabel from '../ui/SectionLabel'
import ChannelCards from '../ui/ChannelCards'
import Squares from '../ui/Squares'
import '../../styles/ie-home.css'

// Cierre claro del Inicio: fondo lila con arco naranja, el técnico en arco alto con bloque extendido desde el borde izquierdo,
// el titular y los tres canales. El pie oscuro viene justo después.
export default function Advisory() {
  return (
    <section className="adv2 bgt bgt-d" aria-labelledby="h-asesoria">
      <Squares items={[{ w: 200, h: 240, tone: 'orange', rate: 0.1, top: '14%', left: '-30px' }, { w: 140, tone: 'lila', rate: -0.1, bottom: '16%', right: '6%' }]} />
      <Xb
        side="l"
        arch
        ratio="6 / 7"
        photo={<img src="/img/fotos/tecnico-casco.webp" alt="Técnico con casco y gafas frente a un tablero eléctrico" width="852" height="1280" loading="lazy" decoding="async" style={{ objectPosition: '50% 20%' }} />}
      >
        <div className="adv2__copy">
          <SectionLabel n="07">Asesoría</SectionLabel>
          <h2 id="h-asesoria" className="rd-h2 adv2__title">¿Qué necesitas <Hl>mover hoy?</Hl></h2>
          <p className="rd-lead">Un asesor te responde con la referencia, el precio y el tiempo de entrega. Escoge el canal que prefieras.</p>
          <ChannelCards />
        </div>
      </Xb>
      <Reveal>
        <p className="adv2__strip">¿Compras seguido? <Link to="/crear-cuenta">Crea tu cuenta gratis →</Link></p>
      </Reveal>
    </section>
  )
}
