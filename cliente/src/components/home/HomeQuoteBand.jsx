import { MessageCircle } from 'lucide-react'
import { site } from '../../config/site'
import Bezel from '../ui/Bezel'
import Eyebrow from '../ui/Eyebrow'
import Pill from '../ui/Pill'
import Reveal from '../ui/Reveal'
import '../../styles/home.css'

// Banda final ink: cotización sin cuenta
export default function HomeQuoteBand() {
  return (
    <section className="wrap hm-section hm-quote">
      <Reveal>
        <Bezel coreClassName="hm-quote__core">
          <div>
            <Eyebrow className="eyebrow--light">Cotización sin cuenta</Eyebrow>
            <h2>¿Necesitas una cotización o asesoría técnica?</h2>
            <p>Cuéntanos qué necesitas mover, proteger o controlar. Un asesor te responde con referencia, precio y tiempo de entrega.</p>
          </div>
          <div className="hm-quote__actions">
            <Pill as="link" to="/carrito#cotizacion" tone="o">Solicitar cotización</Pill>
            <a href={site.whatsappUrl} target="_blank" rel="noreferrer" className="hm-quote__wa"><MessageCircle size={16} aria-hidden="true" /> Escríbenos por WhatsApp</a>
          </div>
        </Bezel>
      </Reveal>
    </section>
  )
}
