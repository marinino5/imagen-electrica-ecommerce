import { Link } from 'react-router-dom'
import { ClipboardList, MessageCircle, ReceiptText, RotateCcw } from 'lucide-react'
import { site } from '../../config/site'
import '../../styles/auth.css'
import '../../styles/auth2.css'
import '../../styles/ax4.css'

const PERKS = [
  { icon: ClipboardList, text: 'Pedidos y cotizaciones en un lugar' },
  { icon: ReceiptText, text: 'Facturación guardada' },
  { icon: RotateCcw, text: 'Recompra en un clic' },
]

// Rueda dentada en línea (dientes calculados): gira muy lento detrás de la vitrina
function Gear({ teeth = 20, outer = 190, inner = 160 }) {
  const pts = []
  const step = (Math.PI * 2) / teeth
  for (let i = 0; i < teeth; i += 1) {
    const a = i * step
    for (const [da, r] of [[0.1, inner], [0.28, outer], [0.72, outer], [0.9, inner]]) pts.push([Math.cos(a + da * step) * r, Math.sin(a + da * step) * r])
  }
  const d = `M${pts.map(([x, y]) => `${x.toFixed(1)} ${y.toFixed(1)}`).join('L')}Z`
  return (
    <svg className="ax4__gear" viewBox="-200 -200 400 400" aria-hidden="true" focusable="false">
      <g fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinejoin="round">
        <path d={d} />
        <circle r="128" />
        <circle r="62" />
        <circle r="22" />
        {Array.from({ length: 6 }, (_, i) => <circle key={i} cx={Math.cos((i * Math.PI) / 3) * 95} cy={Math.sin((i * Math.PI) / 3) * 95} r="14" />)}
      </g>
    </svg>
  )
}

/** Paso a paso visual de Crear cuenta: «1 Tus datos · 2 Facturación». `current` es 1 o 2. */
export function Stepper({ steps, current }) {
  return (
    <ol className="ax4__steps" aria-label="Pasos para crear tu cuenta">
      {steps.map((label, i) => (
        <li key={label} className={i + 1 === current ? 'is-on' : i + 1 < current ? 'is-done' : ''} aria-current={i + 1 === current ? 'step' : undefined}>
          <span aria-hidden="true">{i + 1 < current ? '✓' : i + 1}</span>
          <b>{label}</b>
        </li>
      ))}
    </ol>
  )
}

/**
 * Ingresar, Crear cuenta y Recuperar: una tarjeta grande centrada sobre un fondo lila con grabado industrial. A la izquierda (55 %) el
 * formulario; a la derecha (45 %) la «vitrina» de marca: degradado violeta, una rueda dentada que gira muy lenta, un arco naranja que
 * abraza tres productos estrella y los tres beneficios de tener cuenta en tarjetas de vidrio. En celular el formulario va primero y la
 * vitrina baja a una franja con los productos y los beneficios en carrusel. `title` es el h1, `stepper` el paso a paso y `children` el formulario.
 */
export default function AuthLayout({ title, children, stepper }) {
  return (
    <section className="ax ax4">
      <div className="ax4__card">
        <div className="ax4__form">
          <Link to="/" className="ax__logo" aria-label="Imagen Eléctrica, ir al inicio">
            <img src="/img/logo-ajustado.png" alt="" width="40" height="48" />
          </Link>
          {stepper}
          <h1 className="ax__title">{title}</h1>
          {children}
          <p className="ax4__guest">¿Solo quieres comprar? <Link to="/carrito">Compra sin cuenta</Link></p>
          <p className="ax__help">
            <a href={site.whatsappLink('Hola, necesito ayuda para crear mi cuenta o ingresar en la tienda.')} target="_blank" rel="noreferrer">
              <MessageCircle size={14} aria-hidden="true" /> <span>Ayuda por WhatsApp</span>
            </a>
          </p>
        </div>

        <aside className="ax4__vit" aria-label="Lo que tienes con tu cuenta">
          <Gear />
          <p className="ax4__stat"><strong>25+</strong> años <i aria-hidden="true">·</i> <strong>+1.000</strong> referencias</p>
          <div className="ax4__stage" aria-hidden="true">
            <svg className="ax4__arc" viewBox="0 0 400 220" focusable="false"><path d="M12 214 A188 188 0 0 1 388 214" /></svg>
            <span className="ax4__p ax4__p--a"><img src="/img/catalogo/weg-w22-iec-sm.webp" alt="" width="480" height="480" loading="lazy" /></span>
            <span className="ax4__p ax4__p--b"><img src="/img/catalogo/weg-contactores-sm.webp" alt="" width="480" height="480" loading="lazy" /></span>
            <span className="ax4__p ax4__p--c"><img src="/img/catalogo/weg-cfw500-sm.webp" alt="" width="480" height="480" loading="lazy" /></span>
          </div>
          <ul className="ax4__perks">
            {PERKS.map(({ icon: Icon, text }) => (
              <li key={text}><Icon size={20} aria-hidden="true" /><span>{text}</span></li>
            ))}
          </ul>
        </aside>
      </div>
    </section>
  )
}
