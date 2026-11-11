import { Link } from 'react-router-dom'
import { ClipboardList, MessageCircle, ReceiptText, RotateCcw, ShoppingCart } from 'lucide-react'
import { site } from '../../config/site'
import '../../styles/auth.css'
import '../../styles/auth2.css'
import '../../styles/ax3.css'

const BENEFITS = [
  { icon: ClipboardList, title: 'Pedidos y cotizaciones en un lugar', text: 'Sigue cada solicitud, de la recepción a la entrega.' },
  { icon: ReceiptText, title: 'Datos de facturación guardados', text: 'Tu NIT o cédula listos para la próxima compra.' },
  { icon: RotateCcw, title: 'Recompra en un clic', text: 'Vuelve a pedir lo de siempre sin buscarlo otra vez.' },
]

// Rueda mecánica en línea (dientes calculados): decorado de la composición de marca
function Gear({ teeth = 16, outer = 150, inner = 124, hole = 52 }) {
  const pts = []
  const step = (Math.PI * 2) / teeth
  for (let i = 0; i < teeth; i += 1) {
    const a = i * step
    for (const [da, r] of [[0.1, inner], [0.28, outer], [0.72, outer], [0.9, inner]]) pts.push([Math.cos(a + da * step) * r, Math.sin(a + da * step) * r])
  }
  const d = `M${pts.map(([x, y]) => `${x.toFixed(1)} ${y.toFixed(1)}`).join('L')}Z`
  return (
    <svg className="ax3__gear" viewBox="-160 -160 320 320" aria-hidden="true" focusable="false">
      <g fill="none" stroke="currentColor" strokeWidth="2" strokeLinejoin="round">
        <path d={d} />
        <circle r={hole} />
        <circle r={hole * 0.4} />
      </g>
    </svg>
  )
}

// La «e» del logo (anillo abierto, barra y rayo) en contorno
function BigE() {
  return (
    <svg className="ax3__e" viewBox="0 0 330 300" aria-hidden="true" focusable="false">
      <g fill="none" stroke="currentColor" strokeWidth="2" strokeLinejoin="round">
        <path d="M251.1 234.8A132 132 0 1 1 282.0 150.0L238.0 150.0A88 88 0 1 0 217.4 206.6Z" />
        <path d="M64.0 150H278.0V184H64.0Z" />
        <path d="M172 76L136 126H160L148 146L188 98H164Z" />
      </g>
    </svg>
  )
}

/**
 * Ingresar, Crear cuenta y Recuperar: dos columnas. A la izquierda un panel violeta de marca sin fotos (arcos concéntricos naranja,
 * rueda mecánica y la «e» del logo en contorno, más tres beneficios de tener cuenta); a la derecha el formulario y, debajo, el aviso
 * de que también se puede comprar sin cuenta. En celular el formulario va primero y el panel baja a una franja compacta.
 * `title` es el h1 y `children` el formulario.
 */
export default function AuthLayout({ title, children, wide = false }) {
  return (
    <section className={`ax ax3${wide ? ' ax3--wide' : ''}`}>
      <div className="ax3__main">
        <div className="ax3__col">
          <div className="ax__card">
            <Link to="/" className="ax__logo" aria-label="Imagen Eléctrica, ir al inicio">
              <img src="/img/logo-ajustado.png" alt="" width="40" height="48" />
            </Link>
            <h1 className="ax__title">{title}</h1>
            {children}
          </div>

          <aside className="ax3__guest" aria-label="Compra sin cuenta">
            <ShoppingCart size={20} aria-hidden="true" />
            <p><strong>También puedes comprar sin cuenta.</strong> Arma tu carrito y en el pago solo te pedimos los datos de entrega.</p>
            <Link to="/carrito">Ir al carrito</Link>
          </aside>

          <p className="ax__help">
            <a href={site.whatsappLink('Hola, necesito ayuda para crear mi cuenta o ingresar en la tienda.')} target="_blank" rel="noreferrer">
              <MessageCircle size={14} aria-hidden="true" /> <span>Ayuda por WhatsApp</span>
            </a>
          </p>
        </div>
      </div>

      <div className="ax3__brand">
        <div className="ax3__art" aria-hidden="true">
          <svg className="ax3__arcs" viewBox="0 0 600 600" focusable="false">
            <g fill="none" stroke="var(--ie-orange-500)" strokeWidth="1.5">
              <circle cx="560" cy="40" r="150" />
              <circle cx="560" cy="40" r="230" opacity=".7" />
              <circle cx="560" cy="40" r="310" opacity=".45" />
              <circle cx="560" cy="40" r="390" opacity=".25" />
            </g>
          </svg>
          <Gear />
          <BigE />
        </div>
        <div className="ax3__copy">
          <p className="ax3__kicker">Tu cuenta Imagen Eléctrica</p>
          <p className="ax3__lead">Compra más rápido, <em>con todo a la mano</em></p>
          <ul className="ax3__list">
            {BENEFITS.map(({ icon: Icon, title: t, text }) => (
              <li key={t}>
                <span className="ax3__icon"><Icon size={20} aria-hidden="true" /></span>
                <span><strong>{t}</strong><small>{text}</small></span>
              </li>
            ))}
          </ul>
        </div>
      </div>
    </section>
  )
}
