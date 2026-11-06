import { MessageCircle, Zap } from 'lucide-react'
import { site } from '../../config/site'
import WordReveal from '../ui/WordReveal'
import OrderPreview from './OrderPreview'
import BenefitStack from './BenefitStack'
import '../../styles/kit.css'
import '../../styles/auth.css'

// Crear cuenta e ingresar: sobre el fondo global con arcos. A la izquierda el título, la vista previa de
// Mi cuenta y los beneficios; a la derecha el formulario en una tarjeta blanca.
export default function AuthLayout({ eyebrow, lines, benefits, previewKind, children }) {
  return (
    <section className="container auth">
      <aside className="auth__intro">
        <span className="auth__arc auth__arc--orange" aria-hidden="true" />
        <span className="auth__arc auth__arc--violet" aria-hidden="true" />
        <p className="eyebrow"><Zap size={14} aria-hidden="true" /> {eyebrow}</p>
        <WordReveal as="h1" className="display auth__title" lines={lines} />
        <figure className="auth__photo">
          <img
            src="/img/fotos/crear-cuenta-sm.webp"
            srcSet="/img/fotos/crear-cuenta-sm.webp 640w, /img/fotos/crear-cuenta.webp 1280w"
            sizes="(min-width: 960px) 40vw, 100vw"
            alt="Sala de tableros eléctricos industriales"
            width="640"
            height="338"
            loading="lazy"
          />
        </figure>
        <OrderPreview kind={previewKind} />
        <BenefitStack items={benefits} />
      </aside>
      <div className="auth__main">
        {children}
        <a className="btn btn--ghost btn--block auth__wa" href={site.whatsappLink('Hola, necesito ayuda para crear mi cuenta o ingresar en la tienda.')} target="_blank" rel="noreferrer">
          <MessageCircle size={18} aria-hidden="true" /> ¿Necesitas ayuda? Escríbele a {site.mainContact.name}
        </a>
      </div>
    </section>
  )
}
