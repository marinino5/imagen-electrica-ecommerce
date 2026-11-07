import Reveal from '../ui/Reveal'
import Hl from '../ui/Hl'
import SectionLabel from '../ui/SectionLabel'
import Pill from '../ui/Pill'
import Parallax from '../ui/Parallax'
import { site } from '../../config/site'
import '../../styles/home.css'

// Dos bloques alternados foto / texto: la foto entra desde un lado y el texto desde el contrario con 120 ms de retraso.
export default function BrandBlocks() {
  return (
    <section className="rd-sec bg-white hm-blocks" aria-label="Cómo trabajamos">
      <div className="wrap hm-blocks__stack">
        <div className="split">
          <Reveal side="l" className="split__media">
            <div className="capsule hm-capsule">
              <Parallax src="/img/fotos/asesor-mostrador.webp" alt="Asesora de Imagen Eléctrica atendiendo con diadema" className="hm-capsule__px" />
            </div>
          </Reveal>
          <Reveal side="r" delay={0.12} className="split__text">
            <SectionLabel n="02">Asesoría</SectionLabel>
            <h2 className="rd-h2">Asesoría técnica de quien <Hl>conoce el producto</Hl></h2>
            <p className="rd-lead">Cuéntanos qué máquina vas a mover o qué tablero vas a armar. Te recomendamos la referencia exacta, con su potencia, su protección y la disponibilidad real en bodega.</p>
            <div className="hm-actions">
              <Pill as="a" href={site.whatsappUrl} target="_blank" rel="noreferrer" tone="o">Hablar con un asesor</Pill>
            </div>
          </Reveal>
        </div>

        <div className="split split--flip">
          <Reveal side="r" className="split__media">
            <div className="capsule capsule--r hm-capsule">
              <Parallax src="/img/fotos/entrega.webp" alt="Mensajero entregando paquetes en una puerta" className="hm-capsule__px" />
            </div>
          </Reveal>
          <Reveal side="l" delay={0.12} className="split__text">
            <SectionLabel n="03">Entrega</SectionLabel>
            <h2 className="rd-h2">Del catálogo <Hl>a tu obra</Hl></h2>
            <p className="rd-lead">Recógelo en nuestra tienda de Bucaramanga o recíbelo en tu obra o planta. Un asesor confirma el pago y coordina la entrega para que llegue completo.</p>
            <div className="hm-actions">
              <Pill as="link" to="/como-comprar" tone="d">Así compras</Pill>
            </div>
          </Reveal>
        </div>
      </div>
    </section>
  )
}
