import { FileText, MessageCircle } from 'lucide-react'
import { site } from '../../config/site'
import Eyebrow from '../ui/Eyebrow'
import Pill from '../ui/Pill'
import '../../styles/auth.css'
import '../../styles/rd-pages.css'

// Crear cuenta e ingresar: panel dividido. A un lado el formulario; al otro una foto industrial con los beneficios de la cuenta.
export default function AuthLayout({ eyebrow, lines, benefits, children }) {
  return (
    <section className="au">
      <div className="au__form">
        <div className="au__inner">
          <Eyebrow>{eyebrow}</Eyebrow>
          <h1 className="au__title">
            {lines.map((line, i) => (line.accent ? <em key={i}>{line.text}</em> : <span key={i}>{line.text}</span>))}
          </h1>
          <p className="au__note"><FileText size={18} aria-hidden="true" /> <span><strong>¿Solo quieres cotizar?</strong> No necesitas cuenta. <a href="/carrito#cotizacion">Ir a cotizar</a></span></p>
          {children}
          <Pill as="a" href={site.whatsappLink('Hola, necesito ayuda para crear mi cuenta o ingresar en la tienda.')} target="_blank" rel="noreferrer" tone="l" icon={MessageCircle} className="pill--block auth__wa">
            ¿Necesitas ayuda? Escríbenos por WhatsApp
          </Pill>
        </div>
      </div>
      <aside className="au__photo on-dark" aria-label="Beneficios de tener cuenta">
        <img src="/img/fotos/crear-cuenta.webp" alt="" width="1280" height="853" loading="eager" />
        <div className="au__benefits">
          <h2>Tu cuenta <strong>en Imagen Eléctrica</strong></h2>
          <ul>
            {benefits.slice(0, 3).map(({ icon: Icon, text }) => (
              <li key={text}><span><Icon size={22} strokeWidth={1.5} aria-hidden="true" /></span>{text}</li>
            ))}
          </ul>
        </div>
      </aside>
    </section>
  )
}
