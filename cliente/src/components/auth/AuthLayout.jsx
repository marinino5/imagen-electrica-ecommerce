import { FileText, MessageCircle } from 'lucide-react'
import { site } from '../../config/site'
import Bezel from '../ui/Bezel'
import Eyebrow from '../ui/Eyebrow'
import Pill from '../ui/Pill'
import OrderPreview from './OrderPreview'
import BenefitCards from './BenefitCards'
import '../../styles/auth.css'

// Crear cuenta e ingresar. A la izquierda: título grande, "Así verás tus pedidos" (ejemplo animado) y
// 3 beneficios. A la derecha: el aviso de cotización sin cuenta, el formulario en bisel y la ayuda por WhatsApp.
export default function AuthLayout({ eyebrow, lines, benefits, previewKind, children }) {
  return (
    <section className="wrap auth">
      <span className="auth__ring" aria-hidden="true" />
      <aside className="auth__intro">
        <Eyebrow>{eyebrow}</Eyebrow>
        <h1 className="h-hero auth__title">
          {lines.map((line, i) => (line.accent ? <em key={i}>{line.text}</em> : <span key={i}>{line.text}</span>))}
        </h1>
        <div className="auth__preview">
          <OrderPreview kind={previewKind} />
        </div>
        <BenefitCards items={benefits} />
      </aside>

      <div className="auth__main">
        <Bezel className="auth-note">
          <div className="auth-note__core">
            <span className="auth-note__ic"><FileText size={20} strokeWidth={1.5} aria-hidden="true" /></span>
            <p><strong>¿Solo quieres cotizar?</strong> No necesitas cuenta.</p>
            <Pill as="link" to="/carrito#cotizacion" tone="l" size="sm">Ir a cotizar</Pill>
          </div>
        </Bezel>

        {children}

        <Pill
          as="a"
          href={site.whatsappLink('Hola, necesito ayuda para crear mi cuenta o ingresar en la tienda.')}
          target="_blank"
          rel="noreferrer"
          tone="l"
          icon={MessageCircle}
          className="pill--block auth__wa"
        >
          ¿Necesitas ayuda? Escríbenos por WhatsApp
        </Pill>
      </div>
    </section>
  )
}
