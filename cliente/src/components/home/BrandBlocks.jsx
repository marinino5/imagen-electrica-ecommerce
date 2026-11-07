import { Boxes, FileText, PhoneCall, Store, Truck, Zap } from 'lucide-react'
import Xb from '../ui/Xb'
import Hl from '../ui/Hl'
import Mix from '../ui/Mix'
import SectionLabel from '../ui/SectionLabel'
import Pill from '../ui/Pill'
import { site } from '../../config/site'
import Bgfx from '../ui/Bgfx'
import '../../styles/ie-home.css'

function Bullets({ items }) {
  return (
    <ul className="xb-list">
      {items.map(({ icon: Icon, text }) => (
        <li key={text}>
          <span aria-hidden="true"><Icon size={15} /></span>
          {text}
        </li>
      ))}
    </ul>
  )
}

// Dos bloques con el recurso «bloque extendido» sobre un mismo fondo continuo: Asesoría (foto a la izquierda, bloque desde
// el borde izquierdo) y Entrega (foto a la derecha, bloque desde el borde derecho). Cada uno entra desde su lado.
export default function BrandBlocks() {
  return (
    <section className="ie-xbs bg-lila-fade" aria-label="Cómo trabajamos">
      <Bgfx e b="flip" />
      <Xb
        side="l"
        photo={<img src="/img/fotos/asesoria-tablero.webp" alt="Técnico con casco junto a un tablero eléctrico abierto, revisando las conexiones" width="1280" height="960" loading="lazy" decoding="async" />}
      >
        <SectionLabel n="03">Asesoría</SectionLabel>
        <h2 className="rd-h2"><Mix light="Asesoría técnica">de quien <Hl>conoce el producto</Hl></Mix></h2>
        <p className="rd-lead">Cuéntanos qué máquina vas a mover o qué tablero vas a armar y te recomendamos la referencia exacta, con su potencia, su protección y el tiempo de entrega.</p>
        <Bullets items={[
          { icon: Zap, text: 'Referencia exacta según tu máquina o tablero' },
          { icon: Boxes, text: 'Productos originales de fabricantes reconocidos' },
          { icon: FileText, text: 'Fichas técnicas del fabricante' },
        ]} />
        <div className="xb-actions">
          <Pill as="a" href={site.whatsappUrl} target="_blank" rel="noreferrer" tone="o">Hablar con un asesor</Pill>
        </div>
      </Xb>

      <Xb
        side="r"
        photo={<img src="/img/fotos/entrega-almacen.webp" alt="Operario de almacén escaneando una caja de material eléctrico con un lector de código" width="1280" height="960" loading="lazy" decoding="async" />}
      >
        <SectionLabel n="04">Entrega</SectionLabel>
        <h2 className="rd-h2"><Mix light="Del catálogo">directo <Hl>a tu obra</Hl></Mix></h2>
        <p className="rd-lead">Recógelo en nuestra tienda de Bucaramanga o recíbelo en tu obra o planta. Un asesor confirma el pago y coordina la entrega para que llegue completo.</p>
        <Bullets items={[
          { icon: Store, text: 'Recogida en nuestra tienda de Bucaramanga' },
          { icon: Truck, text: 'Envío a tu obra o planta' },
          { icon: PhoneCall, text: 'Un asesor confirma pago y entrega' },
        ]} />
        <div className="xb-actions">
          <Pill as="link" to="/como-comprar" tone="d">Así compras</Pill>
        </div>
      </Xb>
    </section>
  )
}
