import { Link } from 'react-router-dom'
import { ClipboardList, FileText, MessageCircle, ShoppingCart } from 'lucide-react'
import { site } from '../../config/site'
import Pedestal from '../ui/Pedestal'
import '../../styles/auth.css'
import '../../styles/auth2.css'
import '../../styles/ie-pages.css'

// Los tres beneficios de tener cuenta, en una sola línea y con iconos pequeños
const PERKS = [
  { Icon: ClipboardList, text: 'Sigue tus pedidos' },
  { Icon: FileText, text: 'Cotizaciones valoradas' },
  { Icon: ShoppingCart, text: 'Carrito guardado' },
]

// Crear cuenta e ingresar: una tarjeta de 460 px sobre el degradado blanco → lavanda, con un arco naranja de 2 px detrás y,
// en escritorio, una franja violeta a la derecha. Arriba el logo y el título; después el formulario (children), el enlace para cambiar entre Ingresar y
// Crear cuenta (va dentro de children) y los beneficios.
export default function AuthLayout({ eyebrow, lines, children }) {
  return (
    <section className="ax bg-lila-fade">
      <span className="ax__ring" aria-hidden="true" />
      <div className="ax__stage">
        <div className="ax__card">
          <Link to="/" className="ax__logo" aria-label="Imagen Eléctrica, ir al inicio">
            <img src="/img/logo-ajustado.png" alt="" width="40" height="48" />
          </Link>
          <p className="ax__eyebrow">{eyebrow}</p>
          <h1 className="ax__title">
            {lines.map((line, i) => (line.accent ? <em key={i}>{line.text}</em> : <span key={i}>{line.text}</span>))}
          </h1>
          {children}
          <ul className="ax__perks" aria-label="Beneficios de tener cuenta">
            {PERKS.map(({ Icon, text }) => (
              <li key={text}><Icon size={16} strokeWidth={1.8} aria-hidden="true" /> {text}</li>
            ))}
          </ul>
          <p className="ax__help">
            ¿Solo quieres cotizar? <Link to="/carrito#cotizacion">No necesitas cuenta</Link> ·{' '}
            <a href={site.whatsappLink('Hola, necesito ayuda para crear mi cuenta o ingresar en la tienda.')} target="_blank" rel="noreferrer"><MessageCircle size={14} aria-hidden="true" /> Ayuda por WhatsApp</a>
          </p>
        </div>
      </div>
      {/* Solo escritorio: franja violeta con tres productos sobre pedestales */}
      <aside className="ax__strip" aria-hidden="true">
        <div className="ax__prods">
          <Pedestal className="ax__p ax__p--a" src="/img/catalogo/weg-w22-nema-premium-sm.webp" />
          <Pedestal className="ax__p ax__p--b" src="/img/catalogo/weg-cfw500-sm.webp" />
          <Pedestal className="ax__p ax__p--c" src="/img/catalogo/weg-contactores-sm.webp" />
        </div>
        <p>Tu catálogo industrial, <strong>a un clic</strong></p>
      </aside>
    </section>
  )
}
