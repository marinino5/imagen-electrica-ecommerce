import { Link } from 'react-router-dom'
import { MessageCircle } from 'lucide-react'
import { site } from '../../config/site'
import Bgfx from '../ui/Bgfx'
import Pedestal from '../ui/Pedestal'
import '../../styles/auth.css'
import '../../styles/auth2.css'
import '../../styles/ie-pages.css'

const BRANDS = [
  { name: 'WEG', src: '/img/marcas/weg.png', h: 22 },
  { name: 'ABB', src: '/img/marcas/abb.svg', h: 20 },
  { name: 'Siemens', src: '/img/marcas/siemens.svg', h: 18 },
  { name: 'Fluke', src: '/img/marcas/fluke.svg', h: 20 },
]

// Dos carriles de producto recortado que se desplazan muy despacio en sentidos opuestos (riel de escritorio)
const LANE_A = ['weg-w22-iec', 'weg-cfw300', 'weg-contactores', 'weg-guardamotores', 'weg-cestari-wcg01']
const LANE_B = ['weg-cfw500', 'weg-w21-aluminio', 'weg-minibreakers', 'weg-ssw05', 'weg-plc500']
const MOBILE = ['weg-cfw300', 'weg-w22-iec', 'weg-contactores']

function Lane({ items, reverse }) {
  // La lista va dos veces: al llegar a la mitad el desplazamiento vuelve a empezar sin salto
  const list = [...items, ...items]
  return (
    <ul className={`ax__lane ${reverse ? 'is-rev' : ''}`}>
      {list.map((n, i) => (
        <li key={`${n}-${i}`}><Pedestal src={`/img/catalogo/${n}-sm.webp`} loading="lazy" /></li>
      ))}
    </ul>
  )
}

// «Panel limpio» de Ingresar, Crear cuenta y Recuperar: a la izquierda (55 %) la tarjeta de 460 px sobre lila con cuadrícula
// de plano; a la derecha (45 %, solo escritorio) un «riel de producto» con dos carriles que se mueven en sentidos opuestos,
// 4 logos en gris arriba y un arco naranja grande. En celular: la tarjeta con una fila de 3 productos encima.
// Entrada: la tarjeta sube 16 px con fundido. `title` es el h1 y `children` el formulario.
export default function AuthLayout({ title, children }) {
  return (
    <section className="ax bg-lila-fade">
      <div className="ax__left">
        <Bgfx d b />
        <div className="ax__stage">
          <ul className="ax__mrow" aria-hidden="true">
            {MOBILE.map((n) => <li key={n}><Pedestal src={`/img/catalogo/${n}-sm.webp`} /></li>)}
          </ul>
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
          <ul className="ax__brands" aria-label="Distribuidores de WEG, ABB, Siemens y Fluke">
            {BRANDS.map((b) => <li key={b.name}><img src={b.src} alt={b.name} height={b.h} loading="lazy" /></li>)}
          </ul>
        </div>
      </div>
      <aside className="ax__rail" aria-hidden="true">
        <span className="ax__rail-arc" />
        <ul className="ax__rail-logos">
          {BRANDS.map((b) => <li key={b.name}><img src={b.src} alt="" height={b.h} loading="lazy" /></li>)}
        </ul>
        <div className="ax__lanes">
          <Lane items={LANE_A} />
          <Lane items={LANE_B} reverse />
        </div>
      </aside>
    </section>
  )
}
