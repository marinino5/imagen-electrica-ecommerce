import { Link } from 'react-router-dom'
import { MessageCircle } from 'lucide-react'
import { site } from '../../config/site'
import Bgfx from '../ui/Bgfx'
import '../../styles/auth.css'
import '../../styles/auth2.css'
import '../../styles/ie-pages.css'

const BRANDS = [
  { name: 'WEG', src: '/img/marcas/weg.png', h: 22 },
  { name: 'ABB', src: '/img/marcas/abb.svg', h: 20 },
  { name: 'Siemens', src: '/img/marcas/siemens.svg', h: 18 },
  { name: 'Fluke', src: '/img/marcas/fluke.svg', h: 20 },
]

// «Panel limpio» (Ingresar y Crear cuenta): una tarjeta centrada de 460 px sobre una malla de color, la silueta de un motor
// dibujada en línea y un arco naranja fino. Entrada propia: la tarjeta sube 16 px con fundido. Bajo la tarjeta, los
// logos de las marcas que distribuimos. `title` es el h1 y `children` el formulario.
export default function AuthLayout({ title, children }) {
  return (
    <section className="ax bg-lila-fade">
      <Bgfx b a="motor:r" c="l" arcTop="58%" />
      <div className="ax__stage">
        <div className="ax__card">
          <Link to="/" className="ax__logo" aria-label="Imagen Eléctrica, ir al inicio">
            <img src="/img/logo-ajustado.png" alt="" width="40" height="48" />
          </Link>
          <h1 className="ax__title">{title}</h1>
          <p className="ax__quote">¿Solo quieres cotizar? <Link to="/carrito#cotizacion">No necesitas cuenta</Link></p>
          {children}
          <p className="ax__help">
            <a href={site.whatsappLink('Hola, necesito ayuda para crear mi cuenta o ingresar en la tienda.')} target="_blank" rel="noreferrer"><MessageCircle size={14} aria-hidden="true" /> Ayuda por WhatsApp</a>
          </p>
        </div>
        <div className="ax__brands">
          <p>Distribuidores de WEG, ABB, Siemens y Fluke</p>
          <ul aria-hidden="true">
            {BRANDS.map((b) => <li key={b.name}><img src={b.src} alt="" height={b.h} loading="lazy" /></li>)}
          </ul>
        </div>
      </div>
    </section>
  )
}
