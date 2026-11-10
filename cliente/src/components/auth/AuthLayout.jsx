import { Link } from 'react-router-dom'
import { MessageCircle } from 'lucide-react'
import { site } from '../../config/site'
import '../../styles/auth.css'
import '../../styles/auth2.css'
import '../../styles/ie-pages.css'
import '../../styles/ie-auth.css'

// Productos que asoman detrás de la tarjeta (solo escritorio): el motor a la izquierda, el variador a la derecha y el
// multímetro abajo, inclinados entre −6° y 6°. Son fotos recortadas del catálogo; la tarjeta los tapa en parte.
const PEEK = [
  { id: 'motor', src: 'weg-w22-iec', alt: '' },
  { id: 'variador', src: 'weg-cfw500', alt: '' },
  { id: 'multimetro', src: 'fluke-multimetro-117', alt: '' },
]

// La «e» del logo (anillo abierto, barra y rayo) en contorno: se dibuja con trazos finos, sin relleno
function BigE() {
  return (
    <svg className="ax2__e" viewBox="0 0 330 300" aria-hidden="true" focusable="false">
      <g fill="none" stroke="currentColor" strokeWidth="2" strokeLinejoin="round">
        <path d="M251.1 234.8A132 132 0 1 1 282.0 150.0L238.0 150.0A88 88 0 1 0 217.4 206.6Z" />
        <path d="M64.0 150H278.0V184H64.0Z" />
        <path d="M172 76L136 126H160L148 146L188 98H164Z" />
      </g>
    </svg>
  )
}

// «Panel en dos bandas» de Ingresar, Crear cuenta y Recuperar: arriba violeta-950 con la «e» del logo enorme en contorno al 6 %
// y un arco naranja; abajo lila-100. La tarjeta de 520 px queda a caballo entre las dos, con sombra profunda, y tres productos
// recortados asoman por detrás. En celular: banda oscura de 160 px y la tarjeta debajo, sin productos.
// Entrada: la tarjeta sube 16 px con fundido. `title` es el h1 y `children` el formulario.
export default function AuthLayout({ title, children }) {
  return (
    <section className="ax ax2">
      <div className="ax2__band" aria-hidden="true">
        <BigE />
        <span className="ax2__arc" />
      </div>
      <div className="ax2__stage">
        <p className="ax2__kicker">Tu cuenta Imagen Eléctrica</p>
        <div className="ax2__wrap">
          {PEEK.map((p) => (
            <img key={p.id} className={`ax2__peek ax2__peek--${p.id}`} src={`/img/catalogo/${p.src}-sm.webp`} alt={p.alt} width="480" height="480" aria-hidden="true" loading="lazy" />
          ))}
          <div className="ax__card">
            <Link to="/" className="ax__logo" aria-label="Imagen Eléctrica, ir al inicio">
              <img src="/img/logo-ajustado.png" alt="" width="40" height="48" />
            </Link>
            <h1 className="ax__title">{title}</h1>
            {children}
            <p className="ax__help">
              <a href={site.whatsappLink('Hola, necesito ayuda para crear mi cuenta o ingresar en la tienda.')} target="_blank" rel="noreferrer">
                <MessageCircle size={14} aria-hidden="true" /> <span>Ayuda por WhatsApp</span>
              </a>
            </p>
          </div>
        </div>
      </div>
    </section>
  )
}
